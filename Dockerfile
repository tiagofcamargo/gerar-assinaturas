FROM php:8.1-apache

MAINTAINER webmaster@portalassinaturas.com.br

WORKDIR /var/www/portalassinaturas.com.br

RUN apt-get update && apt-get install nano -y \
                libfreetype6-dev \
                libjpeg62-turbo-dev \
                libpng-dev \
                libzip-dev \
                zip \
                supervisor \
                software-properties-common \
                fontforge \
                python3-pip \
                pngcrush \
        && docker-php-ext-configure gd --with-freetype --with-jpeg \
        && docker-php-ext-install -j$(nproc) gd zip mysqli pdo pdo_mysql pcntl\
        && docker-php-ext-enable pdo_mysql

RUN mkdir -p /var/www/portalassinaturas.com.br && mkdir -p /var/www/portalassinaturas.com.br/public_html

RUN echo "ServerName portalassinaturas.com.br" >> /etc/apache2/apache2.conf

COPY ./ /var/www/portalassinaturas.com.br/public_html
COPY ./server/sites-available  /etc/apache2/sites-available

RUN a2enmod rewrite \
        && a2enmod ssl \
        && a2enmod headers \
        && a2enmod proxy \
        && a2enmod proxy_http \
        && a2enmod proxy_balancer \
        && a2enmod lbmethod_byrequests \
        && a2dissite 000-default.conf \
        && a2ensite portalassinaturas.com.br.conf \
        && service apache2 restart

EXPOSE 3000 443 8000 6379 5173

# ENTRYPOINT ["/usr/sbin/apache2ctl"]
# CMD ["-D", "FOREGROUND"]