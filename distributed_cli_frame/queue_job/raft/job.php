<?php
queue_job(\RaftExample\Controller\Cli::JOB_FOLLOWER, function($data, $retry, $id) {
    return \RaftExample\Controller\Cli::jobFollower(unserialize($data['server']));
});

queue_job(\RaftExample\Controller\Cli::JOB_LEADER, function($data, $retry, $id) {
    return \RaftExample\Controller\Cli::jobLeader(unserialize($data['server']));
});

queue_job(\RaftExample\Controller\Cli::JOB_CANDIDATE, function($data, $retry, $id) {
    return \RaftExample\Controller\Cli::jobCandidate(unserialize($data['server']));
});
