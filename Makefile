SUBDIRS = db docker

all: setup $(SUBDIRS)
setup:
	./setup.sh

$(SUBDIRS):
	$(MAKE) -C $@
.PHONY: $(SUBDIRS) setup
