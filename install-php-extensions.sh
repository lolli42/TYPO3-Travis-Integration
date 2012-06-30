#!/bin/bash


function installPhpExtension() {
	name=$1
	pyrus install pecl/$name && pyrus build pecl/$name
	echo "extension=\"$name.so\"" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
}

installPhpExtension igbinary
apt-get install php-apc


