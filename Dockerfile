FROM php:7.1-apache

COPY backports.list /etc/apt/sources.list.d/

RUN apt-get update && apt-get install -y \
      wget\
      texlive-base\
      texlive-bibtex-extra\
      libjs-mathjax\
      fonts-mathjax\
      fonts-mathjax-extras &&\
      apt-get install -y python-certbot-apache -t jessie-backports

RUN certbot --apache

#ENV PMWIKI_VERSION 2.2.92
ENV PMWIKI_VERSION 2.2.97

RUN wget -O /tmp/pmwiki-${PMWIKI_VERSION}.tgz http://www.pmwiki.org/pub/pmwiki/pmwiki-${PMWIKI_VERSION}.tgz && \
    tar -xvzC /tmp/ -f /tmp/pmwiki-${PMWIKI_VERSION}.tgz && \
    cp -a /tmp/pmwiki-${PMWIKI_VERSION}/* /var/www/html/ && \
    mkdir /var/www/html/wiki.d/ &&  \
    chmod 2777 /var/www/html/wiki.d/ 

COPY index.php /var/www/html/

RUN chown -R www-data /var/www/html/wiki.d /var/www/html/pub /var/www/html/local /var/www/html/cookbook

VOLUME ["/var/www/html/wiki.d/","/var/www/html/local/","/var/www/html/cookbook/", "/var/www/html/pub"]
