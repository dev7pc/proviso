SHELL = /bin/bash
COMPOSER_VERSION = 1.2.1

include maker/project-types/php-composer-library.mk

.DEFAULT_GOAL = build

build: dependencies
	@mkdir -p build
	@php --define=phar.readonly=0 bin/compile

clean-pre::
clean-post::
	@rm -f -r \
		build/ \
		vendor/

composer-validate-pre:: ;
composer-validate-post:: ;

dependencies:
	@php composer.phar install --ansi --optimize-autoloader
