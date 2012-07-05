#!/bin/bash

lessc bootstrap/less/bootstrap.less > ../static/css/bootstrap.css
lessc bootstrap/less/responsive.less > ../static/css/bootstrap-responsive.css
lessc bootstrap/less/font-awesome-ie7.less > ../static/css/font-awesome-ie7.css

