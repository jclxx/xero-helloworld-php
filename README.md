# Xero Hello World #

This is about **Xero accounting software** and getting your own
program running against the Xero API, using the recommended
**Xero-PHP library** from Michael Calcinai.

* Xero is at https://developer.xero.com
* Xero-PHP is at https://github.com/calcinai/xero-php

It can be very difficult to get your first app started.

* The documentation is difficult to follow
* It's not in one place
* There are many errors
* It's not clear what the dependencies are

This project has a single goal: **Error free first Xero Applications**

It contains:

* Step-by-step instructions starting from newly-installed operating system.
* Installation scripts for all dependencies
* Creation scripts for the secrets and keys
* Programs are command line PHP scripts with much error-checking
* No web interfacing whatsoever
* Detailed descriptions of every error that could be found

It has both what Xero calls a Private App and a Public App.

It starts assuming you have nothing installed, not even PHP.

CAVEATS: NO CLAIM IS MADE FOR CORRECTNESS OF THIS PROGRAM IN ANY
WAY.  THERE MAY BE BETTER WAYS OF DOING EVERYTHING.
THIS IS JUST A WAY TO GET YOUR FIRST PROGRAM AUTHENTICATING.
DO NOT DO NOT DO NOT USE THIS WITH LIVE ORGANISATION ACCOUNTS.
YOU ARE RESPONSIBLE FOR THE SECURITY OF YOUR INFORMATION.

Just to repeat: DO NOT USE THIS ON LIVE COMPANY DATA.

All the example details in this README have been discarded.

## TLDR for Private App ##

Install like this:

    git clone https://github.com/jclxx/xero-helloworld-php.git
    cd xero-helloworld-php/
    sudo sh installpackages.sh
    composer require calcinai/xero-php
    sh showversions.sh 
    sh createprivapp.sh 
    cat secretprivapp/publickey.cer 

Go to https://developer.xero.com/myapps and create "New App"

* Must be type "Private App"
* Choose "Demo Company" **DO NOT USE REAL COMPANY**
* Copy the `publickey.cer` contents, paste into Xero page
* Copy the "consumer key" from Xero page into `secretprivapp/consumerkey`

Now run:

    php xero-privapp-helloworld.php 

See result:

    connecting to xero private app with consumer key LD...JZ
    public key certificate fingerprint ab5edf266f19b771079837fbf8198ff5a7348f64
    organisation 1. Name "Demo Company (UK)" Tax Number "GB 123456789"
    happy ending


Now run

    php xero-privapp-testendpoints.php

See hundreds of lines.

## TLDR for Public App ##

Make private app work, as shown above.

Now

    sh createpubapp.sh 

Go to https://developer.xero.com/myapps and create "New App"

* Must be type "Public App"; any App name
* Company or app URL, put `https://example.com`
* Copy the "consumer key" from Xero page into `secretprivapp/consumerkey`
* Copy the "consumer secret" from Xero page into `secretprivapp/consumersecret`

Run

    php xero-pubapp-helloworld.php 

Copy the output URL:

    https://api.xero.com/oauth/Authorize?oauth_token=*TOKEN*

Paste into browser, grant access, see numeric validation code:

    code from xero: [type validation code here]

See output:

    Organisations
      1. Name "Demo Company (UK)" Tax Number "GB 123456789"
    Contacts
      1. Name "Gable Print"
      2. Name "Office Supplies Company"
      3. Name "Wilson Periodicals"
      4. Name "Fulton Airport Parking"
      5. Name "Pret A Manger"
      (and 43 more)
    happy ending

## Known Good Versions ##

These are known to work:

    sh showversions.sh
    == versions of required parts ==
      Linux 4.15.0-29-generic x86_64
      Ubuntu "18.04.1 LTS (Bionic Beaver)"
      PHP 7.2.10-0ubuntu0.18.04.1
      OpenSSL 1.1.0g
      curl 7.58.0
      Composer 1.6.3
      calcinai/xero-php v1.8.3

## Introduction ##

You need to have

