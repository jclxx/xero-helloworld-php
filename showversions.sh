#!/bin/sh

# showversions.sh
# show dependency versions for xero-helloworld
# jcl/jclxx/2018-10-24

set -e

echo "== versions of required parts =="

uname -s -r -m | awk '{ print " ", $0; }'
awk -F= '/VERSION/ {print "  Ubuntu", $2; exit;}' /etc/os-release
php --version | awk '{ print " ", $1, $2; exit; }'
openssl version | awk '{ print " ", $1, $2; exit; }'
curl --version | awk '{ print " ", $1, $2; exit; }'
composer --version | awk '{print " ", $1, $2;}'
fgrep -A1 '"calcinai/xero-php"' composer.lock \
	| awk -F: '{s=s$2;}END{print " " s;}' | tr -d '",'

# end
