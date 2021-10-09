dev:
dev: install serve

test:
test: install test

install:
	composer install

serve:
	symfony serve

test:
	php ./vendor/bin/phpunit
