GREEN = /bin/echo -e "\x1b[32m\#\# $1\x1b[0m"
RED = /bin/echo -e "\x1b[31m\#\# $1\x1b[0m"

# Fonction pour afficher des messages colorés
define color_echo
    @$(call $(1), $(2))
endef

# ----- Programs -----
COMPOSER = composer
PHP = php
SYMFONY = symfony
SYMFONY_CONSOLE = symfony console

init: ## Initialize project
	$(MAKE) db-create

save:
	$(call color_echo, GREEN, "Lets go to make migration and migrate to the database...")
	$(SYMFONY_CONSOLE) make:migration
	$(SYMFONY_CONSOLE) doctrine:migrations:migrate

load-fixtures:
	$(call color_echo, GREEN, "Fixtures loaded")
	$(SYMFONY_CONSOLE) doctrine:fixtures:load

## -------- database --------
db-create: ## Create database
	$(SYMFONY_CONSOLE) doctrine:database:create

db-drop: ## Drop the database
	$(call color_echo, GREEN, "Dropping database...")
	$(SYMFONY_CONSOLE) doctrine:database:drop --force --if-exists

db-reload: ## Drop and create the database
	$(MAKE) db-drop
	$(MAKE) db-create
reload-migrations:
	rm -R migrations/
	mkdir migrations
symfony:
	$(MAKE) symfony-cli
	$(MAKE) install-composer
	$(SYMFONY) check:requirements

symfony-cli:
	curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | sudo -E bash
	sudo apt install symfony-cli
install-composer:
	apt install composer
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
	php composer-setup.php
	php -r "unlink('composer-setup.php');"
	php composer.phar
	composer update

prod:
	$(COMPOSER) dump-env prod
init-on-server:
	$(MAKE) db-create
	$(MAKE) reload-migrations
	$(MAKE) save


php-version:
	sudo update-alternatives --install /usr/bin/php php /usr/bin/php8.3 1
	sudo update-alternatives --config php
	symfony local:php:list
	sudo a2dismod php8.1
	sudo a2enmod php8.3
	sudo systemctl restart apache2
