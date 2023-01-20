ROOT_DIR := ../..
DOCKERFILES_DIR := ./dockerfiles
WORK_DIR := ./dockerfiles/.work

BUILD_APP = echo "[$@] creating..." \
	&& export DOCKER_BUILDKIT=1 \
	&& docker build -q -t zinger/$@:latest -f $(DOCKERFILES_DIR)/app/Dockerfile --target $@ $(DOCKERFILES_DIR) > /dev/null \
	&& echo "[$@] done."

BUILD_WEB = echo "[$@] creating..." \
	&& export DOCKER_BUILDKIT=1 \
	&& docker build -q -t zinger/$@:latest -f $(DOCKERFILES_DIR)/web/Dockerfile --target $@ $(DOCKERFILES_DIR) > /dev/null \
	&& echo "[$@] done."

.PHONY: all
all: dev sandbox staging prod;

.PHONY: dev
dev: app-cli-dev app-server-dev web-dev;

.PHONY: sandbox
sandbox: app-cli-sandbox app-server-sandbox web-sandbox;

.PHONY: staging
staging: app-cli-staging app-server-staging web-staging;

.PHONY: prod
prod: app-cli-prod app-server-prod web-prod;

.PHONY: clean
clean:
	@echo "[clean] cleaning..."
	@rm -rf $(DOCKERFILES_DIR)/web/assets/*/nginx/conf.d/*.conf
	@echo "[clean] done."

.PHONY: app-context
app-context:
	@echo "[app-context] creating..."
	@rm -rf $(WORK_DIR)/app/server
	@cp -pR $(ROOT_DIR)/artisan $(WORK_DIR)/app
	@cp -pR $(ROOT_DIR)/composer.* $(WORK_DIR)/app
	@cp -pR $(ROOT_DIR)/server $(WORK_DIR)/app
	@echo "[app-context] done."

.PHONY: app-base
app-base:
	@$(BUILD_APP)

.PHONY: app-cli-base app-server-base
app-cli-base app-server-base: app-base
	@$(BUILD_APP)

.PHONY: app-cli-dev
app-cli-dev: app-cli-base
	@$(BUILD_APP)

.PHONY: app-cli-sandbox app-cli-staging app-cli-prod
app-cli-sandbox app-cli-staging app-cli-prod: app-cli-base app-context
	@$(BUILD_APP)

.PHONY: app-server-dev
app-server-dev: app-server-base
	@$(BUILD_APP)

.PHONY: app-server-sandbox app-server-staging app-server-prod
app-server-sandbox app-server-staging app-server-prod: app-server-base app-context
	@$(BUILD_APP)

.PHONY: web-context
web-context:
	@echo "[web-context] creating..."
	@rm -rf $(WORK_DIR)/web/public
	@cp -pR $(ROOT_DIR)/packages/frontend/public $(WORK_DIR)/web
	@echo "[web-context] done."

.PHONY: web-base
web-base:
	@$(BUILD_WEB)

.PHONY: web-dev
web-dev: web-base
	@$(BUILD_WEB)

.PHONY: web-sandbox web-staging web-prod
web-sandbox web-staging web-prod: web-base web-context
	@$(BUILD_WEB)
