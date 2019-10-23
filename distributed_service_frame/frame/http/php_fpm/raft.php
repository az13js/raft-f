<?php
/**
 * 返回服务器ID
 *
 * @return int 默认是0
 * @author mengshaoying <mengshaoying@aliyun.com>
 * @version 0.0.0
 */
function raft_get_serverid(): int
{
    return $_GET['serverid'] ?? 0;
}

/**
 * 返回服务器类型
 *
 * 类型是LEADER，FOLLOWER或CANDIDATE
 *
 * @return int 默认是0
 * @author mengshaoying <mengshaoying@aliyun.com>
 * @version 0.0.0
 */
function raft_get_server_type($config_key = 'default'): string
{
    $result = cache_get('raft_server_type_' . raft_get_serverid(), $config_key);
    return empty($result) ? 'FOLLOWER' : $result;
}