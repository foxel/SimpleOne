#!/bin/sh

docker stop elastic && docker rm elastic || true
docker run -d --name elastic -p 127.0.0.1:9200:9200 foxel/elastic
