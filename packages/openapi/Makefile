SPREADSHEET_URL := https://script.google.com/macros/s/AKfycbw0T3o0Guz_jnosliLwdq3feg2bSEMBw9EVICtyRE7w6OFUnLMvQ0wY/exec

.PHONY: all
all: html

clean:
	@rm -rf \
		dist/openapi.yaml \
		dist/schemas.json \
		;

schemas.json:
	curl -fsSL "$(SPREADSHEET_URL)" > schemas.json

.PHONY: paths
paths:
	yarn ts-node lib/update-paths-index.ts

.PHONY: parameters
parameters: schemas
	yarn ts-node lib/update-parameters.ts

.PHONY: schemas
schemas: schemas.json
	yarn ts-node lib/update-schemas.ts

.PHONY: yaml
yaml: paths parameters schemas
	@rm -rf dist/openapi.yaml
	yarn swagger-merger -i src/openapi.yaml -o dist/openapi.yaml

.PHONY: html
html: yaml
	yarn redoc-cli bundle dist/openapi.yaml --output dist/index.html
