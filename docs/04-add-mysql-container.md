# DockerDrop, a training site for Docker for Development with Drupal

## Lesson 4:  Add a MySQL Container and a data volume
 
### 1: Add a MySQL Container to the stack

Open `docker-compose.yml` in your favorite editor and insert the following under the `services` tag:

~~~
  db:
    image: mariadb:10.1.21
~~~

What we've done is add a MariaDB container to our application stack, and pinned it to version 10.1.19.

### 2. Add database environment variables

Now, if we look at the documentation for the official MariaDB container on Docker Hub, we'll see that there are a number of "environment variables" that can be passed to our container when we start it up.  These environment variables can be added to our `docker-compose.yml` file as part of our `db` service.

Below the above image declaration, add the following:

~~~
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: drupal
      MYSQL_USER: drupal
      MYSQL_PASSWORD: drupal
~~~

This sets the root user password for our MySQL installation to `root`, and sets the username, password and databae name for our application database to `drupal`.

### 3. Add MySQL startup command line options
 
We can also add "command line" options when we start our MySQL container and create our database that consist of any options you would normally configure for your MySQL database instance.  In our case, we're going to set the "character set" an "collation" parameters for our database.

Add the following below the `environment` tag:

~~~
    command: --character-set-server=utf8mb4 --collation-server=utf8mb4_unicode_ci # The simple way to override the mariadb config.
~~~

### 4.  Add a volume declaration to the MySQL container

Just as we did with our PHP container, we're going to add some data volumes to our MySQL container.

The first volume we're going to add will be a local data volume to store our MySQL database.  Normally when you destroy a container with `docker-compose down`, the container and all of its contents are destroyed.  This is probably not the desired behavior if you're working on a content management system; we want our data to persist even if we spin our application stack down.

We can do this by declaring a data volume that we mount in the physical location where MySQL normally creates its MySQL databases, which in this case is `/var/lib/mysql`.  Add the following right below the `command` tag:

~~~
    volumes:
      - mysql-data:/var/lib/mysql
~~~

Now this maps a local data volume named `mysql-data` (which we haven't created yet) to the physical file path `/var/lib/mysql` inside our container.

### 5.  Add a local data volume

Now we need to add a local data volume to our application stack for use by MySQL.  Add a new top level tag called `volumes` to your `docker-compose.yml`, below the `services` tag and all of the declared services, and define your data volume as follows:

~~~
volumes:
  mysql-data:
    driver: local
~~~

Notice that the volume name, `mysql-data`, is the same as the volume we mapped for our MySQL container.  Now what will happen when we start our stack is that Docker will create a data volume with the virtual name `mysql-data`, and will mount that volume at `/var/lib/mysql` inside our database container.  

When we spin down our application stack with `docker-compose down`, our MySQL container will be destroyed, but our data volume will persist until we physically destroy it.  The next time we spin up our application stack with `docker-compose up -d`, Docker will check to see if the `mysql-data` volume exists; if it does, Docker will mount it to our MySQL container; if it doesn't Docker will create a new data volume to mount inside our MySQL container.

### 6.  Add a shared data volume for a seed database

When our MySQL container is started for the first time, a new database with the name `drupal` will be created and initialized with the provided configuration variables for the username and password.  Furthermore, it will execute files with extensions When a container is started for the first time, a new database with the specified name will be created and initialized with the provided configuration variables. Furthermore, it will execute files with extensions `.sh`, `.sql` and `.sql.gz` that are found in the internal path `/docker-entrypoint-initdb.d`.

We can use this information to map a shared data volume where we can place a seed database (which we'll create later) that will be imported into our designated database, `drupal`, when our MySQL container starts.

Create a directory in your project called `db`, and add the following to your `volumes` tag under your `db` service:

~~~
      - .db:/docker-entrypoint-initdb.d # Place init .sql file(s) here.
~~~

When our MySQL container is started, Docker will mount our host directory, `db`, at the physical location `docker-entrypoint-initdb.d` inside our MySQL container.  The official MySQL (and MariaDB) containers have a startup script that executes every time a new container instance is created; the startup script will import any `.sql` or `.sql.gz` files it finds in `db` into the database we created, `drupal`.

### 7.  Start up your application stack

Issue the command:

~~~
docker-compose up -d
~~~

Wait about 20 seconds, and issue the command:

~~~
docker-compose ps
~~~

You should see something similar to the following:

~~~
      Name                    Command               State               Ports             
-----------------------------------------------------------------------------------------
dockerdrop_db_1    docker-entrypoint.sh --cha ...   Up      3306/tcp                      
dockerdrop_php_1   php-fpm                          Up      9000/tcp                      
dockerdrop_web_1   nginx -g daemon off;             Up      443/tcp, 0.0.0.0:8000->80/tcp 
~~~

You can now see we have a new container called `db` in our stack.

Your docker compose file should look as follows:

~~~
version: '2'
services:
  web:
    build: ./docker/nginx/
    ports:
      - "8000:80"
    volumes_from:
      - php
    depends_on:
      - php

  php:
    image: php:7.0-fpm
    expose:
      - 9000
    volumes:
      - ./web:/var/www/html/web

  db:
    image: mariadb:10.1.19
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: drupal
      MYSQL_USER: drupal
      MYSQL_PASSWORD: drupal
    command: --character-set-server=utf8mb4 --collation-server=utf8mb4_unicode_ci # The simple way to override the mariadb config.
    volumes:
      - mysql-data:/var/lib/mysql
      - .db:/docker-entrypoint-initdb.d # Place init .sql file(s) here.

volumes:
  mysql-data:
    driver: local
~~~

<a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-sa/4.0/88x31.png" /></a><br />The instructional materials, which includes any materials in this repository included in markdown (.md) and/or text (.txt) files, are licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International License</a>.
