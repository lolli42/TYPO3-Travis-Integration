#!/bin/bash


pecl install igbinary > /dev/null
printf "no\n" | pecl install memcache > /dev/null
echo "extension=\"memcache.so\"" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
echo "extension=\"igbinary.so\"" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
