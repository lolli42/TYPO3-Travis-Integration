#!/bin/bash
if grep directory typo3/sysext/core/Build/FunctionalTests.xml | sed 's#[	]*<directory>\.\./\.\./\.\./\.\./\(typo3/sysext.*\)</directory>$#\1#g' | parallel --gnu --keep-order 'echo "Running {} tests"; ./typo3conf/ext/phpunit/Composer/vendor/bin/phpunit -c typo3/sysext/core/Build/FunctionalTests.xml {}'
then
	exit 0
else
	exit 99
fi
