<?php
/**
 * Copyright (C) 2012 Andrey F. Kupreychik (Foxel)
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

class ElFinder_Model_Object_ElFinderConnector extends SOne_Model_Object
{
    /**
     * @param  array $init
     */
    public function __construct(array $init = array())
    {
        // connector is always hidden
        $init['hideInTree'] = true;
        parent::__construct($init);
    }

    /**
     * @param K3_Environment $env
     * @return FVISNode
     */
    public function visualize(K3_Environment $env)
    {
        $config = $this->_prepareConfig((array) $this->pool['data']);

        //var_dump($config); die;
        $finder = new elFinder($config);

        $isPost = $env->request->isPost;
        $src    = $isPost ? $_POST : $_GET;
        $cmd    = isset($src['cmd']) ? $src['cmd'] : '';
        $args   = array();

        if (!function_exists('json_encode')) {
            $error = $finder->error(elFinder::ERROR_CONF, elFinder::ERROR_CONF_NO_JSON);
            $env->response
                ->write('{"error":["'.implode('","', $error).'"]}')
                ->sendBuffer('', array('contentType' => 'application/json'));
        }

        if (!$finder->loaded()) {
            $env->response
                ->write(json_encode(array('error' => $finder->error(elFinder::ERROR_CONF, elFinder::ERROR_CONF_NO_VOL), 'debug' => $finder->mountErrors)))
                ->sendBuffer('', array('contentType' => 'application/json'));
        }

        // telepat_mode: on
        if (!$cmd && $isPost) {
            $env->response
                ->write(json_encode(array('error' => $finder->error(elFinder::ERROR_UPLOAD, elFinder::ERROR_UPLOAD_TOTAL_SIZE))))
                ->sendBuffer('', array('contentType' => 'text/html'));
        }
        // telepat_mode: off

        if (!$finder->commandExists($cmd)) {
            $env->response
                ->write(json_encode(array('error' => $finder->error(elFinder::ERROR_UNKNOWN_CMD))))
                ->sendBuffer('', array('contentType' => 'application/json'));
        }

        // collect required arguments to exec command
        foreach ($finder->commandArgsList($cmd) as $name => $req) {
            $arg = $name == 'FILES'
                ? $_FILES
                : (isset($src[$name]) ? $src[$name] : '');

            if (!is_array($arg)) {
                $arg = trim($arg);
            }
            if ($req && (!isset($arg) || $arg === '')) {
                $env->response
                    ->write(json_encode(array('error' => $finder->error(elFinder::ERROR_INV_PARAMS, $cmd))))
                    ->sendBuffer('', array('contentType' => 'application/json'));
            }
            $args[$name] = $arg;
        }

        $args['debug'] = isset($src['debug']) ? !!$src['debug'] : false;

        $response = $finder->exec($cmd, $args);

        $env->response
            ->write(json_encode($response))
            ->sendBuffer('', array('contentType' => 'application/json'));
    }

    /**
     * @param array $config
     * @return array
     */
    protected function _prepareConfig(array $config)
    {
        $rootDefaults = array(
            'treeDeep'        => 3,
            'mimeDetect'      => 'internal',
            'tmbPath'         => '.tmb',
            'utf8fix'         => true,
            'tmbCrop'         => false,
            'tmbBgColor'      => 'transparent',
            'accessControl'   => array($this, 'checkAccess'),
            'acceptedName'    => '/^[^\.].*$/',
            'attributes'      => array(
                array(
                    'pattern' => '/\.(js|php)$/',
                    'read'    => true,
                    'write'   => false
                ),
            )
        );

        $explodable = array('uploadAllow', 'uploadDeny', 'uploadOrder');

        if (isset($config['roots']) && is_array($config['roots'])) {
            foreach ($config['roots'] as &$rootConfig) {
                $rootConfig = ((array)$rootConfig) + $rootDefaults;
                foreach ($explodable as $confName) {
                    if ($rootConfig[$confName] && !is_array($rootConfig[$confName])) {
                        $rootConfig[$confName] = array_map('trim', explode(',', $rootConfig[$confName]));
                    }
                }
            }
        }

        return $config;
    }

    /**
     * @param string $attr  attribute name (read|write|locked|hidden)
     * @param string $path  file path relative to volume root directory started with directory separator
     * @param $data
     * @param $volume
     * @return bool|null
     */
    public function checkAccess($attr, $path, $data, $volume)
    {
        return strpos(FStr::basename($path), '.') === 0 // if file/folder begins with '.' (dot)
            ? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
            : null; // else elFinder decide it itself

    }

}
