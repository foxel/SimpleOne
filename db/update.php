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

$configData = FMisc::loadDatafile(__DIR__.'/../data/sone.qfc.php', FMisc::DF_SLINE);
if (!$configData) {
    echo 'Please add configuration file data/sone.qfc.php'.PHP_EOL;
    exit();
}

$config = new K3_Config($configData);

$connectorVersion = '5.1.34';
$connectorName = "mysql-connector-java-{$connectorVersion}";
$connectorFileName = $connectorName.'-bin.jar';

if (!file_exists($connectorPath = __DIR__.DIRECTORY_SEPARATOR.$connectorFileName)) {
    echo 'No MySQL connector jar file found, downloading to '.$connectorPath.'... ';
    shell("curl -Ls 'http://dev.mysql.com/get/Downloads/Connector-J/$connectorName.tar.gz' | tar -xOz '$connectorName/$connectorFileName' > '$connectorPath'");
    echo 'OK'.PHP_EOL;
}

echo 'Updating DB'.PHP_EOL;

shell("../lib/bin/liquibase \\
    --changeLogFile=simpleone.dev.xml \\
    --username={$config->db->username} \\
    --password={$config->db->password} \\
    --url='jdbc:mysql://{$config->db->dataSource->host}/{$config->db->dataSource->dbname}' \\
    --driver=com.mysql.jdbc.Driver \\
    --classpath={$connectorFileName} \\
    update -Ddb.prefix={$config->db->prefix}
");

echo 'Done'.PHP_EOL;

/**
 * @param string $command
 * @throws FException
 */
function shell($command)
{
    list($exitCode, $output, $errors) = K3_Util_Exec::exec($command);
    if (!$exitCode) {
        echo $output.PHP_EOL;
    } else {
        error_log($errors);
        exit($exitCode);
    }
}

