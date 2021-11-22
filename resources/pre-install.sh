#!/bin/bash

if [ -f /etc/update-motd.d/10-armbian-header-jeedomatlas ];then
    sudo apt install wireguard-tools=1.0.20210223-1~bpo10+1
    sudo apt install wireguard -f
fi