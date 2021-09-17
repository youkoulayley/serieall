.PHONY: install-dependencies update-dependencies \
		start-db stop-db \
		start-redis stop-redis

default: start-db start-redis

lint:
	vendor/bin/php-cs-fixer fix .

install-dependencies:
	php7 -f /usr/bin/composer install

update-dependencies:
	php7 -f /usr/bin/composer update

start-db:
	docker run \
		--name serieall-mysql \
		-p 3306:3306 \
		-v /var/lib/mysql/serieall-dev:/var/lib/mysql \
		-e MYSQL_DATABASE="serieall" \
		-e MYSQL_ROOT_PASSWORD="serieall" \
		-d mysql:5.7

stop-db:
	docker stop serieall-mysql
	docker rm serieall-mysql

start-redis:
	docker run \
		--name serieall-redis \
		-p 6379:6379 \
		-d redis

stop-redis:
	docker stop serieall-redis
	docker rm serieall-redis