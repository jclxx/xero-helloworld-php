<?php

# xero-privapp-testendpoints.php
# nearly minimal xero private application
# jcl/jclxx/2018-10-24

#################################################################
# HELLO WORLD FOR XERO ACCOUNTING ("PRIVATE" APP)
# see http://developer.xero.com
#
# simple hello-world program to test private app authenticaton
# from php using xero-php library.  it tests lots of endpoints
# and prints just a few useful fields from each one.
#
# this is for "private" app, not "public" or "partner".
#
# make xero-privapp-helloworld.php work before using this one.
#
# requires php7.2 php7.2-curl php7.2-xml composer unzip
# then "composer require calcinai/xero-php"
# also SECRETSDIR/consumerkey (from xero app page)
# also SECRETSDIR/privatekey.pem (see accompanying createprivapp.sh)
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

use XeroPHP\Application\PrivateApplication;

#################################################################
# CONFIG
#################################################################

# we expect our secrets in here
#
define('SECRETSDIR', './secretprivapp');
define('CONSUMERKEYFILE',	SECRETSDIR . '/consumerkey');
define('PRIVATEKEYFILE',	SECRETSDIR . '/privatekey.pem');
define('PUBLICKEYFILE',		SECRETSDIR . '/publickey.cer');
define('NAGFILE',		SECRETSDIR . '/NAGABOUTKEY');

# minimal config for private app
#	callback and consumer_secret required but value irrelevant
#	do not use "file://" construction for consumer_key
#
$config = [
  'oauth' => [
    'callback'         => "not used in private apps",
    'consumer_key'     =>
      trim(@file_get_contents(CONSUMERKEYFILE)),
    'consumer_secret'  => "not used in private apps",
    'rsa_private_key'  => "file://" . PRIVATEKEYFILE,
  ],
];

# check we have a consumer key
#
if (!$config['oauth']['consumer_key']) {
	printf("ERROR: %s is missing or empty\n", CONSUMERKEYFILE);
	printf("  did you run \"sh createprivapp.sh\" ?\n");
	exit (1);
}

# check it's not the dummy from the create script
#
if (preg_match("/DUMMY/", $config['oauth']['consumer_key'])) {
	printf("WARNING: dummy consumer key\n");
	printf("  expect \"Consumer key was not recognised\" error from xero\n");
	printf("  fix by copying consumer key from xero app page to %s\n",
		CONSUMERKEYFILE);
}

# check we have keys
#
if (!@file_get_contents(PRIVATEKEYFILE)) {
	printf("ERROR: %s is missing or empty\n", PRIVATEKEYFILE);
	printf("  did you run \"sh createprivapp.sh\" ?\n");
	exit (1);
}
if (!@file_get_contents(PUBLICKEYFILE)) {
	printf("ERROR: %s is missing or empty\n", PUBLICKEYFILE);
	printf("  did you run \"sh createprivapp.sh\" ?\n");
	exit (1);
}

# nag about uploading the public key if needed
#
if (file_exists(NAGFILE)) {
	printf("WARNING: %s exists\n", NAGFILE);
	printf("  expect \"Failed to validate signature\" from xero\n");
	printf("  fix by copying %s to xero app page\n", PUBLICKEYFILE);
	printf("  delete %s to remove this warning\n", NAGFILE);
}

#################################################################
# UTILITY
#################################################################

# to show token summaries
#
function offuscare($s)
{
	return (substr($s, 0, 2) . "..." . substr($s, -2, 2));
}

#################################################################
# MAIN
#################################################################

# say what we're doing
#
printf("connecting to xero private app with consumer key %s\n",
	offuscare($config['oauth']['consumer_key']));
printf("public key certificate fingerprint %s\n",
	openssl_x509_fingerprint(file_get_contents(PUBLICKEYFILE)));

# create the structures
#
$xero = new PrivateApplication($config);

# list of app the api calls we are going to make
#
# all the endpoints we could find, and a few fields to print for each
# (or false to skip and true for print_r)
#
# note they are all plural names in xero api documentation
# but singular in the xero-php implementation
#
# see https://developer.xero.com/documentation/api
#
$thingnames = [
	'Accounting\\Attachment' => false,
	'Accounting\\Account' => ['Code', 'Name', 'Class'],
	'Accounting\\BankStatement' => false,
	'Accounting\\BankTransaction' => ['Type', 'CurrencyCode', 'Total'],
	'Accounting\\BankTransfer' => true,
	'Accounting\\BrandingTheme' => ['Name'],
	'Accounting\\Contact' => ['Name', 'EmailAddress'],
	'Accounting\\ContactGroup' => ['Name'],
	'Accounting\\CreditNote' => ['Type', 'CurrencyCode', 'Total'],
	'Accounting\\Currency' => ['Code', 'Description'],
	'Accounting\\Employee' => ['FirstName', 'Lastname', 'Status'],
	'Accounting\\ExpenseClaims (Deprecated)' => false,
	'Accounting\\History' => false,
	'Accounting\\Invoice' => ['InvoiceNumber', 'CurrencyCode', 'Total'],
	'Accounting\\InvoiceReminder' => false,
	'Accounting\\Item' => ['Code', 'Name', 'QuantityOnHand'],
	'Accounting\\Journal' => ['JournalNumber'],
	'Accounting\\LinkedTransaction' => true,
	'Accounting\\ManualJournal' => ['Narration', 'Status'],
	'Accounting\\Organisation' => ['Name', 'TaxNumber'],
	'Accounting\\Overpayment' => true,
	'Accounting\\PaymentServices' => false,
	'Accounting\\Payment' => ['Amount', 'Reference', 'Status'],
	'Accounting\\Prepayment' => true,
	'Accounting\\PurchaseOrder' =>
		['PurchaseOrderNumber', 'CurrencyCode', 'Total'],
	'Accounting\\Quote' => false,
	'Accounting\\Receipts (Deprecated)' => false,
	'Accounting\\RepeatingInvoice' => ['Type', 'CurrencyCode', 'Total'],
	'Accounting\\Report' => false,
	'Accounting\\TaxRate' => ['Name', 'Status', 'EffectiveRate'],
	'Accounting\\TrackingCategory' => ['Name', 'Status'],
	'Accounting\\User' => ['FirstName', 'OrganisationRole'],
];

# now make all the api calls
#
$ii = 0;
foreach ($thingnames as $thingname => $fields) {
	if ($fields === false) {
		# deprecated or unknown endpoints
		continue;
	}

	printf("== %d '%s' ==\n", $ii + 1, $thingname);
	try {
		$things = $xero->load($thingname)->execute();
	} catch (Exception $e) {
		printf("XERO ERROR %s: %s\n", $e->getCode(), $e->getMessage());
		exit (1);
	}

	if (file_exists(NAGFILE)) {
		unlink(NAGFILE);

		printf("PS: deleted %s after successful call\n",
			NAGFILE);
	}

	foreach ($things as $i => $thing) {
		if ($fields === true) {
			# don't know how to print this
			print_r($thing);
			continue;
		}
		printf("  %d.", $i + 1);
		foreach ($fields as $field) {
			printf(" %s=\"%s\"", $field, $thing->$field);
		}
		printf("\n");
	}

	++$ii;
}

printf("happy ending\n");
exit (0);

# end
