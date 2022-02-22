PHPUNIT := 'vendor/bin/phpunit -d xdebug.max_nesting_level=250 -d memory_limit=1024M'

# Set up project installation
@setup:
	composer install  --prefer-dist --optimize-autoloader --no-suggest

# Lint files
@lint:
	vendor/bin/php-cs-fixer fix
