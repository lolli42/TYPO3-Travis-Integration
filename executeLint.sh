#!/bin/bash
if find typo3/ -name \*.php | parallel --gnu --keep-order 'php -l {}' > /tmp/errors
then
	exit 0
else
	grep -v "No syntax errors detected in" /tmp/errors
	exit 99
fi
