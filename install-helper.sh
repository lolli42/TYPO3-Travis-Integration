#!/bin/bash
export phpConfigFile=`php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`

function addOptionToPhpConfig() {
	if grep -q "$1" $phpConfigFile
	then
		echo "Option $1 already added"
	else
		echo "$1" >> $phpConfigFile
	fi
}

function addModuleToPhpConfig() {
	if grep -q "$1.so" $phpConfigFile
	then
		echo "Module $1 already added"
	else
		addOptionToPhpConfig "extension=$1.so"
	fi
}

function installPhpModule() {
	case "$1" in
		-y)
			printf "no\n" | pecl install $2 > /dev/null
			shift
		;;
		redis)
			installRedis > /dev/null
		;;
		*)
			pecl install $1 > /dev/null
		;;
	esac

	addModuleToPhpConfig $1

	if [[ "$1" == "apc" ]]
	then
		addOptionToPhpConfig "apc.enable_cli=1"
		addOptionToPhpConfig "apc.slam_defense=0"
	fi
}

function installRedis() {
	_pwd=$PWD
	mkdir build-environment/phpredis-build
	cd build-environment/phpredis-build
	git clone --depth 1 git://github.com/nicolasff/phpredis.git
	cd phpredis
	phpize
	./configure
	make
	sudo make install
	cd $_pwd
}
function functionalTestsCommand {
	if grep directory typo3/sysext/core/Build/FunctionalTests.xml | sed 's#[	]*<directory>\.\./\.\./\.\./\.\./\(typo3/sysext.*\)</directory>$#\1#g' | parallel --gnu --keep-order 'echo "Running {} tests"; ./typo3conf/ext/phpunit/Composer/vendor/bin/phpunit -c typo3/sysext/core/Build/FunctionalTests.xml {}'
	then
		return 0
	else
		return 99
	fi
}
function unitTestCommand {
	if ./typo3conf/ext/phpunit/Composer/vendor/bin/phpunit -c typo3/sysext/core/Build/UnitTests.xml
	then
		return 0
	else
		return 99
	fi
}
function phpLint {
	if find . -name \*.php | parallel --gnu --keep-order 'php -l {}' > /tmp/errors
	then
		return 0
	else
		grep -v "No syntax errors detected in" /tmp/errors
		return 99
	fi
}
