README
======

[![Build Status](https://travis-ci.org/blablacar/worker-lib.png)](https://travis-ci.org/blablacar/worker-lib)


Description
-----------

Simple library to consume your AMQP messages.

Installation
------------

Add the library to your application's `composer.json` file:

```json
{
    "require": {
        "blablacar/worker-lib": "1.0.*"
    }
}
```

Launch tests
------------

Once you have a working RabbitMQ running on local and PHPUnit installed, you
can simply launch

    ./tests.sh

Tests will delete you "/" vhost.
