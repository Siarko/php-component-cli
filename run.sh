#!/bin/bash
trap "" SIGINT
if [ "$1" == "local_debug" ]; then
    echo "!! XDEBUG ENABLED !! => This will have a heavy performance impact"
    export XDEBUG_CONFIG="remote_enable=1 remote_mode=req remote_port=9000 remote_host=127.0.0.1 remote_connect_back=0"
    php -dxdebug.remote_enable=1 -dxdebug.remote_mode=req -dxdebug.remote_port=9000 -dxdebug.remote_host=127.0.0.1 -dxdebug.remote_connect_back=0 psw.php
else
    php psw.php
fi
