<?php

interface SOne_Interface_Object_WithSubObjects
{
    /**
     * @abstract
     * @return array
     */
    public function getSubObjectsFilter();

    /**
     * @abstract
     * @param SOne_Model_Object[] $subObjects
     */
    public function setSubObjects(array $subObjects);

    /**
     * @abstract
     * @return SOne_Model_Object[]
     */
    public function getSubObjects();
}
