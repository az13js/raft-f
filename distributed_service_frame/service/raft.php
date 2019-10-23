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