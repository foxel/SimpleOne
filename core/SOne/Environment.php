<?php
/**
 * Copyright (C) 2013, 2015 Andrey F. Kupreychik (Foxel)
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

/**
 * @property K3_Db_Abstract $db
 * @property FVISInterface  $VIS
 * @property FLNGData       $lang
 *
 * @property SOne_Model_User  $user
 * @property SOne_Application $app
 *
 */
class SOne_Environment extends K3_Environment
{
    /** @var K3_Db_Abstract */
    protected $_db;
    /** @var FVISInterface */
    protected $_vis;
    /** @var FLNGData */
    protected $_lang;
    /** @var SOne_Application */
    protected $_app;
    /** @var SOne_Model_User */
    protected $_user;

    /**
     * @param K3_Environment $env
     * @return SOne_Environment
     */
    public static function prepare(K3_Environment $env = null)
    {
        if (is_null($env)) {
            $env = F()->appEnv;
        }

        /** @var SOne_Environment $instance */
        $instance = new static();
        $instance->setClient($env->getClient());
        $instance->setServer($env->getServer());
        $instance->setRequest($env->getRequest());
        $instance->setResponse($env->getResponse());
        $instance->setSession($env->getSession());

        return $instance;
    }

    /**
     * @param SOne_Application $app
     * @return $this
     */
    public function setApp(SOne_Application $app)
    {
        $this->_app = $app;
        return $this;
    }

    /**
     * @return SOne_Application
     */
    public function getApp()
    {
        return $this->_app;
    }

    /**
     * @param K3_Db_Abstract $db
     * @return $this
     */
    public function setDb(K3_Db_Abstract $db)
    {
        $this->_db = $db;
        return $this;
    }

    /**
     * @return K3_Db_Abstract
     */
    public function getDb()
    {
        return $this->_db;
    }

    /**
     * @param FLNGData $lang
     * @return $this
     */
    public function setLang(FLNGData $lang)
    {
        $this->_lang = $lang;
        return $this;
    }

    /**
     * @return FLNGData
     */
    public function getLang()
    {
        return $this->_lang;
    }

    /**
     * @param SOne_Model_User $user
     * @return $this
     */
    public function setUser(SOne_Model_User $user)
    {
        $this->_user = $user;
        return $this;
    }

    /**
     * @return SOne_Model_User
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * @param FVISInterface $vis
     * @return $this
     */
    public function setVIS(FVISInterface $vis)
    {
        $this->_vis = $vis;
        return $this;
    }

    /**
     * @return FVISInterface
     */
    public function getVIS()
    {
        return $this->_vis;
    }


}
