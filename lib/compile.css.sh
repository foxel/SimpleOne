#!/bin/bash

lessc less/bootstrap/bootstrap.less > ../static/css/bootstrap.css
lessc less/bootstrap/responsive.less > ../static/css/bootstrap-responsive.css
lessc less/bootstrap/font-awesome-ie7.less > ../static/css/font-awesome-ie7.css

lessc less/style.less > ../static/css/simple.css
