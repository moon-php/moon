FROM composer:latest as composer
FROM php:fpm

# Define ENVs and ARGs
ARG XDEBUG_REMOTE_HOST=docker.for.mac.localhost
ENV XDEBUG_CONFIGURATION_FILE='/usr/local/etc/php/conf.d/xdebug.ini'
ENV OPCACHE_FILE=$PHP_INI_DIR/conf.d/opcache.ini

# Add the project to the container
WORKDIR /moon
ADD . /moon

# Install dependeies for PHP
RUN apt-get update && \
    apt-get install -y git libzip-dev && \
    docker-php-ext-install zip

# Install dependecies (dev included)
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer install

# Install XDebug and add XDebug configurations
RUN yes | pecl install xdebug
RUN echo 'xdebug.idekey=MOON' >> $XDEBUG_CONFIGURATION_FILE && \
    echo 'xdebug.remote_enable=1' >> $XDEBUG_CONFIGURATION_FILE && \
    echo 'xdebug.remote_port=9090' >> $XDEBUG_CONFIGURATION_FILE && \
    echo 'xdebug.remote_connect_back=0' >> $XDEBUG_CONFIGURATION_FILE && \
    echo 'xdebug.remote_autostart=1' >> $XDEBUG_CONFIGURATION_FILE && \
    echo 'xdebug.remote_log="/var/log/xdebug/xdebug.log"' >> $XDEBUG_CONFIGURATION_FILE && \
    echo "xdebug.remote_host=$XDEBUG_REMOTE_HOST" >> $XDEBUG_CONFIGURATION_FILE && \
    echo ';;settings for profiling' >> $XDEBUG_CONFIGURATION_FILE && \
    echo 'xdebug.profiler_enable_trigger=1' >> $XDEBUG_CONFIGURATION_FILE && \
    echo 'xdebug.profiler_output_name=xdebug.out.%t' >> $XDEBUG_CONFIGURATION_FILE && \
    echo 'xdebug.profiler_output_dir="/tmp/xdebug"' >> $XDEBUG_CONFIGURATION_FILE && \
    echo 'xdebug.profiler_enable_trigger=1' >> $XDEBUG_CONFIGURATION_FILE && \
    echo 'xdebug.trace_enable_trigger=1' >> $XDEBUG_CONFIGURATION_FILE && \
    echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" >> $XDEBUG_CONFIGURATION_FILE

RUN mkdir /var/log/xdebug && chmod 0777 /var/log/xdebug
