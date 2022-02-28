#!/usr/bin/make
SHELL = /bin/sh

.DEFAULT_GOAL := help
T_START = \033[0;32m
T_END = \033[0m

help:
	@echo "${T_START}GOIP SMS Server:${T_END}"
	@echo '  make build   - Build'
	@echo '  make up      - Start SMS Server'
	@echo '  make down    - Stop SMS Server'
	@echo '  make restart - Restart SMS Server'
	@echo ''

build:
	docker-compose pull
	docker-compose build

up:
	docker-compose up -d

down:
	docker-compose down

restart:
	docker-compose down
	docker-compose up -d
