<?php
namespace RaftExample\Controller;

use \RaftExample\RaftServer;
use \Exception;
use \RaftExample\Tools\MultipleUrlContextLoader;

class Cli
{
    const JOB_FOLLOWER  = 'RAFT_FOLLOWER_CHECK';
    const JOB_CANDIDATE = 'RAFT_CANDIDATE_CHECK';
    const JOB_LEADER    = 'RAFT_LEDAER_HEARTBEAT';

    public static function queueStart()
    {
        $config = config('raft');
        foreach ($config['servers'] as $server) {
            cache_delete('raft_server_' . $server['id']);
            $raftServer = RaftServer::getRaftServerById($server['id']);
            switch ($raftServer->serverType) {
                case RaftServer::TYPE_FOLLOWER:
                    $now = time();
                    if (is_null($raftServer->lastActivity) || $raftServer->lastActivity < $now) {
                        $raftServer->lastActivity = $now;
                        queue_push(self::JOB_FOLLOWER, ['server' => serialize($raftServer)], mt_rand($config['wait_min'], $config['wait_max']));
                    }
                    break;
                default:
                    throw new Exception('undefined type:' . $raftServer->serverType);
            }
        }
    }

    public static function jobFollower(RaftServer $serverFromBeanstalk): bool
    {
        $raftServer = RaftServer::getRaftServerById($serverFromBeanstalk->serverId);
        if ($raftServer->uuid != $serverFromBeanstalk->uuid) {
            return true;
        } else {
            // 开始选举
            $raftServer->currentTerm++;
            $raftServer->votedFor = $raftServer->serverId;
            $raftServer->serverType = RaftServer::TYPE_CANDIDATE;
            if (self::requestVoteRPC($raftServer, $raftServer->currentTerm, $raftServer->serverId, $raftServer->commitIndex, $raftServer->currentTerm)) {
                $raftServer->serverType = RaftServer::TYPE_LEADER;
            }
            if ($raftServer->raftSave()) {
                $config = config('raft');
                if ($raftServer->serverType == RaftServer::TYPE_LEADER) {
                    queue_push(self::JOB_LEADER, ['server' => serialize($raftServer)], $config['wait_min']);
                } else {
                    queue_push(self::JOB_CANDIDATE, ['server' => serialize($raftServer)], mt_rand($config['wait_min'], $config['wait_max']));
                }
            }
            return true;
        }
    }

    public static function jobLeader(RaftServer $serverFromBeanstalk): bool
    {
        $raftServer = RaftServer::getRaftServerById($serverFromBeanstalk->serverId);
        if ($raftServer->uuid != $serverFromBeanstalk->uuid) {
            return true;
        }
        self::appendEntriesRPC($raftServer);
        if ($raftServer->raftSave()) {
            $config = config('raft');
            queue_push(self::JOB_LEADER, ['server' => serialize($raftServer)], $config['wait_min']);
        }
        return true;
    }

    public static function jobCandidate(RaftServer $serverFromBeanstalk): bool
    {
        $raftServer = RaftServer::getRaftServerById($serverFromBeanstalk->serverId);
        if ($raftServer->uuid != $serverFromBeanstalk->uuid) {
            return true;
        } else {
            if (self::requestVoteRPC($raftServer, $raftServer->currentTerm, $raftServer->serverId, $raftServer->commitIndex, $raftServer->currentTerm)) {
                $raftServer->serverType = RaftServer::TYPE_LEADER;
            }
            if ($raftServer->raftSave()) {
                if ($raftServer->serverType == RaftServer::TYPE_LEADER) {
                    $config = config('raft');
                    queue_push(self::JOB_LEADER, ['server' => serialize($raftServer)], $config['wait_min']);
                } else {
                    queue_push(self::JOB_CANDIDATE, ['server' => serialize($raftServer)], mt_rand($config['wait_min'], $config['wait_max']));
                }
            }
            return true;
        }
    }

    public static function appendEntriesRPC(RaftServer $raftServer): bool
    {
        $logTotal = count($raftServer->log, COUNT_NORMAL);
        $log = $logTotal == 0 ? ['term' => 0, 'index' => 0] : $raftServer->log[$logTotal - 1];
        $params = [
            'term' => $raftServer->currentTerm,
            'leaderId' => $raftServer->serverId,
            'prevLogIndex' => $log['index'],
            'prevLogTerm' => $log['term'],
            'entries' => urlencode(json_encode($log)),// TODO
            'leaderCommit' => $raftServer->commitIndex,
        ];
        $config = config('raft');
        $urls = [];
        foreach ($config['servers'] as $server) {
            if ($server['id'] == $raftServer->serverId) {
                continue;
            }
            $urls[] = $server['address'] . '/appendEntriesRPC?' . http_build_query($params);
        }
        $loader = new MultipleUrlContextLoader();
        $loader->setUrls($urls);
        $loader->loadContent();
        // TODO
        return true;
    }

    public static function requestVoteRPC(RaftServer $raftServer, int $term, int $candidateId, int $lastLogIndex, int $lastLogTerm): bool
    {
        $logTotal = count($raftServer->log, COUNT_NORMAL);
        $log = $logTotal == 0 ? ['term' => 0, 'index' => 0] : $raftServer->log[$logTotal - 1];
        $params = [
            'term' => $term,
            'candidateId' => $candidateId,
            'lastLogIndex' => $log['index'],
            'lastLogTerm' => $log['term'],
        ];
        $config = config('raft');
        $urls = [];
        foreach ($config['servers'] as $server) {
            if ($server['id'] == $raftServer->serverId) {
                continue;
            }
            $urls[] = $server['address'] . '/requestVoteRPC?' . http_build_query($params);
        }
        $loader = new MultipleUrlContextLoader();
        $loader->setUrls($urls);
        $voteGranted = 0;
        $loader->loadContent();
        foreach ($loader->getContents() as $respond) {
            $data = json_decode($respond, true);
            if ($data['voteGranted']) {
                $voteGranted++;
            }
        }
        if ($voteGranted > count($config['servers'], COUNT_NORMAL) - $voteGranted - 1) {
            return true;
        }
        return false;
    }
}
