composer.phar:
	@curl -sS https://getcomposer.org/installer | php

vendor: composer.phar
	@php composer.phar install

server.pid:
	@echo "Start Server on localhost:1337"
	@{ php -S localhost:1337 -t ./tests/ > /dev/null 2> server.log & echo $$! > server.pid; }

test: vendor server.pid
	@phpunit --coverage-text --coverage-html build/coverage --coverage-clover build/logs/clover.xml
	@kill `cat server.pid` && rm server.pid

check: vendor
	@./vendor/bin/phpcs --standard=./vendor/leaphub/phpcs-symfony2-standard/leaphub/phpcs/Symfony2/ ./src

phpmd: vendor
	@./vendor/bin/phpmd ./src/ text unusedcode

travis: test check
