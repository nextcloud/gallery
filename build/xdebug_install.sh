#!/usr/bin/env bash
# Installs the latest xdebug extensions
#
cd build
wget http://pecl.php.net/get/xdebug
mkdir xdebug-extension
tar zxvf xdebug -C xdebug-extension --strip-components=1
cd xdebug-extension
phpize
./configure
make -j4
make install
