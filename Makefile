.PHONY: sh clean build up down restart logs

# Container names
PHP_CONTAINER = home_bridge_php
NGINX_CONTAINER = home_bridge_nginx

# Enter the PHP container shell
sh:
	docker exec -it $(PHP_CONTAINER) bash

sh-nginx:
	docker exec -it $(NGINX_CONTAINER) bash

make build:
	docker compose build

# Start the containers in detached mode
up:
	docker compose up -d

# Stop the containers
down:
	docker compose down

# Restart the containers
restart:
	docker compose restart

# Laravel specific commands
laravel-install:
	docker exec -it $(PHP_CONTAINER) composer create-project laravel/laravel . --no-dev

laravel-install-dev:
	docker exec -it $(PHP_CONTAINER) composer create-project laravel/laravel .

# Run Laravel artisan commands
artisan:
	docker exec -it $(PHP_CONTAINER) php /app/app/artisan $(CMD)

# Install Laravel installer globally
install-laravel-installer:
	docker exec -it $(PHP_CONTAINER) composer global require laravel/installer

# Show Laravel application logs
laravel-logs:
	docker exec -it $(PHP_CONTAINER) tail -f storage/logs/laravel.log
