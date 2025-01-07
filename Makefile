ifeq ($(OS),Windows_NT)
    PWD := $(shell cd)
else
    PWD := $(shell pwd -L)
endif

ARCH := $(shell uname -m)
PLATFORM :=

ifeq ($(ARCH),arm64)
    PLATFORM := --platform=linux/amd64
endif

DOCKER_RUN = docker run ${PLATFORM} --rm -it --net=host -v ${PWD}:/app -w /app gustavofreze/php:8.3

.PHONY: configure test test-file test-no-coverage review show-reports clean

configure:
	@${DOCKER_RUN} composer update --optimize-autoloader

test:
	@${DOCKER_RUN} composer tests

test-file:
	@${DOCKER_RUN} composer test-file ${FILE}

test-no-coverage:
	@${DOCKER_RUN} composer tests-no-coverage

review:
	@${DOCKER_RUN} composer review

show-reports:
	@sensible-browser report/coverage/coverage-html/index.html report/coverage/mutation-report.html

clean:
	@sudo chown -R ${USER}:${USER} ${PWD}
	@rm -rf report vendor .phpunit.cache *.lock
