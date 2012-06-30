#!/bin/bash


name=$1
pecl install $name
echo "extension=\"$name.so\"" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`



