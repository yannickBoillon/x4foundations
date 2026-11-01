export UID := $(shell id -u)
export GID := $(shell id -g)

DC      = docker compose
PHP     = $(DC) exec php
CONSOLE = $(PHP) php bin/console

.DEFAULT_GOAL := help

help: ## Affiche cette aide
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

build: ## Construit les images
	$(DC) build

up: ## Démarre la stack (crée le projet Symfony au premier lancement)
	$(DC) up -d --build

down: ## Arrête la stack
	$(DC) down

logs: ## Suit les logs (ex: make logs s=php)
	$(DC) logs -f $(s)

sh: ## Shell dans le container PHP
	$(PHP) sh

console: ## Console Symfony (ex: make console c="debug:router")
	$(CONSOLE) $(c)

composer: ## Composer (ex: make composer c="require foo/bar")
	$(PHP) composer $(c)

npm: ## npm dans le container frontend (ex: make npm c="install axios")
	$(DC) exec frontend npm $(c)

migration: ## Génère une migration Doctrine
	$(CONSOLE) make:migration

migrate: ## Exécute les migrations
	$(CONSOLE) doctrine:migrations:migrate --no-interaction

health: ## Vérifie PostgreSQL, Elasticsearch et RabbitMQ
	@curl -s http://localhost:$${HTTP_PORT:-8080}/health; echo

.PHONY: help build up down logs sh console composer npm migration migrate health
