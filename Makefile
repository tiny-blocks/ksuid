DOCKER_RUN = docker run --rm -it --net=host -v ${PWD}:/app -w /app gustavofreze/php:8.2.6

.PHONY: configure test test-no-coverage review show-reports clean

configure:
	@${DOCKER_RUN} composer update --optimize-autoloader

test: review
	@${DOCKER_RUN} composer tests

test-no-coverage: review
	@${DOCKER_RUN} composer tests-no-coverage

review:
	@${DOCKER_RUN} composer review

show-reports:
	@sensible-browser report/coverage/coverage-html/index.html report/coverage/mutation-report.html

clean:
	@sudo chown -R ${USER}:${USER} ${PWD}
	@rm -rf report vendor
