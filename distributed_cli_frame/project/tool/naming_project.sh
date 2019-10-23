#!/bin/bash

sed_name()
{
    cat $1 | sed -e "s/distributed_cli_frame/$2/g" > $1.new && mv $1.new $1
}

if [ ! -n "$1" ] ;then
    echo "Usage: $0 <name>"
    exit
fi

ROOT_DIR="$(cd "$(dirname $0)" && pwd)"/../../

sed_name $ROOT_DIR/project/config/development/supervisor/queue_worker.conf $1
sed_name $ROOT_DIR/project/config/production/supervisor/queue_worker.conf $1
sed_name $ROOT_DIR/project/tool/start_dev_server.sh $1
