#!/bin/bash

#usage: bash setup_kernel.sh [{path/to/download/kernel3}]

if [ "$1" ];
then
    KERNEL3_PATH="$1"
else
    KERNEL3_PATH="../K3"
fi

git clone "git://github.com/foxel/Kernel3.git" "${KERNEL3_PATH}"
ln -s "${KERNEL3_PATH}/kernel3"
cd "${KERNEL3_PATH}"
git checkout envire
