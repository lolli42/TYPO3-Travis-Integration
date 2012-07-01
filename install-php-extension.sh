#!/bin/bash


name=$1
pecl install $name
[ -z "`grep $name.so $phpConfigFile`" ] && echo "extension=\"$name.so\"" >> $phpConfigFile


