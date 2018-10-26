# Xero Hello World #

This is about Xero the accounting software and getting your own program running against the Xero API, using the Xero-PHP library.

It can be tricky reading through all the Xero documentation
to get your first Xero application running.  In particular there are errors at Xero-PHP and the information is not in a single place.

This project has a single goal: **Error free first Xero Applications**

It contains:

 - Installation scripts for all dependencies
 - Creation scripts for the secrets
 - PHP Scripts for the apps
 - These are command line scripts with much error-checking and no web interfacing whatsoever
 - Step-by-step instructions.

It has both what Xero calls a Private App and a Public App.

## Getting started ##

You need to have

- A developer account at Xero
- A Demo Company organisation at Xero

Go to http://developer.xero.com to get these.

You also need a Linux computer with outgoing internet access.

- Tested on Ubuntu 18.04.1 LTS server, on VirtualBox
- Tested on Xubuntu 18.04 desktop
- It is likely to work on many other systems but hasn't been tested

These notes were tested on freshly installed systems.  If you find errors running, you might try a new virtual computer and install from there.

## What is a Xero App? ##

A Xero app software which interacts with the Xero API, typically for making custom reports from your accounts, or to interface a stock control system to your accounts.

In these notes

- **program** means the code which does something,
- **app** means the registration of authentication secrets at your Xero developer page

Apps are registered at your page at https://developer.xero.com/myapps.

Xero apps use Oauth 1.0a to authenticate themselves.

This is an excellent detailed description of how it works.
https://www.cubrid.org/blog/dancing-with-oauth-understanding-how-authorization-works

### Private App ###

There are several kinds of Xero App: "private", "public", and "partner".

A "private application" works with Xero like this:

- You create a X.509 private key and make a certificate
- You register the app at https://developer.xero.com/myapps
- You choose which organisation's information is connected to this app
- Only a single organisations accounts are connected to this app
- Xero generates a "consumer key", a 30-character unguessable ID for this application
- You upload the certificate to the app page, which Xero calls the "Public Key"
- You download the consumer key and tell it to your program
- When the program runs, it exchanges encrypted information with Xero over its API

The consumer key is just an unguessable identifier for the app, not particularly secret.  You can change the consumer key at any time on the app's page.  (Xero also generates a "consumer secret", but this isn't used at all for a private app.)

The public key is the way that Xero validates requests from the actual program, which signs them with the private key.  You can upload a new public key whenever you like.  (It's not actually a public key, it's a signed certificate.)

There is nothing about what the app actually does here.  It's just about authentication.  You can register an app without any program code at all (obviously it will do nothing), but you do have to have a signed certificate.

### Public Apps ###

A "public application" works like this

