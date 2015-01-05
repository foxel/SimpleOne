<?php
/**
 * Copyright (C) 2015 Andrey F. Kupreychik (Foxel)
 *
 * This file is part of QuickFox SimpleOne.
 *
 * SimpleOne is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SimpleOne is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SimpleOne. If not, see <http://www.gnu.org/licenses/>.
 */

require_once __DIR__.'/../core/bootstrap.php';

$config = new K3_Config(FMisc::loadDatafile(__DIR__.'/../data/sone.qfc.php', FMisc::DF_SLINE));

$connectorVersion = '5.1.34';
$connectorName = "mysql-connector-java-{$connectorVersion}";
$connectorFileName = $connectorName.'-bin.jar';

if (!file_exists($connectorPath = __DIR__.DIRECTORY_SEPARATOR.$connectorFileName)) {
    F()->appEnv->response->write('No MySQL connector jar file found, downloading to '.$connectorPath);
    $connectorDownloadResult = `curl -Ls http://dev.mysql.com/get/Downloads/Connector-J/$connectorName.tar.gz | tar -xOz '$connectorName/$connectorFileName' > '$connectorPath' && echo 'OK'`;
    F()->appEnv->response->write($connectorDownloadResult);
}

F()->appEnv->response->write('Generating liquibase.properties');
$properties = "#liquibase.properties
driver: com.mysql.jdbc.Driver
classpath: {$connectorFileName}
changeLogFile: simpleone.dev.xml
url: jdbc:mysql://{$config->db->dataSource->host}/{$config->db->dataSource->dbname}
username: {$config->db->username}
password: {$config->db->password}
";
file_put_contents(__DIR__.'/liquibase.properties', $properties);

F()->appEnv->response->write('Generating Makefile');
$makeFile = "all:
\t../lib/bin/liquibase update -Ddb.prefix={$config->db->prefix}";

file_put_contents(__DIR__.'/Makefile', $makeFile);

F()->appEnv->response->write('Done');
F()->appEnv->response->sendBuffer();
