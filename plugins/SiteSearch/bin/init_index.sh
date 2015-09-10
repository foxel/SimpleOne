#!/usr/bin/env bash

HOST=$1
INDEX=$2

curl -XPUT "http://${HOST}:9200/${INDEX}" -d '{
    "settings": {
        "analysis": {
            "analyzer": {
                "sone_analyzer": {
                    "type": "custom",
                    "tokenizer": "standard",
                    "filter": ["lowercase", "russian_morphology", "english_morphology", "my_stopwords"],
                    "char_filter": ["html_strip"]
                }
            },
            "filter": {
                "my_stopwords": {
                    "type": "stop",
                    "stopwords": "а,без,более,бы,был,была,были,было,быть,в,вам,вас,весь,во,вот,все,всего,всех,вы,где,да,даже,для,до,его,ее,если,есть,еще,же,за,здесь,и,из,или,им,их,к,как,ко,когда,кто,ли,либо,мне,может,мы,на,надо,наш,не,него,нее,нет,ни,них,но,ну,о,об,однако,он,она,они,оно,от,очень,по,под,при,с,со,так,также,такой,там,те,тем,то,того,тоже,той,только,том,ты,у,уже,хотя,чего,чей,чем,что,чтобы,чье,чья,эта,эти,это,я,a,an,and,are,as,at,be,but,by,for,if,in,into,is,it,no,not,of,on,or,such,that,the,their,then,there,these,they,this,to,was,will,with"
                }
            }
        }
    }
}' && echo
curl -XPUT "http://${HOST}:9200/${INDEX}/object/_mapping" -d '{
    "object": {
        "_all" : {"analyzer" : "russian_morphology"},
        "properties" : {
            "caption" : { "type" : "string", "index" : "not_analyzed" },
            "caption" : { "type" : "string", "analyzer" : "sone_analyzer" },
            "content" : { "type" : "string", "analyzer" : "sone_analyzer" },
            "tags" : { "type" : "string", "analyzer" : "russian_morphology" }
        }
    }
}' && echo
