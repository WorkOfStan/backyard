#!/bin/bash

# initialize the vendor folder, if needed
composer install -a --prefer-dist --no-progress

composer require --dev phpstan/phpstan-webmozart-assert --prefer-dist --no-progress --with-all-dependencies
# when "phpunit/phpunit": "<7.0"
#composer require --dev phpstan/phpstan-phpunit --prefer-dist --no-progress

vendor/bin/phpunit
#vendor/bin/phpstan.phar --configuration=conf/phpstan.webmozart-assert.neon analyse . --memory-limit 300M --pro
vendor/bin/phpstan.phar --configuration=phpstan.neon.dist analyse . --memory-limit 300M --pro
