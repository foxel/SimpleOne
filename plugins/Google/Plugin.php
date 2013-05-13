<?php
/**
 * Copyright (C) 2012 - 2013 Andrey F. Kupreychik (Foxel)
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

class Google_Plugin
{
    /** @var SOne_Application */
    protected $_app;
    /** @var K3_Config */
    protected $_config;
    /** @var SOne_Model_Object */
    protected $_pageObject;

    /**
     * @param SOne_Application $app
     * @param K3_Config $config
     */
    public function __construct(SOne_Application $app, K3_Config $config)
    {
        $this->_app    = $app;
        $this->_config = $config;
        if ($this->_config->analytics || $this->_config->analyticsId) {
            $this->_app->addEventHandler(SOne_Application::EVENT_PAGE_OBJECT_ROUTED, array($this, 'grabPageObject'));
            $this->_app->addEventHandler(SOne_Application::EVENT_PAGE_RENDERED, array($this, 'addAppVisData'));
            $this->_app->addEventHandler(SOne_Application::EVENT_CRON_PROCESS, array($this, 'processCronJob'));
        }
    }

    /**
     * @param SOne_Model_Object $pageObject
     */
    public function grabPageObject(SOne_Model_Object $pageObject)
    {
        $this->_pageObject = $pageObject;
    }

    /**
     * @param FVISNode $pageNode
     */
    public function addAppVisData(FVISNode $pageNode)
    {
        $analyticsConfig = ($this->_config->analytics instanceof K3_Config)
            ? $this->_config->analytics->toArray()
            : (array) $this->_config->analytics;

        if (!isset($analyticsConfig['accountId'])) {
            $analyticsConfig['accountId'] = (string) $this->_config->analyticsId;
        }

        $user = $this->_app->getEnv()->getUser();

        $pageNode
            ->addNode('SONE_GOOGLE_ANALYTICS_JS', 'JS_BLOCKS', array(
                'userId'    => $user->id ? $user->id : null,
                'pageClass' => $this->_pageObject ? $this->_pageObject->class : 'unknown',
            ) + $analyticsConfig);
    }

    /**
     * @param SOne_Environment $env
     */
    public function processCronJob(SOne_Environment $env)
    {
        $config = ($this->_config->cron instanceof K3_Config)
            ? $this->_config->cron->toArray()
            : (array) $this->_config->cron;

        if (isset($config['statPaths'])) {
            $paths = is_array($config['statPaths'])
                ? $config['statPaths']
                : explode(',', $config['statPaths']);

            foreach ($paths as $path) {
                $this->fetchStats(rtrim($path, '/').'/', true);
            }
        }
    }

    /**
     * @return K3_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @param  string|array $scope
     * @return Google_API_Auth
     * @throws FException
     */
    public function getAPIAuth($scope)
    {
        if ($this->_config->API instanceof K3_Config) {
            return new Google_API_Auth($this->_config->API->accountName, $scope, $this->_config->API->privateKeyPath);
        }

        throw new FException('Google API auth parameters not set in config');
    }

    /**
     * @param $path
     * @param bool $updateData
     * @return array
     */
    public function fetchStats($path, $updateData = false)
    {
        $config  = $this->getConfig();
        $cacheId = FStr::shortHash($path);

        $rawStats = null;
        if ($statsCache = FCache::get('googleStats.'.$cacheId)) {
            if (!$updateData || $statsCache['timestamp'] >= time() - 900) {
                $rawStats = $statsCache['stats'];
            }
        }
        if ($rawStats === null) {
            try {
                $auth = Google_Bootstrap::getPluginInstance()->getAPIAuth(Google_API_Analytics::SCOPE_URL);
                $analytics = new Google_API_Analytics($auth);
                $rawStats = $analytics->getMostVisitedPagesStats($analytics->getFistProfileId($config->analytics->accountId), $path);
            } catch (Exception $e) {
                $rawStats = array();
            }
            FCache::set('googleStats.'.$cacheId, array(
                'timestamp' => time(),
                'stats' => $rawStats,
            ));
        }

        return $rawStats;
    }
}
