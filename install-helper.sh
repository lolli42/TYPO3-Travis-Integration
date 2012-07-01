#!/bin/bash
export DEBUG="echo"
export phpConfigFile=`php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`

function addOptionToPhpConfig() {
	if grep -q "$1" $phpConfigFile
	then
		echo "Option $1 already added"
	else
		if [ -n "$DEBUG" ]
		then
			echo "$1 >> $phpConfigFile" 
		else
			echo "$1" >> $phpConfigFile
		fi
	fi
}

function addModuleToPhpConfig() {
	addOptionToPhpConfig "extension=$1.so"
}

function installPhpModule() {
	if [[ "$1" == "redis" ]]
	then
		installRedis
		return
	fi
	if [[ "$1" == "-y" ]]
	then
		CONFIRM="printf \"no\n\" |"
		shift
	fi
	$DEBUG $CONFIRM pecl install $1
	addModuleToPhpConfig $1
}

function installRedis() {
	_pwd=$PWD
	mkdir phpredis-build
	cd phpredis-build
	$DEBUG git clone --depth 1 git://github.com/nicolasff/phpredis.git
	$DEBUG cd phpredis
	$DEBUG phpize
	$DEBUG ./configure
	$DEBUG make
	$DEBUG sudo make install
	cd $_pwd
}

installPhpModule igbinary
installPhpModule -y memcache
installPhpModule redis

if [[ "$TRAVIS_PHP_VERSION" == "5.3" ]]
then 
	installPhpModule -y apc 
	addOptionToPhpConfig "apc.enable_cli=1"
	addOptionToPhpConfig "apc.slam_defense=0"
fi


