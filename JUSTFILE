PHPUNIT := 'vendor/bin/phpunit -d xdebug.max_nesting_level=250 -d memory_limit=1024M'

# Set up project installation
@setup:
	composer install  --prefer-dist --optimize-autoloader --no-suggest

# Run unit and integration tests
@test:
	echo "Running unit and integration tests"; \
	{{PHPUNIT}};

# Run tests and create code-coverage report
@coverage:
	echo "Running unit and integration tests"; \
	echo "Once completed, the generated code coverage report can be found under ./build)"; \
	sudo phpenmod -s cli xdebug;
	php {{PHPUNIT}}; \
	sudo phpdismod -s cli xdebug;

# Lint files
@lint:
	vendor/bin/php-cs-fixer fix
