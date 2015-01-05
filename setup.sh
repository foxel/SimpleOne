#!/bin/bash
#
# Copyright (C) 2014 Andrey F. Kupreychik (Foxel)
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
# usage: bash setup.sh

SIMPLEONE_DIR="${PWD}"

php -r "readfile('https://getcomposer.org/installer');" | php

./composer.phar update

KERNEL3_PATH="lib/vendor/foxel/kernel3"
echo "kernel dir is ${KERNEL3_PATH}"
cd "${KERNEL3_PATH}"
echo "making phars"
php -d phar.readonly=0 -f "makePhar.php"
# get back to Simple One
cd "${SIMPLEONE_DIR}"
