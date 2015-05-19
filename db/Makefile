all: update
update:
	php update.php < /dev/null
clean:
	rm -f databasechangelog.csv
	rm -f mysql-*.jar
sql: mysql
mysql:
	rm -f databasechangelog.csv
	../lib/bin/liquibase \
	 --changeLogFile=simpleone.dev.xml \
	 --url='offline:$@?version=5.0.0&outputLiquibaseSql=true' \
	 updateSQL -Ddb.prefix=qfso_ | grep -v 'Liquibase Home' > sql/$@.sql
.PHONY: mysql clean
