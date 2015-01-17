SUBDIRS = db

all: setup $(SUBDIRS)
setup: dummy
	./setup.sh

$(SUBDIRS):
	$(MAKE) -C $@
.PHONY: $(SUBDIRS)