- You register the app at https://developer.xero.com/myapps
- Xero generates a "consumer key", a 30-character unguessable ID for this application
- Xero generates a "consumer secret", a 30-character secret for signing requests
- You don't need any X.509 keys or certificates
- When the program runs, it presents a one-time URL to the user at https://api.xero.com/oauth/Authorize?...
- The user clicks through and has to log in
- The user grants (or doesn't) access to a given organisations's accounts
- Xero gives you a magic validation number (currently 7-digit)
- The user inputs the validation code into your program
- Your program converts the validation code into an access token
- Now your program can make API calls and find out details from the accounts

There are two methods for Xero to give you the validation code, chosen by your program.

When the user clicks through the authorisation
- Xero presents a page saying "Enter this code in *appname* to finish the process", or
- Xero redirects to a a "callback URL" which you choose, and it makes a GET request with oauth_verifier=*magicnumber*

## Partner Apps ##

A "partner application" is a variety of public application which has been specially upgraded by Xero.  You create a public app and then get them to upgrade it.  We don't consider partner apps any further.

## Installing the Private App ##

This project is just the simplest "Hello World" type Xero application,
using Xero-PHP interface.

This is for a "Private Application", which is only used for
connecting to a single Xero Organisation.  It's the simplest kind
of app.

    git clone https://github.com/jclxx/xero-helloworld-php.git
    cd xero-helloworld-php/
    sudo sh installpackages.sh
    sh createprivapp.sh
    cat secretprivapp/publickey.cer

Now go to https://developer.xero.com/myapps and click "New App" (top right).

- Choose "Private App"
- Give the App name "Hello World Private" (or whatever you like)
- Choose Organisation "Demo Company (UK)" (or whatever you like)
- Copy/paste your certificate into the "Public Key)" (or select file to upload)
- Accept the conditions and press Create App

In confirmation you will see the Xero page for this app.

When you upload the Public Key, you can delete the NAG file:

    rm ./secretprivapp/NAGABOUTKEY

On the Xero app page

- Find the "OAuth 1.0a Credentials" and see the "Consumer key"
- Click "Copy"

Edit this key into your file:

    nano secretprivapp/consumerkey

That completes the setup of the Xero Private App at Xero.

Now get the program running:

    composer require calcinai/xero-php
    
## Running Private App Hello World ##

Run

    php xero-privapp-helloworld.php

Your should see:

    connecting to xero private app with consumer key 3A...XI
    public key certificate fingerprint e2b0059cdf0d45fd1bcd730aa83b8d620efbe37b
    organisation 1. Name "Demo Company (UK)" Tax Number "GB 123456789"
    happy ending

That's the API call authenticating and working, showing the organisation.

If you don't, look at the error and refer to Troubleshooting Private App, below.

## Running Private App Test End Points ##

There's a longer version of the same program, for seeing the output of many endpoints.

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

First, make the Private App work, as shown above.  This ensures all the dependencies are met.

Then

    sh createpubapp.sh

Now go to https://developer.xero.com/myapps and click "New App" (top right).

- Choose "Public App"
- Give the App name "Hello World Public" (or whatever you like)
- For company URL, give "http://example.com" (or whatever you like)
- Click "Add an OAuth 1.0a Callback Domain"
 - Add the domain "localhost" (use this exact domain)
- Accept the conditions and press Create App

In confirmation you will see the Xero page for this app.
On the Xero app page look for the OAuth 1.0a Credentials.

- Find the "Consumer key" and click "Copy"
 - Edit this into the file "secretpubapp/consumerkey"
- Find the "Consumer secret" and click "Copy"
 - Edit this into the file "secretpubapp/consumersecret"
 
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
      https://api.xero.com/oauth/Authorize?oauth_token=PZMKYAYERQHQ9K9FABCOGUYG1JKXNV
    waiting for VALIDATION code
    code from xero: []

Copy and paste the link into your browser to go to Xero's web site.
If necessary, it will ask you to log in, and they verify that you'd
like this app to have access to your accounting data.

If you grant the access, you'll be given a numeric code.  Copy and paste it into the program.

    code from xero: 2922483
    validate code = 2922483
    getting ACCESS token
    xero gave us access token (AP...3C:OM...JK, valid 1800 sec)
    saved creds in ./secretpubapp/SESSION
    credentials expire 2018-10-26 22:39:17

The program converts the validation code into an access token, which it stores (secretpubapp/SESSION).
Then it continues and makes some API calls to get information about the Organisatino and the Contacts.

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

If you run the program again, it will see that it has credentials (from secretpubapp/SESSION) and not take you through the authentication process.

    already authenticated
    loaded creds AP...3C:OM...JK from ./secretpubapp/SESSION
    credentials expire 2018-10-26 22:39:17

## Running the Public App with Callback ##

Instead of getting the validation code and cutting it and pasting it into your program, the normal method is for Xero to do a "callback".  In this method, your program gives a URL to the authentication request, and after the user has granted access, Xero gives you a redirect to the URL the program gave.

    http://localhost/callback?oauth_token=*ACCESSTOKEN*&oauth_verifier=*VERIFICATIONCODE*&org=*ORGID*

For demonstration purposes we don't need to use an actual web site.

Run the public app program again but with a URL (use this exact literal URL)

    rm secretpubapp/SESSION  # this will force another authentication
    php xero-pubapp-helloworld.php http://localhost/callback

You'll be presented with an Authorize URL, which you cut and paste into the browser as before.

    user must now get code from xero:
      https://api.xero.com/oauth/Authorize?oauth_token=*TOKEN*

After granting permission at Xero, you will be taken to a http://localhost/callback URL, which will fail because you have no web server on your local computer.

Examine the URL and extract the numeric validation code after oauth_verifier=**1234567**

Copy/paste this into the prompt from the program as before:

waiting for VALIDATION code
    code from xero: 1234567


# https://developer.xero.com/documentation/auth-and-limits/oauth-issues

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

If you forget to update secretprivapp/consumerkey from that on the Xero page, the program will warn you.

    WARNING: dummy consumer key
      expect "Consumer key was not recognised" error from xero
      fix by copying consumer key from xero app page to ./secretprivapp/consumerkey

### XERO ERROR 401: Consumer key was not recognised ###

If the consumer key doesn't match one at Xero, you need to either copy it from Xero to secretprivapp/consumerkey, or create a new app at Xero.

    XERO ERROR 401: Consumer key was not recognised

Fix: copy the consumer key from the Xero page to secretprivapp/consumerkey.

### WARNING: ./secretprivapp/NAGABOUTKEY exists ###

When the X.509 private key and certificate are made (by createprivapp.sh), it also makes a file `NAGABOUTKEY`, which will nag you about uploading the public key certificate to the Xero app page.

If you don't upload the key, you will also see "Failed to validate signature" errors.  As this error could be caused by many things, we make a NAGABOUTKEY file.

If you definitely uplodoaded the public key but forgot to remove the NAGABOUTKEY file, it will be removed automatically after the first successfull Xero API call, as that won't happen unless the key was uploaded correctly.

    WARNING: ./secretprivapp/NAGABOUTKEY exists
      expect "Failed to validate signature" from xero
      fix by copying ./secretprivapp/publickey.cer to xero app page
      delete ./secretprivapp/NAGABOUTKEY to remove this warning

### XERO ERROR 401: Failed to validate signature ###

If they program can't create the correct signatures, the Xero site will say that the signature isn't validated.  There could be many causes of this, but the most common is that the private key used in the program's requests doesn't match the consumer key and uploaded public key at Xero.

    XERO ERROR 401: Failed to validate signature

Look at the output of xero-privapp-helloworld.php

    connecting to xero private app with consumer key 3A...XI
    public key certificate fingerprint e2b0059cdf0d45fd1bcd730aa83b8d620efbe37b

Fix: check these against the values shown on the Xero page.  If necessary repeat the steps at "Installing Hello World Private App", above.

## Troubleshooting the Public App ##

### ERROR: ./secretpubapp/consumerkey is missing or empty ###

There is no consumer key file or it's empty.  Probably because you didn't run createpubapp.sh

    ERROR: ./secretpubapp/consumerkey is missing or empty
      did you run "sh createpubapp.sh" ?

Fix: run the creation script

    sh createpubapp.sh

### WARNING: dummy consumer key ###

When the createpubapp.sh script creates its files, it puts a dummy value for the consumer key.

    WARNING: dummy consumer key
      expect "Unknown Consumer" error from xero
      fix by copying consumer key from xero app page to ./secretpubapp/consumerkey

Fix: copy the consumer key from the Xero app page to the file secretpubapp/consumerkey

### XERO ERROR 401 (during REQUESTTOKEN): Unknown Consumer ###

If Xero doesn't recognise the consumer key it doesn't know what app you want.

    XERO ERROR 401 (during REQUESTTOKEN): Unknown Consumer (Realm: , Key: DUMMYPUBLICAPPCONSUMERKEY00000)
    Problem: consumer_key_unknown
      Unknown Consumer (Realm: , Key: DUMMYPUBLICAPPCONSUMERKEY00000)

Fix: propertly copy the consumer key from Xero app into secretpubapp/consumerkey

### WARNING: dummy consumer secret ###

When the createpubapp.sh script creates its files, it puts a dummy value for the consumer secret.  If you don't have the correct consumer secret, it will cause an invalid signature error from Xero.

    WARNING: dummy consumer secret
      expect "Consumer "Failed to validate signature" error from xero
      fix by copying consumer secret from xero app page to ./secretpubapp/consumerkey
      
Fix: copy the consumer secret from Xero app page into secretpubapp/consumersecret

### XERO ERROR 401 (during REQUESTTOKEN): Failed to validate signature ###

This is a general encryption failure, which just means that the program didn't use the correct credentials to encrypt its request.  It can be cause by any number of things, but basically it means the consumer secret in the program doesn't match that from the Xero app page.  The most common reason is that they were not copied from Xero to the file; or copied incorrectly.

    XERO ERROR 401 (during REQUESTTOKEN): Failed to validate signature
    Problem: signature_invalid
      Failed to validate signature

Fix: properly copy the consumer secret from the Xero app page to the file secretpubapp/consumersecret

### XERO ERROR 401 (during ACCESSTOKEN): The consumer was denied access to this resource. ###

During the authentication the user is shown a Xero page to grant access to the organisation's accounts, and if granted gives the user a validation code.  If the validation code is no good becuase it's too old, has been used before, belongs to a different request, it was mistyped, or it was just guessed, Xero will deny access.

   XERO ERROR 401 (during ACCESSTOKEN): The consumer was denied access to this resource.
   Problem: permission_denied
     The consumer was denied access to this resource.
  
Fix: try program again and grant access to a company, carefully copying and pasting the validation code into the program

### XERO ERROR 400 (during REQUESTTOKEN): Bad Request ###

If the program supplies a callback URL to the authorisation request, that URL has to be in the domain which is registered in the Xero app page.

XERO ERROR 400 (during REQUESTTOKEN): Bad Request
Problem: parameter_rejected
  Callback url is not in the registered callback domain



(end)
