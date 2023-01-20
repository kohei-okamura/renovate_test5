PROFILE = zinger
HOST = $(shell yarn --silent aws:get-ecr-host -p $(PROFILE) | tail -n 3 | jq -r .host)
TAG = $(shell date '+%Y%m%d%H%M%S')-$(shell git rev-parse --short main)

.PHONY: dev
dev:
	yarn docker:build:dev
	yarn start:docker

.PHONY: prod
prod:
	yarn docker:build:prod
	yarn aws:push-docker-images:prod $(TAG)
	yarn aws:ecs:register -p zinger --tag $(TAG)

.PHONY: sandbox
sandbox:
	yarn docker:build:sandbox
	yarn aws:push-docker-images:sandbox $(TAG)
	yarn aws:ecs:register -p zinger-sandbox --tag $(TAG)

.PHONY: staging
staging:
	yarn docker:build:staging
	yarn aws:push-docker-images:staging $(TAG)
	yarn aws:ecs:register -p zinger-staging --tag $(TAG)
