#!/bin/bash

if [ -f /etc/update-motd.d/10-armbian-header-jeedomatlas ];then
    sudo apt install wireguard-tools=1.0.20210223-1~bpo10+1
    sudo apt install wireguard -f
fi

VERSION=$(grep -oP '(?<=^VERSION_ID=).+' /etc/os-release | tr -d '"')
if [[ ${VERSION} -lt 11 ]] && [[ `arch` == "x86_64" ]] ; then
    sudo sh -c "echo 'deb http://deb.debian.org/debian buster-backports main contrib non-free' > /etc/apt/sources.list.d/buster-backports.list"
fi