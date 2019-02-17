FROM php:7.2-fpm

MAINTAINER Lisa Ridley "lisa@codementality.com"

RUN apt-get update && apt-get install -yq \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng-dev \
    libyaml-dev \
    unzip \
    zip \
    mariadb-client \
    && rm -rf /var/lib/apt/lists/* \
    && apt autoremove -y \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure gd --with-png-dir=/usr --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install zip \
    && docker-php-ext-install opcache \
    && docker-php-ext-install zip \
    && docker-php-ext-install mbstring \
    && pecl install mcrypt-1.0.1 yaml \
    && docker-php-ext-enable mcrypt yaml
RUN chmod 644 /usr/local/etc/php/conf.d/* \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer --version
# Install Mailhog Sendmail support:
RUN apt-get update -qq && apt-get install -yq git golang-go \
    && mkdir -p /opt/go \
    && export GOPATH=/opt/go \
    && go get github.com/mailhog/mhsendmail \
# Add configuration to PHP for MHSendmail
    && { \
      echo 'sendmail_path = "/opt/go/bin/mhsendmail --smtp-addr=mailhog:1025"'; \
    } > /usr/local/etc/php/conf.d/mailhog.ini
# add PHP override config file
COPY conf.d/php.ini /usr/local/etc/php/conf.d/php.ini
# Add entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
# Make sure it's executable
RUN chmod a+x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

CMD ["php-fpm"]
