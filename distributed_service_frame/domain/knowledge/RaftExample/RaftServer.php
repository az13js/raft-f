<?php
namespace RaftExample;

use \Exception;

class RaftServer
{
    const TYPE_FOLLOWER   = 'FOLLOWER';
    const TYPE_LEADER     = 'LEADER';
    const TYPE_CANDIDATE  = 'CANDIDATE';

    public static $selfInstance  = null;

    public $serverType    = self::TYPE_FOLLOWER;
    public $serverId      = 0;

    public $lastActivity  = null;   // 最后一次活动时间
    public $uuid          = '';     // 保证对象没有经过任何变化

    // 原文定义的一系列参数
    public $currentTerm   = 0;      // 周期
    public $votedFor      = null;   // 候选人id
    public $log           = [['term' => 0, 'index' => 0]];     // 日志实体
    public $commitIndex   = 0;      // 已提交的最大的日志实体的索引
    public $lastApplied   = 0;      // 已应用到状态机的最大的实体索引

    // LEADER独有的属性，在选举完成时重新设置
    public $nextIndex     = [];     // 将发送给服务器的下一实体的索引。初始化为LEADER最大索引+1
    public $matchIndex    = [];     // 已同步到服务器的最大日志索引

    public function __construct(int $serverId)
    {
        $this->serverId = $serverId;
        $this->flushUuid();
    }

    public static function getRaftServer(): RaftServer
    {
        if (is_object(self::$selfInstance)) {
            return self::$selfInstance;
        }
        $config = config('raft');
        $address = $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT'];
        foreach ($config['servers'] as $server) {
            if ($server['address'] == $address) {
                return self::$selfInstance = self::getRaftServerById($server['id']);
            }
        }
        throw new Exception('Error, no configure.');
    }

    public function raftSave(): bool
    {
        $server = self::getRaftServerById($this->serverId);
        if ($server->uuid == $this->uuid) {
            $this->flushUuid();
        } else {
            return false;
        }
        return cache_set('raft_server_' . $this->serverId, $this, 60 * 60);
    }

    public static function getRaftServerById(int $server_id): RaftServer
    {
        $server = cache_get('raft_server_' . $server_id);
        if (false === $server) {
            $server = new RaftServer($server_id);
            cache_set('raft_server_' . $server_id, $server, 60 * 60);
        }
        return $server;
    }

    private static function deleteCache(int $serverId): bool
    {
        return cache_delete('raft_server_' . $serverId);
    }

    private function flushUuid()
    {
        $this->uuid = uniqid($this->serverId . '_', true);
    }
}
