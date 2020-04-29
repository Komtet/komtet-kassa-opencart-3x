SHELL:=/bin/bash

VERSION=$(shell grep -o '^[0-9]\+\.[0-9]\+\.[0-9]\+' CHANGES.txt | head -n1)


help:
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[0;36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST) | sort

version:  ## Версия проекта
	@echo -e "${BCyan}Version:${Color_Off} $(VERSION)";

build:  ## Собрать контейнер
	@docker-compose build

stop: ## Остановить все контейнеры
	@docker-compose down

start_web5: stop  ## Запустить контейнер
	@docker-compose up -d web5

start_web7: stop  ## Запустить контейнер
	@docker-compose up -d web7

update:  #Обновить модуль
	 @cp -rf upload/admin/ php/ && \
	  cp -rf upload/system/ php/ && \
	  cp -rf upload/catalog/ php/

release:  ## Архивировать для загрузки в маркет
	@tar\
	 --exclude='upload/system/library/komtet-kassa-sdk/*' \
	 -czvf komtet-kassa-$(VERSION).tar.gz upload/


.PHONY: help
.DEFAULT_GOAL := help

