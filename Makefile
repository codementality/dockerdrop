install:
	make clean
	make up
	if [ -f db/database.sql.gz ]; then rm db/database.sql.gz; fi
	if wget --spider https://s3.us-east-2.amazonaws.com/dockerdrop/database.sql.gz 2>/dev/null; then wget https://s3.us-east-2.amazonaws.com/dockerdrop/database.sql.gz --directory-prefix=db && gunzip db/database.sql.gz; fi
	if [ ! -f web/sites/default/settings.php ] && [ -f db/database.sql ]; then cp default/settings.php web/sites/default/settings.php; cp default/services.yml web/sites/default/services.yml; fi
	-docker-compose exec -T php /bin/sh -c "chown -Rf www-data:www-data /var/www/html"
	docker-compose exec -T --user 82 php composer install
	docker-compose exec -T --user 82 php bin/phpcs --config-set installed_paths /var/www/html/vendor/drupal/coder/coder_sniffer
	docker-compose exec -T --user 82 php /bin/sh -c "bin/drupal init"
	docker-compose exec -T --user 82 php /bin/sh -c "cp /var/www/html/default/.bashrc ~/.bashrc"
	docker-compose exec -T --user 82 php /bin/sh -c "mkdir -p ~/.config/fish/completions"
	docker-compose exec -T --user 82 php /bin/sh -c "ln -s ~/.console/drupal.fish ~/.config/fish/completions/drupal.fish"
	make initialize-site
	make update-tests
	make provision

provision:
	make config-import
	docker-compose exec -T --user 82 php bin/drush @dev updb -y
	docker-compose exec -T --user 82 php bin/drush @dev entup -y
	docker-compose exec -T --user 82 php bin/drush @dev wd-del all -y
	-docker-compose ps
	-make uli

clean:
	docker-compose down

clean-data:
	docker volume rm dockerdrop_mysql-data

up:
	docker-compose up -d

uli:
	docker-compose exec -T --user 82 php bin/drush @dev uli

update-tests:
	-docker-compose exec -T --user 82 php bin/behat -c tests/behat.yml --init

test:
	make phpcs
	make behat

phpcs:
	docker-compose exec -T --user 82 php bin/phpcs --standard=Drupal --extensions=php,module,inc,install,test,profile,theme tests/features web/modules/custom --ignore=*.css,*.min.js,*addthis_widget.js,*features.*.inc

behat:
	docker-compose exec -T --user 82 php bin/behat -c tests/behat.yml --tags=~@failing -f progress

composer-update:
	-docker-compose exec --user 82 php composer update

initialize-site:
	if [ ! -f db/database.sql ]; then docker-compose exec -T --user 82 php bin/drush @dev si --account-name=admin --account-pass=admin --db-url='mysql://drupal:drupal@mariadb/drupal' --site-name='DockerDrop, a Docker Training Site' -y; fi

config-export:
	docker-compose exec -T --user 82 php bin/drush @dev config-export --destination=/var/www/html/config/sync -y

config-import:
    if [ "$(ls -A /var/www/html/config/sync)" ]; then docker-compose exec -T --user 82 php bin/drush @dev config-import --source=/var/www/html/config/sync -y; fi

refresh-seed-db:
	docker-compose exec -T --user 82 php bin/drush @dev wd-del all -y
	docker-compose exec -T --user 82 php bin/drush @dev sql-dump --result-file=/var/www/html/db/database.sql --gzip
	aws s3 cp db/database.sql.gz s3://dockerdrop/database.sql.gz --profile lhridley --region us-east-2
	if [ ! -f default/settings.php ]; then cp web/sites/default/settings.php default/settings.php; fi
	if [ ! -f default/services.yml ]; then cp web/sites/default/settings.php default/services.yml; fi
