Acme PHP Symfony Bundle
=======================

[![Build Status](https://travis-ci.org/acmephp/cli.svg?branch=master)](https://travis-ci.org/acmephp/cli)
[![StyleCI](https://styleci.io/repos/51296588/shield)](https://styleci.io/repos/51296588)

> Note this bundle is in active development (it is not ready for the moment).

The ACME protocol is a protocol defined by the Let's Encrypt Certificate Authority.
You can see the complete specification on https://letsencrypt.github.io/acme-spec/.

The ACME PHP project aims to implement the ACME protocol in PHP to be able to use it
easily in various projects.

This repository is the Symfony bundle based on the [PHP library](https://github.com/acmephp/core).

Usage
-----

Simply configure the bundle to your needs:

``` yml
# app/config/config.yml

acmephp:

    # Recovery email used by Let's Encrypt for registration and recovery contact
    recovery_email:       ~ # Required

    # The monolog channel to use for commands output. Useful to be notified on certificate renewal in CRON
    # (see the documentation for more details about how to configure, for instance, a slack notification).
    log_channel:           null

    # Domains to get certificates for (this application should response to these domains)
    domains:              [] # Required
```
