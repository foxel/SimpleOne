SUBDIRS = db

all: setup $(SUBDIRS)
setup:
	./setup.sh

css: libs.bootstrap
	lessc lib/less/style.less > static/css/simple.css

libs: libs.bootstrap libs.jquery

libs.bootstrap:
	mkdir -p static/libs/bootstrap/js
	cp -rT lib/vendor/twbs/bootstrap/dist/fonts static/libs/bootstrap/fonts
	cp lib/vendor/twbs/bootstrap/dist/js/bootstrap*.js static/libs/bootstrap/js

libs.jquery:
	mkdir -p static/libs/jquery
	cp lib/vendor/components/jquery/jquery*.js static/libs/jquery/
	cp lib/vendor/components/jquery/jquery*.map static/libs/jquery/

docker:
	docker build -t foxel/simpleone .

docker-clean:
	git archive HEAD | \
	docker build -t foxel/simpleone -

$(SUBDIRS):
	$(MAKE) -C $@
.PHONY: $(SUBDIRS) setup docker static
