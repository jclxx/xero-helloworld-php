<?php

# xero-pubapp-helloworld.php
# minimal xero public application
# jcl/jclxx/2018-10-24

#################################################################
# HELLO WORLD FOR XERO ACCOUNTING ("PUBLIC" APP)
# see http://developer.xero.com
#
# simple hello-world program to test public app authenticaton
# from php using xero-php library.  it's the simplest possible.
#
# this is for "public" app, not "private" or "partner".
#
# requires php7.2 php7.2-curl php7.2-xml composer unzip
# then "composer require calcinai/xero-php"
# also SECRETSDIR/consumerkey (from xero app page)
# also SECRETSDIR/consumersecret (from xero app page)
#
# make xero-privapp-helloworld.php work before using this program,
# then you know you have all the dependencies correct.
#################################################################


#################################################################
# PREREQUISITES
#################################################################

# check composer parts seem to be in place
# it gives an autoloader to load classes on demand
#
if (!file_exists('composer.json') || !file_exists('vendor/autoload.php')) {
        printf("ERROR: missing composer parts\n");
        printf("  did you run \"composer require calcinai/xero-php\" ?\n");
        exit (1);
}
include 'vendor/autoload.php';

use XeroPHP\Application\PublicApplication;
use XeroPHP\Remote\Request;
use XeroPHP\Remote\URL;

#################################################################
# CONFIG
#################################################################

# we expect our secrets in here
#
define('SECRETSDIR', './secretpubapp');
define('CONSUMERKEYFILE',	SECRETSDIR . '/consumerkey');
define('CONSUMERSECRETFILE',	SECRETSDIR . '/consumersecret');
define('SESSIONFILE',		SECRETSDIR . '/SESSION');

# minimal config for private app
#       do not use "file://" construction for consumer_key or consumer_secret
#	callback can be set from command line, see later
#
$config = [
  'oauth' => [
    'callback'         => "oob",	# "out of band", ie manual
    'consumer_key'     => trim(@file_get_contents(CONSUMERKEYFILE)),
    'consumer_secret'  => trim(@file_get_contents(CONSUMERSECRETFILE)),
    'rsa_private_key'  => 'not used in public app',
  ],
];

# check we have a consumer key and consumer secret
#
if (!$config['oauth']['consumer_key']) {
	printf("ERROR: %s is missing or empty\n", CONSUMERKEYFILE);
	printf("  did you run \"sh createpubapp.sh\" ?\n");
	exit (1);
}
if (!$config['oauth']['consumer_secret']) {
	printf("ERROR: %s is missing or empty\n", CONSUMERSECRETFILE);
	printf("  did you run \"sh createpubapp.sh\" ?\n");
	exit (1);
}

# check it's not the dummy from the create script
#
if (preg_match("/DUMMY/", $config['oauth']['consumer_key'])) {
	printf("WARNING: dummy consumer key\n");
	printf("  expect \"Unknown Consumer\" error from xero\n");
	printf("  fix by copying consumer key from xero app page to %s\n",
	CONSUMERKEYFILE);
}

if (preg_match("/DUMMY/", $config['oauth']['consumer_secret'])) {
	printf("WARNING: dummy consumer secret\n");
	printf("  expect \"Consumer \"Failed to validate signature\" error from xero\n");
	printf("  fix by copying consumer secret from xero app page to %s\n",
	CONSUMERKEYFILE);
}


#################################################################
# UTILITY
#################################################################

# little functions to show token summaries
#
function offuscare($s)
{
	return (substr($s, 0, 2) . "..." . substr($s, -2, 2));
}

#################################################################
# AUTHENTICATION
#################################################################

if (isset($argv[1])) {
	$callbackurl = $argv[1];
	$config['oauth']['callback'] = $callbackurl;
} else {
	$callbackurl = false;
}


$xero = new PublicApplication($config);

