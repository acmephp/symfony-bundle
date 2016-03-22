Acme PHP Symfony Bundle
=======================

> Note this bundle is in **active development (it is not ready for the moment)**.

The ACME protocol is a protocol defined by the Let's Encrypt Certificate Authority.
You can see the complete specification on https://letsencrypt.github.io/acme-spec/.

The ACME PHP project aims to implement the ACME protocol in PHP to be able to use it
easily in various projects.

This repository is the Symfony bundle based on the [PHP library](https://github.com/acmephp/core).

Installation
------------

### Step 1: Download AcmePhpBundle using composer

Require the `acmephp/symfony-bundle` with composer [Composer](http://getcomposer.org/).

```bash
$ composer require acmephp/symfony-bundle
```

### Step 2: Enable the bundle

Enable the bundle in the kernel:

```php
<?php

// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new AcmePhp\Bundle\AcmePhpBundle(),
        // ...
    );
}
```

### Step 3: Configure the AcmePhpBundle

Below is a minimal example of the configuration necessary to use the
`AcmePhpBundle` in your application:

```yml
# app/config/config.yml

acme_php:
    contact_email: contact@mycompany.com
    default_distinguished_name:
        country: FR
        state: France
        locality: Paris
        organization_name: MyCompany
        organization_unit_name: IT
    domains:
        myapp.com: ~
```

### Step 4:  Import AcmePhpBundle routing files

Now that you have activated and configured the bundle, all that is left to do
is import the AcmePhpBundle routing files.

```yml
# app/config/routing.yml

acme_php:
    resource: "@AcmePhpBundle/Resources/config/routing.xml"
```

Configuration reference
-----------------------

```yml
# app/config/config.yml

acme_php:
    # Certificates locations. Default: `~/.acmephp`
    # Beware to use a directory readable by the web server
    # It should be writable too, to store certificates, keys and challenges.
#    certificate_dir: ~/.acmephp
    
    # Certificate Authority used to deliver certificates. Default: `letsencrypt`. Available values : `letsencrypt`
    # You can use your own Certificate Authority by :
    #  - implementing the CertificateAuthorityConfigurationInterface interface
    #  - registering the service with the tag "acme_php.certificate_authority" and with an alias to use here
#    certificate_authority: letsencrypt

    # Email addresse associated to the account used to generate certificate
    contact_email: contact@mycompany.com
    
    # Default Distinguished Name (or a DN) informations used to request certificates.
    # Thoses values are the default one, but can be overrided for each registered domain
    default_distinguished_name: # https://scotthelme.co.uk/setting-up-le/
        # Country Name (2 letter code)
        country: FR
        
        # State or Province Name (full name)
        state: France
        
        # Locality Name (eg, city)
        locality: Paris
        
        # Organization Name (eg, company)
        organization_name: MyCompany
        
        # Organizational Unit Name (eg, section)
        organization_unit_name: IT
        
        # Email Address. When missing, the adresse defined in the parameter contact_email will be used
#        email_address: john.doe@mycompany.com
    
    # List of domains to request
    domains:
    
        myapp.com: ~
    
        www.myapp.com: ~
    
        invoice.myapp.com:
            organization_unit_name: Derusse billing
            email_address: sales@mycompany.com

```

Usage
-----

You can both, request and renew a certificate with the single commande

```
$ bin/console acmephp:generate
```

The certificates will be stored in the folder defined by the parameter `certificate_dir`. 

```
$ tree ~/.acmephp
├── account                   # Your account's keys
│   ├── private.pem
│   └── public.pem
├── challenges                # Pending challenges
├── domains                   # Contains all domain's certificate (1 sub directory per domain)
│   ├── company.com           # Contains the certificates for domain `company.com`
│   │   ├── cert.pem          # Server certificate only
│   │   ├── chain.pem         # All certificates that need to be served by the browser **excluding** server certificate
│   │   ├── combined.pem      # All certificates plus private key
│   │   ├── fullchain.pem     # All certificates, **including** server certificate
│   │   ├── private.pem       # Private key for the certificate
│   │   └── public.pem        # Public key for the certificate
│   └── www.company.com
│       └── ...
└── domains-backup            # Previous versions of the certificates
    ├── company.com           # domains as subdirectory
    │   └── 20160314-144416   # each subdirectory is a backup
    │       └── ...
    └── www.company.com
        └── ...

```


Monitoring
----------

If your application use monolog (which should be the case by default), Acme PHP Symfony Bundle will log in a `acme_php`
channel. By this way, you can handle logs and being notified on renewal failure.

Here is a sample of configuration


```yml
# app/config/config.yml

monolog:
    handlers:
        certificate_slack:
            type: slack
            token: # Your slack's token : https://api.slack.com/web
            channel: "#production" # name of the slack's channel
            bot_name: CertificatesBot
            icon_emoji: lock_with_ink_pen
            level: NOTICE
            channels: [acme_php]
```

Extensions
----------

**Certificate Authority**: You can add your own certificate authority by implementing the interface 
`AcmePhp\Bundle\Acme\CertificateAuthority\Configuration\CertificateAuthorityConfigurationInterface` and adding the 
service with tag `acme_php.certificate_authority` as follow :

```yml
# app/config/services.yml

services:
    app.custom_certificate_authority:
        class: AppBundle\Acme\CustomConfiguration
        public: false
        tags:
            - name: acme_php.certificate_authority
              alias: custom
```

You just have to reference it in your configuration
```yml
# app/config/config.yml

acme_php:
    certificate_authority: custom
```

**Domain Loader**: By default, the bundle loads domain's configurations through the config's file. But, you can add
your own loader by implementing the interface `AcmePhp\Bundle\Acme\Domain\Loader\LoaderInterface` and adding the service 
with tag `acme_php.domains_configurations_loader` as follow:  

```yml
# app/config/services.yml

services:
    app.custom_certificate_authority:
        class: AppBundle\Acme\CustomLoader
        public: false
        tags:
            - name: acme_php.domains_configurations_loader
```

**Certificate Formatter**: When a new certificate is requested, it is dumped in several formats (cert.pem, 
combined.pem, ...). You can add your own formatter by implementing the interface
`AcmePhp\Bundle\Acme\Certificate\Formatter\FormatterInterface` and adding the service with tag 
`acme_php.certificate_formatter` as follow:  

```yml
# app/config/services.yml

services:
    app.custom_certificate_authority:
        class: AppBundle\Acme\CustomFormatter
        public: false
        tags:
            - name: acme_php.certificate_formatter
```

**Events**: AcmePhpBundle triggers many events:

- CERTIFICATE_REQUESTED: When a certificate is requested on renewed
- CHALLENGE_REQUESTED: When a new challenge is requested
- CHALLENGE_CHECKED: When the challenge is checked (whether or not it have been accepted)
- CHALLENGE_ACCEPTED: When the challenge have been accepted
- CHALLENGE_REJECTED: When the challenge have been rejected


Contributing
------------

**Unit tests**

```
composer install

./bin/phpunit
```

**Functionnal testing**

```
composer install

docker run -d --net host jderusse/testing-ca
docker run --rm --net host martin/wait -c localhost:4000 -t 120
features/fixtures/TestApp/console acmephp:server:start

./bin/behat

features/fixtures/TestApp/console acmephp:server:stop
```

**Manual testing**

Because Letsencrypt has a rate limiting, We recommends to use
[Boulder](https://github.com/letsencrypt/boulder) as Certificate Authority.
Which is include and fully package in the docker image `jderusse/testing-ca`

You'll find a micro symfony application in the folder
`features/fixtures/TestApp`.

It allow you to easily test the application. You just have to edit the config
file in `features/fixtures/TestApp/config/config.yml`. Then start Boulder and
the symfony's server (to handle challenge requests).

```
composer install

# Launch boulder
docker run -d --net host jderusse/testing-ca
docker run --rm --net host martin/wait -c localhost:4000 -t 120

# Launche the application to listen to challenge checks
features/fixtures/TestApp/console acmephp:server:start

# Generate the certificate
features/fixtures/TestApp/console acmephp:generate

ls -al features/fixtures/TestApp/certs/domains/*/
```
