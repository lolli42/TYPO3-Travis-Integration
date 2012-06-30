#!/bin/bash

# install igbinary
pyrus install pecl/igbinary && pyrus build pecl/igbinary
echo "extension=\"igbinary.so\"" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`

