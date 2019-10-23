<?php
namespace RaftExample\Controller;

use \RaftExample\RaftServer;
use \Exception;

class Service
{
    /**
     * TODO 待完善
     */
    public static function respondForAppendEntries(): array
    {
        $term = $_GET['term'];
        $leaderId = $_GET['leaderId'];
        $prevLogIndex = $_GET['prevLogIndex'];
        $prevLogTerm = $_GET['prevLogTerm'];
        $entries = json_decode(urldecode($_GET['entries']), true);
        $leaderCommit = $_GET['leaderCommit'];
        $server = RaftServer::getRaftServer();
        // TODO
        $server->log = $entries;
        $server->raftSave();
        switch ($server->serverType) {
            case RaftServer::TYPE_FOLLOWER:
                if ($term < $server->currentTerm) {
                    return [
                        'term' => $server->currentTerm,
                        'success' => false,
                    ];
                }
                foreach ($server->log as $log) {
                    if ($log['index'] == $prevLogIndex) {
                        if ($log['term'] != $prevLogTerm) {
                            return [
                                'term' => $server->currentTerm,
                                'success' => false,
                            ];
                        }
                    }
                }
                if ($server->log[$prevLogIndex - 1]['term']) {
                    
                }
                break;
            default:
                ;
        }
        return [
            'term' => $server->currentTerm,
            'success' => true,
        ];
    }

    public static function respondForVote(): array
    {
        $server = RaftServer::getRaftServer();
        if (RaftServer::TYPE_FOLLOWER != $server->serverType) {
            self::updateServerInfo($server);
            return [
                'term' => $server->currentTerm,
                'voteGranted' => false,
            ];
        }
        $logTotal = count($server->log, COUNT_NORMAL);
        $log = $logTotal == 0 ? ['term' => 0, 'index' => 0] : $server->log[$logTotal - 1];
        $myLastLogIndex = $log['index'];
        $myLastLogTerm = $log['term'];

        $term = $_GET['term'];
        $candidateId = $_GET['candidateId'];
        $lastLogIndex = $_GET['lastLogIndex'];
        $lastLogTerm = $_GET['lastLogTerm'];

        if ($term < $server->currentTerm) {
            self::updateServerInfo($server);
            return [
                'term' => $server->currentTerm,
                'voteGranted' => false,
            ];
        }
        if ((is_null($server->votedFor) || $server->votedFor == $candidateId) && $lastLogIndex >= $myLastLogIndex && $lastLogTerm >= $myLastLogTerm) {
            self::updateServerInfo($server);
            return [
                'term' => $server->currentTerm,
                'voteGranted' => true,
            ];
        }
        self::updateServerInfo($server);
        return [
            'term' => $server->currentTerm,
            'voteGranted' => false,
        ];
    }

    private static function updateServerInfo(RaftServer $server): bool
    {
        $server->lastActivity = time();
        return $server->raftSave();
    }
}
