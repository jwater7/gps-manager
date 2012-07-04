#!/bin/bash

# its set up right now so it requires packages: doxygen graphviz mscgen

cd $(dirname $0)
cd ..

rm -r docs/html/
doxygen -g docs/Doxyfile.defaults
(
	test -e "docs/Doxyfile.defaults" && cat docs/Doxyfile.defaults;
	test -e "docs/Doxyfile.overrides" && cat docs/Doxyfile.overrides;
) | doxygen -

rm docs/Doxyfile.defaults
