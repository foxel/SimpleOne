#!/bin/bash
#
# Copyright (C) 2012 Andrey F. Kupreychik (Foxel)
#
# This file is part of QuickFox SimpleOne.
#
# SimpleOne is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# SimpleOne is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with SimpleOne. If not, see <http://www.gnu.org/licenses/>.
#
# usage: bash setup_kernel.sh [{path/to/download/kernel3}]

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
