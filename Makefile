# DIRS
DOCUMENTATION = docs
SOURCE = src
TOOLS = tools
TESTS = tests

.PHONY: all test docs clean

all: clean test docs

test:
	php $(TOOLS)/phpunit.phar --verbose $(TESTS)/XmlSerializerTest

docs:
	mkdir $(DOCUMENTATION)
	php $(TOOLS)/phpDocumentor.phar -p --template="clean" -d $(SOURCE)/ -t $(DOCUMENTATION)/

clean:
	rm -rf $(DOCUMENTATION)/
