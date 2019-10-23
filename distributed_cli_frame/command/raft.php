<?php
command('raft:test_servers', '测试服务器', function()
{
    $config = config('raft');
    foreach ($config['servers'] as $server) {
        $respond = remote_get($server['address'] . '/status', 10, 1, [
            "host: {$server['host']}",
        ]);
        if (empty($respond)) {
            echo 'Fail, id=' . $server['id'] . PHP_EOL;
        } else {
            echo 'id=' . $server['id'] . ':' . PHP_EOL;
            echo $respond . PHP_EOL;
        }
    }
});

command('raft:push_delay', '推送延时到队列', function()
{
    RaftExample\Controller\Cli::queueStart();
});

command('raft:watch_servers', '监视服务器', function()
{
    $config = config('raft');
    while (true) {
        echo date('i:s') . PHP_EOL;
        foreach ($config['servers'] as $server) {
            $raftServer = RaftExample\RaftServer::getRaftServerById($server['id']);
            echo ' ' . $raftServer->serverType . '[' . $raftServer->uuid . ']' . PHP_EOL;
        }
        echo PHP_EOL;
        usleep(41667);
    }
});