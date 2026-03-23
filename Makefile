DOCKER_APP = docker-compose exec --index=1 frontend

.PHONY: help up down install cache-clear bash

help:
	@echo "Comandes disponibles:"
	@echo "  make up          - Aixeca tots els contenidors"
	@echo "  make down        - Atura i elimina els contenidors"
	@echo "  make install     - Executa 'composer install' dins d'una instància"
	@echo "  make cache-clear - Neteja la memòria cau de Symfony"
	@echo "  make bash        - Entra dins del primer contenidor de Symfony per executar coses manualment"

up:
	docker-compose up -d

down:
	docker-compose down

install:
	$(DOCKER_APP) git config --global --add safe.directory /var/www/html
	$(DOCKER_APP) composer install

cache-clear:
	$(DOCKER_APP) php bin/console cache:clear
	@echo "Memòria cau completament neta!"

bash:
	$(DOCKER_APP) bash
