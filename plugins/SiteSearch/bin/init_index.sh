#!/usr/bin/env bash

HOST=$1
INDEX=$2

curl -XDELETE "http://${HOST}:9200/${INDEX}" && echo
curl -XPUT "http://${HOST}:9200/${INDEX}" -H 'Content-Type: application/json' -d '{
    "settings": {
        "analysis": {
            "analyzer": {
                "sone_analyzer": {
                    "type": "custom",
                    "tokenizer": "standard",
                    "filter": ["lowercase", "russian_hunspell", "english_hunspell", "my_stopwords"],
                    "char_filter": ["html_strip"]
                }
            },
            "filter": {
                "russian_hunspell": {
                    "type": "hunspell",
                    "language": "ru_RU"
                },
                "english_hunspell": {
                    "type": "hunspell",
                    "language": "en_US"
                },
                "my_stopwords": {
                    "type": "stop",
                    "stopwords": "а,без,более,бы,был,была,были,было,быть,в,вам,вас,весь,во,вот,все,всего,всех,вы,где,да,даже,для,до,его,ее,если,есть,еще,же,за,здесь,и,из,или,им,их,к,как,ко,когда,кто,ли,либо,мне,может,мы,на,надо,наш,не,него,нее,нет,ни,них,но,ну,о,об,однако,он,она,они,оно,от,очень,по,под,при,с,со,так,также,такой,там,те,тем,то,того,тоже,той,только,том,ты,у,уже,хотя,чего,чей,чем,что,чтобы,чье,чья,эта,эти,это,я,a,an,and,are,as,at,be,but,by,for,if,in,into,is,it,no,not,of,on,or,such,that,the,their,then,there,these,they,this,to,was,will,with"
                }
            }
        }
    },
    "mappings": {
        "properties" : {
            "path" : { "type" : "text", "index" : "false" },
            "createTime" : { "type" : "date", "format" : "date_time_no_millis" },
            "class" : { "type" : "keyword" },
            "caption" : { "type" : "text", "analyzer" : "sone_analyzer" },
            "content" : { "type" : "text", "analyzer" : "sone_analyzer" },
            "tags" : { "type" : "text", "analyzer" : "sone_analyzer" }
        }
    }
}' && echo
