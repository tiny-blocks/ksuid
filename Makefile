PWD := $(CURDIR)
ARCH := $(shell uname -m)
PLATFORM :=

ifeq ($(ARCH),arm64)
    PLATFORM := --platform=linux/amd64
endif

DOCKER_RUN = docker run ${PLATFORM} --rm -it --net=host -v ${PWD}:/app -w /app gustavofreze/php:8.5-alpine

RESET := \033[0m
GREEN := \033[0;32m
YELLOW := \033[0;33m

.DEFAULT_GOAL := help

.PHONY: configure
configure: ## Configure development environment
	@${DOCKER_RUN} composer update --optimize-autoloader

.PHONY: test
test: ## Run all tests with coverage
	@${DOCKER_RUN} composer tests

.PHONY: test-file
test-file: ## Run tests for a specific file (usage: make test-file FILE=path/to/file)
	@${DOCKER_RUN} composer test-file ${FILE}

.PHONY: test-no-coverage
test-no-coverage: ## Run all tests without coverage
	@${DOCKER_RUN} composer tests-no-coverage

.PHONY: review
review: ## Run static code analysis
	@${DOCKER_RUN} composer review

.PHONY: show-reports
show-reports: ## Open static analysis reports (e.g., coverage, lints) in the browser
	@sensible-browser report/coverage/coverage-html/index.html report/coverage/mutation-report.html

.PHONY: clean
clean: ## Remove dependencies and generated artifacts
	@sudo chown -R ${USER}:${USER} ${PWD}
	@rm -rf report vendor .phpunit.cache *.lock

.PHONY: help
help:  ## Display this help message
	@echo "Usage: make [target]"
	@echo ""
	@echo "$$(printf '$(GREEN)')Setup$$(printf '$(RESET)')"
	@grep -E '^(configure):.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*? ## "}; {printf "$(YELLOW)%-25s$(RESET) %s\n", $$1, $$2}'
	@echo ""
	@echo "$$(printf '$(GREEN)')Testing$$(printf '$(RESET)')"
	@grep -E '^(test|test-file|test-no-coverage):.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "$(YELLOW)%-25s$(RESET) %s\n", $$1, $$2}'
	@echo ""
	@echo "$$(printf '$(GREEN)')Quality$$(printf '$(RESET)')"
	@grep -E '^(review):.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "$(YELLOW)%-25s$(RESET) %s\n", $$1, $$2}'
	@echo ""
	@echo "$$(printf '$(GREEN)')Reports$$(printf '$(RESET)')"
	@grep -E '^(show-reports):.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "$(YELLOW)%-25s$(RESET) %s\n", $$1, $$2}'
	@echo ""
	@echo "$$(printf '$(GREEN)')Cleanup$$(printf '$(RESET)')"
	@grep -E '^(clean):.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "$(YELLOW)%-25s$(RESET) %s\n", $$1, $$2}'
