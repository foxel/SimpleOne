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

class ElFinder_Model_Object_ElFinderConnector extends SOne_Model_Object
{
    /** @var array */
    protected $_config = array();
    /** @var bool */
    protected $_writeAccess = false;

    /**
     * @param  array $init
     */
    public function __construct(array $init = array())
    {
        // connector is always hidden
        $init['hideInTree'] = true;
        parent::__construct($init);

        $this->_config = (array) $this->pool['data'];
    }

    /**
     * @param SOne_Environment $env
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env)
    {
        $this->_writeAccess = ($this->editLevel <= $env->user->accessLevel);

        $finder = new elFinder($this->_prepareFinderConfig($this->_config, $env));

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

        $this->_filterCmd($cmd, $args);

        $response = $finder->exec($cmd, $args);

        if (F_DEBUG) {
            $response['dbQueries'] = $env->db->history;
        }

        $env->response
            ->write(json_encode($response))
            ->sendBuffer('', array('contentType' => 'application/json'));
    }

    /**
     * @param array $config
     * @param SOne_Environment $env
     * @return array
     */
    protected function _prepareFinderConfig(array $config, SOne_Environment $env)
    {
        $rootDefaults = array(
            'driver'          => 'SOneFileSystem',
            'treeDeep'        => 3,
            'mimeDetect'      => 'internal',
            'tmbPath'         => '.tmb',
            'utf8fix'         => true,
            'tmbCrop'         => false,
            'tmbBgColor'      => 'transparent',
            'accessControl'   => array($this, '_accessControl'),
            'acceptedName'    => '/^[^\.].*$/',
            'attributes'      => array(
                array(
                    'pattern' => '/\.(js|php)$/',
                    'read'    => true,
                    'write'   => false,
                    'locked'  => true,
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
                $rootConfig['env'] = $env;
            }
        }

        $config['bind'] = array('upload' => array($this, '_uploadHook'));

        return $config;
    }

    /**
     * @param string $attr  attribute name (read|write|locked|hidden)
     * @param string $path  file path relative to volume root directory started with directory separator
     * @param $data
     * @param $volume
     * @return bool|null
     */
    public function _accessControl($attr, $path, $data, $volume)
    {
        switch ($attr) {
            case 'locked':
                return (!$this->_writeAccess || strpos(FStr::basename($path), '.') === 0) ? true : null;
            case 'hidden':
                return (strpos(FStr::basename($path), '.') === 0) ? true : null;
            case 'write':
                return !$this->_writeAccess ? false : null;
            case 'read':
            default:
                return null;
        }
    }

    /**
     * @param string $action
     * @param array $result
     * @param array $args
     * @param elFinder $finder
     */
    public function _uploadHook($action, array &$result, array $args, elFinder $finder)
    {
        if ($action != 'upload' || !isset($result['added']) || !is_array($result['added'])) {
            return;
        }

        $imageActionsNeeded = false;

        $autoResize = $maxWidth = $maxHeight = null;
        if (isset($this->_config['maxImageWidth']) || isset($this->_config['maxImageHeight'])) {
            $imageActionsNeeded = $autoResize = true;
            $maxWidth = isset($this->_config['maxImageWidth']) && is_numeric($this->_config['maxImageWidth'])
                ? (int) $this->_config['maxImageWidth']
                : PHP_INT_MAX;
            $maxHeight = isset($this->_config['maxImageHeight']) && is_numeric($this->_config['maxImageHeight'])
                ? (int) $this->_config['maxImageHeight']
                : PHP_INT_MAX;
        }

        $addWatermark = $watermarkFont = $watermarkText = $watermarkSize = null;
        if (isset($this->_config['watermarkFont']) && isset($this->_config['watermarkText'])) {
            $imageActionsNeeded = $addWatermark = true;
            $watermarkFont = realpath($this->_config['watermarkFont']);
            $watermarkText = $this->_config['watermarkText'];
            $watermarkSize = isset($this->_config['watermarkSize']) ? $this->_config['watermarkSize'] : 22;
        }

        foreach ($result['added'] as &$file) {
            $path = $finder->realpath($file['hash']);

            if ($imageActionsNeeded && (strpos($file['mime'], 'image/') === 0) && ($iData = getimagesize($path))) {
                if ($autoResize && ($iData[0] > $maxWidth || $iData[1] > $maxHeight)) {
                    $s = min($maxWidth/$iData[0], $maxHeight/$iData[1]);
                    if ($s > 0 && $s < 1) {
                        $w = (int) $iData[0]*$s;
                        $h = (int) $iData[1]*$s;
                        $res = $finder->exec('resize', array(
                            'target' => $file['hash'],
                            'width'  => $w,
                            'height' => $h,
                            'x'      => false,
                            'y'      => false,
                            'mode'   => 'resize',
                            'degree' => false
                        ));

                        if (isset($res['changed'])) {
                            $file = array_shift($res['changed']);
                        }
                    }
                }
                if ($addWatermark && $this->_watermark($path, $watermarkText, $watermarkFont, $watermarkSize)) {
                    $file['ts'] = filemtime($path);
                    $file['size'] = @filesize($path);
                    $iData = getimagesize($path);
                    $file['mime'] = $iData['mime'];
                }
            }
        }
    }

    /**
     * @param string $action
     * @param array $args
     */
    protected function _filterCmd($action, array &$args)
    {
        switch ($action) {
            case 'upload':
                foreach ($args['FILES'] as &$file) {
                    foreach ($file['name'] as &$name) {
                        $name = FStr::cast($name, FStr::PATH);
                    }
                }
                break;
            case 'rename':
                $args['name'] = FStr::cast($args['name'], FStr::PATH);
                break;
        };
    }

    /**
     * @param string $path
     * @param string $text
     * @param string $fontFile
     * @param int|string $fontSize
     * @return bool
     */
    protected function _watermark($path, $text, $fontFile, $fontSize = 24)
    {
        if ($img = K3_Image::load($path)) {
            $img->watermark($text, $fontFile, $fontSize);
            $result = $img->save($path, null, $img->format == IMAGETYPE_JPEG ? 100 : 7);

            return $result;
        }

        return false;
    }

}