if (file_exists(SESSIONFILE)) {
	#########################
	# already logged in
	#########################

	printf("already authenticated\n");
	$creds = unserialize(file_get_contents(SESSIONFILE));
	printf("loaded creds %s from %s\n",
		sprintf("%s:%s",
			offuscare($creds['oauth_token']),
			offuscare($creds['oauth_token_secret'])),
		SESSIONFILE);

} else {
	#########################
	# need to log in
	#########################

	printf("need to authenticate at xero\n");
	printf("connecting to xero public app %s\n",
		$callbackurl
			? "with callback to $callbackurl"
			: "without callback");
	
	printf("  consumer key/secret = %s/%s\n",
		offuscare($config['oauth']['consumer_key']),
		offuscare($config['oauth']['consumer_key']));

	# do OAuth 1.0a 3-leg handshake
	# 1.  Get a Request Token (identifies this app to xero)
	# 2.  This generates a Authorisation URL (user clicks on that)
	# 3.  User logs in at xero, grants permission to a company
	# 	for our app and either
	# 	a) does a callback with 7-digit code
	# 	b) shows user a 7-digit code directly
	# 4.  User puts code into our app
	# 5.  We ask for an Access Token
	# 6.  We use the Access Token to make requests

	# ask for a REQUEST token
	#
	$url = new URL($xero, URL::OAUTH_REQUEST_TOKEN);
	$request = new Request($xero, $url);
	try {
		printf("getting REQUEST token\n");
		$request->send();
	} catch (Exception $e) {
		printf("XERO ERROR %s (during REQUESTTOKEN): %s\n",
			$e->getCode(),
			$e->getMessage());
		$x = $request->getResponse()->getOAuthResponse();
		printf("Problem: %s\n", $x['oauth_problem']);
		printf("  %s\n", $x['oauth_problem_advice']);
		exit (1);
	}

	# we have the REQUEST token
	#	it's a token and a token secret
	#
	$creds = $request->getResponse()->getOAuthResponse();
	printf("xero sent a request token (%s:%s)\n",
		offuscare($creds['oauth_token']),
		offuscare($creds['oauth_token_secret']));

	# convert REQUEST token into VALIDATION url
	#	give user a URL based on the token
	#
	$url = $xero->getAuthorizeURL($creds['oauth_token']);

	# tell use to log in at xero and AUTHORISE us
	#
	printf("user must now get code from xero:\n");
	printf("  %s\n", $url);

	# get the VALIDATION code from the user (or callback)
	# 	normally would get a callback to URL in $config
	# 	(must match domain in xero app page)
	#
	printf("waiting for VALIDATION code\n");
	$validatecode = readline("code from xero: ");

	# convert the VALIDATION code to an ACCESS token
	#
	printf("validate code = %s\n", $validatecode);
	$xero->getOAuthClient()->setToken($creds['oauth_token']);
	$xero->getOAuthClient()->setTokenSecret($creds['oauth_token_secret']);
	$xero->getOAuthClient()->setVerifier($validatecode);

	# ask for an ACCESS token
	#	uses the REQUEST token and the VALIDATION code
	#
	$url = new URL($xero, URL::OAUTH_ACCESS_TOKEN);
	$request = new Request($xero, $url);
	try {
		printf("getting ACCESS token\n");
		$request->send();
	} catch (Exception $e) {
		printf("XERO ERROR %s (during ACCESSTOKEN): %s\n",
			$e->getCode(),
			$e->getMessage());
		$x = $request->getResponse()->getOAuthResponse();
		printf("Problem: %s\n", $x['oauth_problem']);
		printf("  %s\n", $x['oauth_problem_advice']);
		exit (1);
	}

	# we have the ACCESS token
	#
	$creds = $request->getResponse()->getOAuthResponse();
	$creds['expires'] = time() + $creds['oauth_expires_in'];
	printf("xero gave us access token (%s:%s, valid %d sec)\n",
		offuscare($creds['oauth_token']),
		offuscare($creds['oauth_token_secret']),
		$creds['oauth_expires_in']);

	# save it in a file so we can re-use it
	#
	file_put_contents(SESSIONFILE, serialize($creds));
	printf("saved creds in %s\n", SESSIONFILE);
}

#################################################################
# LOGGED IN
#################################################################

# LOGGED IN
#	at this point $creds has oauth_token and oauth_token_secret
#	(and also expiry time)
#
printf("credentials expire %s\n",
	strftime("%Y-%m-%d %H:%M:%S", $creds['expires']));
if ($creds['expires'] <= time()) {
	printf("WARNING: expired %d seconds ago\n", time() - $creds['expires']);
	printf(" expect error \"The access token has expired\" from xero\n");
	printf(" fix by removing %s\n", SESSIONFILE);
}

# fill/refill our structures with the authenticated token
#
$xero->getOAuthClient()->setToken($creds['oauth_token']);
$xero->getOAuthClient()->setTokenSecret($creds['oauth_token_secret']);

printf("connecting to xero public app\n");
printf("consumer key/secret = %s/%s\n",
	offuscare($config['oauth']['consumer_key']),
	offuscare($config['oauth']['consumer_secret']));

#################################################################
# MAIN API CALLS
#################################################################

try {
	$organisations = $xero->load('Accounting\\Organisation')->execute();
} catch (Exception $e) {
	printf("XERO ERROR %s: %s\n", $e->getCode(), $e->getMessage());
	exit (1);
}

printf("Organisations\n");
foreach ($organisations as $i => $organisation) {
	printf("  %d.", $i + 1);
	printf(" Name \"%s\"", $organisation->Name);
	printf(" Tax Number \"%s\"", $organisation->TaxNumber);
	printf("\n");
	#print_r($organisation);
}

try {
	$things = $xero->load('Accounting\\Contact')->execute();
} catch (Exception $e) {
	printf("XERO ERROR %s: %s\n", $e->getCode(), $e->getMessage());
	exit (1);
}
printf("Contacts\n");
foreach ($things as $i => $thing) {
	printf("  %d.", $i + 1);
	printf(" Name \"%s\"", $thing->Name);
	printf("\n");
	if ($i == (5-1)) {
		printf("  (and %d more)\n", count($things) - 5);
		break;
	}
}

printf("happy ending\n");
exit (0);

# end
