.PHONY: lint tests \
		install-dependencies update-dependencies \
		start-db stop-db \
		start-db-tests stop-db-tests \
		start-redis stop-redis

default: start-db start-redis start-db-tests lint tests

lint:
	vendor/bin/php-cs-fixer fix --config .php-cs-fixer.dist.php --dry-run

lint-fix:
	vendor/bin/php-cs-fixer fix --config .php-cs-fixer.dist.php

tests:
	vendor/bin/phpunit --configuration phpunit.xml

install-dependencies:
	composer install

update-dependencies:
	composer update

start-db:
	docker start serieall-mysql || docker run \
		--name serieall-mysql \
		-p 3307:3306 \
		-e MYSQL_DATABASE="serieall" \
		-e MYSQL_ROOT_PASSWORD="serieall" \
		-d mysql:5.7

stop-db:
	docker stop serieall-mysql
	docker rm serieall-mysql

start-db-tests:
	docker start serieall-tests-mysql || docker run  \
		--name serieall-tests-mysql \
		-p 3306:3306 \
		-e MYSQL_DATABASE="serieall-tests" \
		-e MYSQL_ROOT_PASSWORD="serieall" \
		-d mysql:5.7

stop-db-tests:
	docker stop serieall-tests-mysql
	docker rm serieall-tests-mysql

start-redis:
	docker start serieall-redis || docker run \
		--name serieall-redis \
		-p 6379:6379 \
		-d redis

stop-redis:
	docker stop serieall-redis
	docker rm serieall-redis
