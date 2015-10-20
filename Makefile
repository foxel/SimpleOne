SUBDIRS = db

all: setup $(SUBDIRS)
setup:
	./setup.sh

css:
	lessc lib/less/bootstrap/bootstrap.less > static/css/bootstrap.css
	lessc lib/less/style.less > static/css/simple.css

docker:
	docker build -t foxel/simpleone .

docker-clean:
	git archive HEAD | \
	docker build -t foxel/simpleone -

$(SUBDIRS):
	$(MAKE) -C $@
.PHONY: $(SUBDIRS) setup docker