* A user account at https://xero.com
* A Demo Company organisation at Xero (Visible in https://go.xero.com/Dashboard/)
* A developer account at Xero (from http://developer.xero.com)

You also need a Linux computer with outgoing internet access.

* Tested on Ubuntu 18.04.1 LTS server, on VirtualBox
* Tested on Xubuntu 18.04 desktop
* It is likely to work on many other systems but hasn't been tested

These notes were tested on freshly installed systems.  If you
find errors running, you might try a new virtual computer
and install from there.

All of this was done with Xero's UK offering.  It has not been
tested yet against the other countries' systems.

## What is a Xero App? ##

If you want to write some software which interacts with your Xero accounts,
to run a stock-control system or make custom reports, you need
to connect to Xero's web-based API.

There are two parts to this:

* Writing the code
* Getting all the authentication to work

In these notes

* **program** means the code which does something,
* **app** means the registration of authentication details
at your Xero developer page

Apps are registered at your page at https://developer.xero.com/myapps.

You can have lots of different programs run as a single Xero App.

Xero apps use Oauth 1.0a to authenticate themselves.

### Private App ###

There are several kinds of Xero App: "private", "public", and "partner".

A "private application" works with Xero like this:

* You create a X.509 private key and make a certificate
* You register the app at https://developer.xero.com/myapps
* You choose which organisation's information is connected to this app
* Only a single organisation's accounts are connected to this app
* Xero generates a "consumer key", a 30-character ID for this application
* You upload the certificate to the app page, which Xero confusingly calls the "Public Key"
* You download the consumer key and tell it to your program
* When the program runs, it says which app it wants with the
consumer key, and encrypts it with the private key
* Xero sees which app is required, and checks the validity of the sender by checking the transmitted information with the public key

The consumer key is just an unguessable identifier for the app,
not particularly secret.  You can change the consumer key at any
time on the app's page -- which will prevent someone who stole your
laptop from seeing your accounts.  (Xero also generates a different
thing called the "consumer secret", but this isn't used at all for
a private app.)

The public key is the way that Xero validates requests from
the actual program, which signs them with the private key.
You can upload a new public key whenever you like.  (Despite Xero's
usage, it's not actually a public key, it's a signed certificate.)
Again, if you create a new key and make a new certificate and upload
it, that will prevent anyone who has the old key from accessing
your organisation's data.

"App" is just about authentication.  You can register an app
without any program code at all (obviously it will do nothing),
but you do have to have a signed certificate.

### Public Apps ###

A "public application" works like this

* You register the app at https://developer.xero.com/myapps
* Xero generates a "consumer key", a 30-character ID for this application
* Xero generates a "consumer secret", a 30-character secret for signing requests
* You don't need any X.509 keys or certificates
* When the program runs, it presents a one-time URL to the user at `https://api.xero.com/oauth/Authorize?...`
* The user clicks through and has to log in
* The user grants (or doesn't) access to a given organisations's accounts
* Xero gives you a numeric validation number
* The user inputs the validation code into your program
* Your program converts the validation code into an access token
* Now your program can make API calls and find out details from the accounts
* The access token expires after 30 minutes

There are two methods for Xero to give you the validation code,
chosen by your program.

When the user clicks through the authorisation, either

* Xero shows you a page with the validate code
* Xero redirects to a a "callback URL" which you choose, and it makes a GET request with `oauth_verifier=*magicnumber*`

## Partner Apps ##

A "partner application" is a variety of public application which
has been specially upgraded by Xero, and they have longer than
30 minutes.  You create a public app and then get them to
upgrade it.  We don't consider partner apps any further.

## Installing the Private App ##

This project is just the simplest "Hello World" type Xero application,
using Xero-PHP interface.

It's a command-line program with no web action at all.  This is the
easiest way to understand the authentication.

This is for a "Private Application", which is only used for
connecting to a single Xero Organisation.  It's the simplest kind
of app.

    git clone https://github.com/jclxx/xero-helloworld-php.git
    cd xero-helloworld-php/
    sudo sh installpackages.sh
    sh createprivapp.sh
    cat secretprivapp/publickey.cer

Now go to https://developer.xero.com/myapps and click "New App" (top right).

* Choose "Private App"
* Give the App name `Hello World Private` (or whatever you like)
* Choose Organisation `Demo Company (UK)` (or whatever you like)
* DO NOT USE A REAL ORGANISATION ACCOUNTS
* Copy/paste your certificate into the "Public Key" (or select file to upload)
* Accept the conditions and press Create App

In confirmation you will see the Xero page for this app.

After you upload the Public Key, you can delete the "nag" file:

    rm ./secretprivapp/NAGABOUTKEY

On the Xero app page

* Find the "OAuth 1.0a Credentials" and see the "Consumer key"
* Click "Copy"

Edit this key into your file:

    nano secretprivapp/consumerkey

That completes the setup of the Xero Private App at Xero.

Now install Xero-PHP and create the autoloader:

    composer require calcinai/xero-php
    
## Running Private App Hello World ##

Run

    php xero-privapp-helloworld.php

Your should see:

    connecting to xero private app with consumer key 3A...XI
    public key certificate fingerprint e2b0059cdf0d45fd1bcd730aa83b8d620efbe37b
    organisation 1. Name "Demo Company (UK)" Tax Number "GB 123456789"
    happy ending

That's the API call authenticating and working, showing the organisation
whose accounts we're looking at, and one of the other things
from the organisation record, the tax number.

If you get an error, refer to Troubleshooting Private App, below.

## Running Private App Test End Points ##

There's a longer version of the same program, for seeing the
output of many different endpoints.

Run it with:

    php xero-privapp-testendpoints.php 

You should see several hundred lines of output, beginning with

    connecting to xero private app with consumer key 3A...XI
    public key certificate fingerprint e2b0059cdf0d45fd1bcd730aa83b8d620efbe37b
    == 1 'Accounting\Account' ==
      1. Code="090" Name="Business Bank Account" Class="ASSET"
      2. Code="091" Name="Business Savings Account" Class="ASSET"
      3. Code="200" Name="Sales" Class="REVENUE"

If you don't, look at the error and refer to Troubleshooting Private App, below.

## Installing the Public App ##

First, make the Private App work, as shown above.  This ensures
all the dependencies are met.

Then

    sh createpubapp.sh

Now create the Public App

* Go to https://developer.xero.com/myapps and click "New App" (top right).
* Choose "Public App"
* Give the App name `Hello World Public` (or whatever you like)
* For company URL, give `http://example.com` (or whatever you like)
* Click "Add an OAuth 1.0a Callback Domain"
* Add the domain `localhost` (use this exact domain)
* Accept the conditions and press Create App

In confirmation you will see the Xero page for this app.
On the Xero app page look for the OAuth 1.0a Credentials.

* Find the "Consumer key" and click "Copy"
* Edit this into the file `secretpubapp/consumerkey`
* Find the "Consumer secret" and click "Copy"
* Edit this into the file `secretpubapp/consumersecret`
 
That completes the setup of the Xero Public App at Xero.

## Running the Public App ##

Run it

    php xero-pubapp-helloworld.php
    
You should see

    need to authenticate at xero
    connecting to xero public app without callback
      consumer key/secret = E8...A1/E8...A1
    getting REQUEST token
    xero sent a request token (PZ...NV:DI...KN)
    user must now get code from xero:
      https://api.xero.com/oauth/Authorize?oauth_token=*TOKEN*
    waiting for VALIDATION code
    code from xero: []

Copy and paste the link into your browser to go to Xero's web site.
If necessary, it will ask you to log in, and if you have multiple
organisations (perhaps several companies) you choose one to grant
access to.

WARNING: DO NOT GRANT ACCESS TO A REAL ORGANISATION.
ONLY USE THE "DEMO COMPANY".  Seriously.

If you grant the access, you'll be given a numeric code.  Copy
and paste it into the program.

    code from xero: 1234567
    validate code = 1234567
    getting ACCESS token
    xero gave us access token (AP...3C:OM...JK, valid 1800 sec)
    saved creds in ./secretpubapp/SESSION
    credentials expire 2018-10-26 22:39:17

The program converts the validation code into an access token,
which it stores (`secretpubapp/SESSION`).  Then it continues and
makes some API calls to get information about the Organisation
and the Contacts.

    connecting to xero public app
    consumer key/secret = E8...A1/F2...HA
    Organisations
      1. Name "Demo Company (UK)" Tax Number "GB 123456789"
    Contacts
      1. Name "Gable Print"
      2. Name "Office Supplies Company"
      3. Name "Wilson Periodicals"
      4. Name "Fulton Airport Parking"
      5. Name "Pret A Manger"
      (and 43 more)
    happy ending

If you run the program again, it will see that it has
credentials (from `secretpubapp/SESSION`) and not take you
through the authentication process.

    already authenticated
    loaded creds AP...3C:OM...JK from ./secretpubapp/SESSION
    credentials expire 2018-10-26 22:39:17

To log the program out from Xero, just delete the session information:

    rm secretpubapp/SESSION

Note that the information in the `SESSION` is secret: for a real
organisation this would give access to the organisations accounts.

## Running the Public App with Callback ##

Instead of getting the validation code and pasting it into
your program, the normal method is to use a "callback".
In this method, your program gives a URL to the authentication
request, and after the user has granted access, Xero gives the
browser a redirect to the URL the program gave.

    http://localhost/callback?oauth_token=*ACCESSTOKEN*&oauth_verifier=*VERIFICATIONCODE*&org=*ORGID*

For demonstration purposes we don't need to use an actual web site.

Run the public app program again but with a URL (use this exact literal URL)

    rm secretpubapp/SESSION  # this will force another authentication
    php xero-pubapp-helloworld.php http://localhost/callback

You'll be presented with an Authorize URL, which you cut and paste
into the browser as before.

    user must now get code from xero:
      https://api.xero.com/oauth/Authorize?oauth_token=*TOKEN*

After granting permission at Xero (DO NOT USE A REAL
ORGANISATION), you will be taken to
a `http://localhost/callback` URL, which will fail because
you have no web server on your local computer.

Examine the URL and extract the numeric validation code
after `oauth_verifier=**1234567**`

Copy/paste this into the prompt from the program as before:

    waiting for VALIDATION code
    code from xero: 1234567

Note that the URL is resolved by the browser, which means that
you can pass through internal resources which are not accessible
to Xero or the wider internet.  So `https://localhost/thing`
is resolved to the browsing computer, not Xero's server.

Xero explains the details of the callback URL here:

## Links ##

* Excellent description of how OAuth works [link](https://www.cubrid.org/blog/dancing-with-oauth-understanding-how-authorization-works)
* Description of Xero's use of OAuth [link](https://developer.xero.com/documentation/auth-and-limits/oauth-issues)
* Description of callbacks [link](https://developer.xero.com/documentation/auth-and-limits/oauth-callback-domains-explained)

## Troubleshooting the Private App ##

### ERROR: missing composer parts ###

If the dependencies aren't there, because you didn't run `composer` or because they have become damaged,

    ERROR: missing composer parts
      did you run "composer require calcinai/xero-php" ?

Fix: run 

    composer require calcinai/xero-php

### ERROR: ./secretprivapp/consumerkey is missing or empty ###

If you didn't create the file for the consumer key, or it has become damaged

    ERROR: ./secretprivapp/consumerkey is missing or empty
      did you run "sh createprivapp.sh" ?

Fix: make the files with

    sh createprivapp.sh

### WARNING: dummy consumer key ###

If you forget to update `secretprivapp/consumerkey` from that on
the Xero page, the program will warn you.

    WARNING: dummy consumer key
      expect "Consumer key was not recognised" error from xero
      fix by copying consumer key from xero app page to ./secretprivapp/consumerkey

### XERO ERROR 401: Consumer key was not recognised ###

If the consumer key doesn't match one at Xero, you need to
copy it from Xero to the program.

    XERO ERROR 401: Consumer key was not recognised

Fix: copy the consumer key from the Xero page to `secretprivapp/consumerkey`

### WARNING: ./secretprivapp/NAGABOUTKEY exists ###

When the X.509 private key and certificate are made
(by `createprivapp.sh`), it also makes a file `NAGABOUTKEY`,
which will nag you about uploading the public key certificate
to the Xero app page.

If you don't upload the key, you will also see `Failed to validate
signature` errors.  As this error could be caused by many
things, we make the program nag about things it knows would cause this.

If you definitely uplodoaded the public key but forgot to
remove the `NAGABOUTKEY` file, it will be removed automatically
after the first successfull Xero API call, as that won't
happen unless the key was uploaded correctly.

    WARNING: ./secretprivapp/NAGABOUTKEY exists
      expect "Failed to validate signature" from xero
      fix by copying ./secretprivapp/publickey.cer to xero app page
      delete ./secretprivapp/NAGABOUTKEY to remove this warning

### XERO ERROR 401: Failed to validate signature ###

If they program can't create the correct signatures, the
Xero site will say that the signature isn't validated.  There
could be many causes of this, but the most common is that
the private key used in the program's requests doesn't
match the consumer key and uploaded public key at Xero.

    XERO ERROR 401: Failed to validate signature

Look at the output of xero-privapp-helloworld.php

    connecting to xero private app with consumer key 3A...XI
    public key certificate fingerprint e2b0059cdf0d45fd1bcd730aa83b8d620efbe37b

Fix: check these against the values shown on the Xero page.
If necessary repeat the steps at "Installing Hello World Private App", above.

Note that immediately after changing a public key certificate,
it takes about 10 seconds for the key to be work and up
to about 30 seconds for all of Xero's servers to know about it.
You can find interspersed success and failures from one API call
to the next, presumably because different internal servers
are handling the requests, and they have not yet all been updated.
If you have definitely copied the key and consumer secret
correctly, try waiting a 30 seconds and trying again.

## XERO ERROR 401: The access token has not been authorized, or has been revoked by the user ##o

If you delete the app or change the keys at Xero app page, the
private key will no longer match anything.

    XERO ERROR 401: The access token has not been authorized, or has been revoked by the user

Fix: recreate the app at Xero.

## Troubleshooting the Public App ##

### ERROR: ./secretpubapp/consumerkey is missing or empty ###

There is no consumer key file or it's empty.  Probably because you
didn't run `createpubapp.sh`

    ERROR: ./secretpubapp/consumerkey is missing or empty
      did you run "sh createpubapp.sh" ?

Fix: run the creation script

    sh createpubapp.sh

### WARNING: dummy consumer key ###

When the `createpubapp.sh` script creates its files, it
puts a dummy value for the consumer key.

    WARNING: dummy consumer key
      expect "Unknown Consumer" error from xero
      fix by copying consumer key from xero app page to ./secretpubapp/consumerkey

Fix: copy the consumer key from the Xero app page to the file `secretpubapp/consumerkey`

### XERO ERROR 401 (during REQUESTTOKEN): Unknown Consumer ###

If Xero doesn't recognise the consumer key it doesn't know what app you want.

    XERO ERROR 401 (during REQUESTTOKEN): Unknown Consumer (Realm: , Key: DUMMYPUBLICAPPCONSUMERKEY00000)
    Problem: consumer_key_unknown
      Unknown Consumer (Realm: , Key: DUMMYPUBLICAPPCONSUMERKEY00000)

Fix: properly copy the consumer key from Xero app into `secretpubapp/consumerkey`

### WARNING: dummy consumer secret ###

When the `createpubapp.sh` script creates its files, it puts a
dummy value for the consumer secret.  If you don't have the
correct consumer secret -- because you have the dummy one --
it will cause an invalid signature error from Xero.

    WARNING: dummy consumer secret
      expect "Consumer "Failed to validate signature" error from xero
      fix by copying consumer secret from xero app page to ./secretpubapp/consumerkey
      
Fix: copy the consumer secret from Xero app page into `secretpubapp/consumersecret`

### XERO ERROR 401 (during REQUESTTOKEN): Failed to validate signature ###

This is a general encryption failure, which just means that the
program didn't use the correct credentials to encrypt its request.
It can be cause by any number of things, but basically it
means the consumer secret in the program doesn't match that
from the Xero app page.  The most common reason is that they
were not copied from Xero to the file; or copied incorrectly.

    XERO ERROR 401 (during REQUESTTOKEN): Failed to validate signature
    Problem: signature_invalid
      Failed to validate signature

Fix: properly copy the consumer secret from the Xero app page to the file secretpubapp/consumersecret



### XERO ERROR 401 (during ACCESSTOKEN): The consumer was denied access to this resource. ###

During the authentication the user is shown a Xero page to
grant access to the organisation's accounts, and if granted
gives the user a validation code.  If the validation code
is no good becuase it's too old, has been used before, belongs
to a different request, it was mistyped, or it was just
guessed, Xero will deny access.

   XERO ERROR 401 (during ACCESSTOKEN): The consumer was denied access to this resource.
   Problem: permission_denied
     The consumer was denied access to this resource.
  
Fix: try program again and grant access to a company, carefully
copying and pasting the validation code into the program

### XERO ERROR 400 (during REQUESTTOKEN): Bad Request ###

If the program supplies a callback URL to the authorisation request,
that URL has to be in the domain which is registered in the Xero app page.

XERO ERROR 400 (during REQUESTTOKEN): Bad Request
Problem: parameter_rejected
  Callback url is not in the registered callback domain

Fix: ensure that the URL passed into the program is in the
domain from the Xero app's page.

### XERO ERROR 401: The access token has expired ###

The grant of access by the user is only valid for a period of
time, currently 30 minutes.  After 30 minutes, a new access
key is required which means new authentication is required.

    XERO ERROR 401: The access token has expired

Fix: remove `secretpubapp/SESSION` and retry.

(end)
