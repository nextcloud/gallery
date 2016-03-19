#!/usr/bin/env bash
# Installs the latest xdebug extensions
#

## Official builds
wget http://pecl.php.net/get/xdebug
mkdir xdebug-extension
tar zxvf xdebug -C xdebug-extension --strip-components=1

## Git master
#git clone https://github.com/xdebug/xdebug.git xdebug-extension
#cd xdebug-extension

phpize
./configure
make -j4
make install
