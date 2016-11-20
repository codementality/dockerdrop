# Dockerdrop Project

## Build Status
* Master: [![Build Status](https://travis-ci.org/codementality/dockerdrop.svg?branch=master)](https://travis-ci.org/codementality/dockerdrop)
* Develop: [![Build Status](https://travis-ci.org/codementality/dockerdrop.svg?branch=develop)](https://travis-ci.org/codementality/dockerdrop)

## To Initialize this project

* Clone this repository with https://github.com/codementality/dockerdrop.git
* Execute `git remote remove origin`
* Create your repository for this project on Github
* Execute `git remote add origin <your repository github link>`
* Execute `git push -f origin develop`
* Configure your github repository with a Travis-CI Service integration
* Configure your Travis-CI account to build on:
> * pushes
> * pull requests
> * only if .travis.yml is present

## Make file targets
* `install` -- initially installs the project, or rebuilds from scratch
* `provision` -- provisions a site with changes to configuration, hook updates, or entity additions / updates
* `clean` -- shuts down and removes project service containers
* `clean-data` -- removes the database volume
* `up` -- reinitializes project service containers
* `update-tests` -- initializes or updates behat for changes in behat.yml
* `test` -- run coding standards check and behat tests
* `phpcs` -- run coding standards check
* `behat` -- run behat tests
* `composer-updates` -- updates composer.lock
* `initialize-site` -- installs drupal with `drush si`
* `config-export` -- exports configuration management database changes to files
* `config-import` -- imports configuration management files to database
* `refresh-seed-db` -- exports the seed database and uploads it to AWS S3 (example only)
