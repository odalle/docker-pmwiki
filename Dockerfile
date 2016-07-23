FROM nginx:stable

RUN apt-get update\
    && apt-get install -y wget php5 php5-fpm

# make php-fpm run as user nginx
RUN sed -i 's/www-data/nginx/g' /etc/php5/fpm/pool.d/*

ENV PMWIKI_VERSION 2.2.88

RUN wget -O /tmp/pmwiki-${PMWIKI_VERSION}.tgz http://www.pmwiki.org/pub/pmwiki/pmwiki-${PMWIKI_VERSION}.tgz && \
    tar -xvzC /tmp/ -f /tmp/pmwiki-${PMWIKI_VERSION}.tgz && \
    mkdir -p /var/www/html/ &&  \
    cp -r /tmp/pmwiki-${PMWIKI_VERSION}/* /var/www/html/ && \
    mkdir -p /var/www/html/wiki.d/ &&  \
    chmod 2777 /var/www/html/wiki.d/ 

COPY default.conf /etc/nginx/conf.d/
COPY index.php /var/www/html/
COPY run.sh /

VOLUME ["/var/www/html/wiki.d/","/var/www/html/local/","/var/www/html/cookbook/", "/var/www/html/pub"]

ENTRYPOINT ["/run.sh"]
