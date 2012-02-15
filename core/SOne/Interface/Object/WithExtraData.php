<?php

interface SOne_Interface_Object_WithExtraData
{
    public function loadExtraData(FDataBase $db);
    public function saveExtraData(FDataBase $db);
}
