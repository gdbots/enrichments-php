enrichments-php
=============

[![Build Status](https://api.travis-ci.org/gdbots/enrichments-php.svg)](https://travis-ci.org/gdbots/enrichments-php)
[![Code Climate](https://codeclimate.com/github/gdbots/enrichments-php/badges/gpa.svg)](https://codeclimate.com/github/gdbots/enrichments-php)
[![Test Coverage](https://codeclimate.com/github/gdbots/enrichments-php/badges/coverage.svg)](https://codeclimate.com/github/gdbots/enrichments-php/coverage)

Php library that provides implementations for __gdbots:enrichments__ schemas.   Using this library assumes
that you've already created and compiled your own pbj classes using the [Pbjc](https://github.com/gdbots/pbjc-php)
and are making use of the __"gdbots:enrichments:mixin:*"__ mixins from [gdbots/schemas](https://github.com/gdbots/schemas).


## Symfony Integration
Enabling these enrichments in a Symfony app is done by importing classes and letting Symfony
autoconfigure and autowire them.

__config/packages/enrichments.yml:__

```yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true
    public: false

  Gdbots\Enrichments\:
    resource: '%kernel.project_dir%/vendor/gdbots/enrichments/src/*'
    tags:
      - {name: monolog.logger, channel: enrichments}
    bind:
      Psr\Log\LoggerInterface: '@monolog.logger.enrichments'

```


## TODO
* Create the ip-to-geo enricher with pluggable providers (ip2location, maxmind)
