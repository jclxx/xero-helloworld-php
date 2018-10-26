#!/bin/sh

# installpackages.sh
# install required packages for xero-helloworld
# jcl/jclxx/2018-10-24

set -e

echo "== installing packages (via apt-get) =="

for pkg in php7.2 php7.2-curl php7.2-xml composer unzip ; do
	echo "  $pkg"
	apt-get install -q -q -y "$pkg"
done

# end
