SUBDIRS = db

all: setup $(SUBDIRS)
setup:
	./setup.sh

docker:
	docker build -t foxel/simpleone .

docker-clean:
	git archive HEAD | \
	docker build -t foxel/simpleone -

$(SUBDIRS):
	$(MAKE) -C $@
.PHONY: $(SUBDIRS) setup docker
