<?php
service('requestVoteRPC', function()
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(RaftExample\Controller\Service::respondForVote());
    die();
});

service('appendEntriesRPC', function()
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(RaftExample\Controller\Service::respondForAppendEntries());
    die();
});

call_user_func(function()
{
    $server = RaftExample\RaftServer::getRaftServer();
    if ($server->serverType != RaftExample\RaftServer::TYPE_LEADER) {
        foreach (config('raft')['servers'] as $serv) {
            if ($serv['id'] == $server->votedFor) {
                header('Location: http://' . $serv['address'] . $_SERVER['REQUEST_URI'], true, 307);
                die();
            }
        }
    }
    $data = service_args();
    $server->commitIndex++;
    $server->lastApplied++;
    $log = ['index' => $server->lastApplied, 'term' => $server->currentTerm, 'data' => $data];
    $server->log[] = $log;
    $server->raftSave();
    $config = config('raft');
    queue_push(RaftExample\Controller\Cli::JOB_LEADER, ['server' => serialize($server)], $config['wait_min']);
});