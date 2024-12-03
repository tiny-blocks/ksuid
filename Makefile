DOCKER_RUN = docker run --rm -it --net=host -v ${PWD}:/app -w /app gustavofreze/php:8.3

.PHONY: configure test test-file test-no-coverage review show-reports clean

configure:
	@${DOCKER_RUN} composer update --optimize-autoloader

test:
	@${DOCKER_RUN} composer tests

test-file:
	@${DOCKER_RUN} composer tests-file-no-coverage ${FILE}

test-no-coverage:
	@${DOCKER_RUN} composer tests-no-coverage

review:
	@${DOCKER_RUN} composer review

show-reports:
	@sensible-browser report/coverage/coverage-html/index.html report/coverage/mutation-report.html

clean:
	@sudo chown -R ${USER}:${USER} ${PWD}
	@rm -rf report vendor .phpunit.cache
