<?php

interface SOne_Interface_Object_WithExtraData
{
    /**
     * @abstract
     * @param FDataBase $db
     */
    public function loadExtraData(FDataBase $db);

    /**
     * @abstract
     * @param FDataBase $db
     */
    public function saveExtraData(FDataBase $db);
}
