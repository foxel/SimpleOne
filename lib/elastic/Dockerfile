FROM elasticsearch:8.18.0

# Download and install English dictionary
RUN mkdir -p /usr/share/elasticsearch/config/hunspell/en_US \
    && cd /tmp \
    && curl -O https://raw.githubusercontent.com/LibreOffice/dictionaries/master/en/en_US.aff \
    && curl -O https://raw.githubusercontent.com/LibreOffice/dictionaries/master/en/en_US.dic \
    && mv en_US.* /usr/share/elasticsearch/config/hunspell/en_US/

# Download and install Russian dictionary
RUN mkdir -p /usr/share/elasticsearch/config/hunspell/ru_RU \
    && cd /tmp \
    && curl -O https://raw.githubusercontent.com/LibreOffice/dictionaries/master/ru_RU/ru_RU.aff \
    && curl -O https://raw.githubusercontent.com/LibreOffice/dictionaries/master/ru_RU/ru_RU.dic \
    && mv ru_RU.* /usr/share/elasticsearch/config/hunspell/ru_RU/
