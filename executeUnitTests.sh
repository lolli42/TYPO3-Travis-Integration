#!/bin/bash
if ./typo3conf/ext/phpunit/Composer/vendor/bin/phpunit -c typo3/sysext/core/Build/UnitTests.xml
then
	exit 0
else
	exit 99
fi

