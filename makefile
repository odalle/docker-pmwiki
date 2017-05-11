IMAGE=odalle/pmwiki:simple
IMAGE-ALPINE=odalle/pmwiki:alpine
BASEPATH= $(abspath .)
USERNAME=pmwiki
USERID=1000
PMWIKI_VERSION=2.2.92


.PHONY: build run clean

build: .build

alpine: .build-alpine

html:
	mkdir html
	cp index.php html/
	wget -O /tmp/pmwiki-$(PMWIKI_VERSION).tgz http://www.pmwiki.org/pub/pmwiki/pmwiki-${PMWIKI_VERSION}.tgz && tar -xvzC /tmp/ -f /tmp/pmwiki-${PMWIKI_VERSION}.tgz && cp -r /tmp/pmwiki-$(PMWIKI_VERSION)/* html/ && mkdir html/wiki.d/ && chmod 2777 html/wiki.d/ && cp -r cookbook html/

.build: Dockerfile-simple
	docker build -t $(IMAGE) -f Dockerfile-simple .
	touch .build

simple-cert:
	docker exec -it pmwiki certbot --apache -d www.olivier-dalle.fr -m olivier@olivier-dalle.fr --agree-tos
	docker exec -it pmwiki cp /000-default.conf /etc/apache2/sites-available/


.build-alpine: Dockerfile-alpine
	docker  build -t $(IMAGE-ALPINE) -f Dockerfile-alpine .


run: .build
	docker run -d -v $(BASEPATH)/html:/var/www/html/ \
		-v $(BASEPATH)/000-default.conf:/000-default.conf \
		-e APACHE_RUN_USER=$(USERNAME) \
		-e USERNAME=$(USERNAME) \
		-e USERID=$(USERID) \
		-p 80:80 \
		-p 443:443 \
		--name pmwiki $(IMAGE)

run-alpine: .build-alpine
	docker run -d -v $(BASEPATH)/html:/var/www/html/ \
		-e APACHE_RUN_USER=$(USERNAME) \
		-e USERNAME=$(USERNAME) \
		-e USERID=$(USERID) \
		-p 80:80 \
		--name pmwiki-alpine $(IMAGE-ALPINE)


clean:
	/bin/rm -f .build

