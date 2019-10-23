<?php

function client_call($service_name, $method, $args = [])
{
    $configs = config('client');
    $config = $configs[$service_name];

    $host = $config['host'];
    $ips = $config['ips'];
    $timeout = $config['timeout'];
    $retry = $config['retry'];
    $server_id = array_rand($ips); // 这里保存选中的ID，带在http请求头里面发送给service，用来识别serverid
    $ip = $ips[$server_id];

    $raw_data = remote_post('http://'.$ip.'/'.$method . '?serverid=' . $server_id, serialize($args), $timeout, $retry, ["host: $host"]);
    if (false === $raw_data) {
        return false;
    }

    $data = unserialize($raw_data);

    if ($data['res']) {
        return $data['data'];
    } else {
        throw new $data['exception']['class']($data['exception']['message']);
    }
}
