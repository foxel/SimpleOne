<?php
/**
 * Copyright (C) 2012 - 2013, 2015, 2020 Andrey F. Kupreychik (Foxel)
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
 * @property-read string $basePath
 * @property-read string $xAccelLocation
 * @property-read bool   $m3uEnabled
 * @property-read bool   $uploadAllowed
 * @property-read string $uploadAllow
 * @property-read int    $uploadLevel
 */
class SOne_Model_Object_FileIndex extends SOne_Model_Object
    implements SOne_Interface_Object_WithSubRoute, SOne_Interface_Object_Structured
{
    /** @var string */
    protected $_subPath = '';

    /**
     * @param  SOne_Environment $env
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env)
    {
        if ($this->actionState == 'uploader') {
            $startPath = $basePath = rtrim($this->basePath, DIRECTORY_SEPARATOR);
            if ($this->_subPath) {
                $startPath.= DIRECTORY_SEPARATOR.strtr($this->_subPath, array('/' => DIRECTORY_SEPARATOR));
            }
            $uploaderConfig = array(
                'roots' => array(array(
                    'alias' => $this->caption,
                    'path'  => $basePath,
                    'startPath' => $startPath,
                    'URL'   => K3_Util_Url::fullUrl($this->path, $env),
                    'uploadAllow' => $this->uploadAllow,
                    'uploadOrder' => 'allow,deny',
                )),
                'editLevel' => $this->uploadAllowed ? $this->uploadLevel : 999,
            );
            if (!empty($this->data['uploader']) && is_array($this->data['uploader'])) {
                $uploaderConfig += $this->data['uploader'];
            }
            $sub = new SOne_Model_Object_ElFinderConnector($uploaderConfig);
            return $sub->visualize($env);
        }

        if (in_array($this->actionState, array('save'))) {
            $env->response->sendRedirect($this->path);
        }

        $realPath = strtr($this->_subPath, array('/' => DIRECTORY_SEPARATOR));
        if ($this->basePath) {
            $realPath = $this->basePath.DIRECTORY_SEPARATOR.$realPath;
        }

        if ($this->actionState == 'm3u' && $this->m3uEnabled) {
            $iterator = $this->_getFSIterator($realPath, true, '/^.+\.(mp3|ogg|fla|flac|wav)$/i');
            $out = array();
            $realBasePath = realpath($this->basePath);
            foreach ($iterator as $file) {
                $out[] = strtr($file->getRealPath(), array($realBasePath => $this->path));
            }
            unset($iterator);
            sort($out);
            foreach ($out as &$item) {
                $item = K3_Util_Url::fullUrl(implode('/', array_map('rawurlencode', explode('/', $item))), $env);
            }
            $env->getResponse()->write(implode(PHP_EOL, $out))->sendBuffer(F::INTERNAL_ENCODING, array(
                'contentType' => 'audio/x-mpegurl',
                'filename'    => K3_Util_File::basename($realPath).'.m3u',
            ), K3_Response::DISPOSITION_ATTACHMENT);

            return null;
        }

        if (is_file($realPath)) {
            // TODO: improve this
            $fInfo = new finfo(FILEINFO_MIME_TYPE);
            $fileMTime  = filemtime($realPath);
            $params = array(
                'contentType' => $fInfo->file($realPath),
                'contentTime' => $fileMTime,
            );

            if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $fileMTime) {
                $env->getResponse()
                    ->clearBuffer()
                    ->setStatusCode(304)
                    ->sendBuffer('', $params);
            }

            if ($this->actionState == 'scale' && ($imgInfo = getimagesize($realPath))) {
                $w = $env->request->getNumber('w', K3_Request::GET);
                $h = $env->request->getNumber('h', K3_Request::GET);
                if (!$w && !$h) {
                    $env->response->sendRedirect($this->path.'/'.$this->_subPath);
                }
                $img = new K3_Image($realPath);
                $img->resize(min($img->width(), $w), min($img->height(), $h));

                $env->getResponse()
                    ->write($img->toString())
                    ->sendBuffer('', $params);
            } elseif ($this->xAccelLocation && ($env->getResponse() instanceof K3_Response_HTTP)) {
                $accelRedirectPath = $this->xAccelLocation.'/'.$this->_subPath;
                $params['filename'] = K3_Util_File::basename($realPath);
                $env->getResponse()
                    ->write('nGinx redirected to '.$accelRedirectPath)
                    ->setHeader('X-Accel-Redirect', $accelRedirectPath)
                    ->setHeader('X-Accel-Expires', 3600)
                    ->sendBuffer('', $params);
            } else {
                $env->response->sendFile($realPath, $params);
            }

            return null;
        } else {
            $node = new FVISNode('SONE_FILES_FILEINDEX', 0, $env->getVIS());
            $node->addDataArray($this->pool)->addData('curPath', $this->_subPath);
            $contents = array();
            if (is_dir($realPath)) {
                $dir = $this->_getFSIterator($realPath ?: '.');
                foreach ($dir as $file) {
                    /** @var $file DirectoryIterator */
                    if ($file->isDot()) {
                        continue;
                    }

                    if (strpos($file->getFilename(), '.') === 0) {
                        continue;
                    }

                    if (!is_readable($file->getRealPath())) {
                        continue;
                    }

                    $fileType = $file->getType();
                    // dereferencing type
                    if ($fileType == 'link') {
                        // fix missing symlink targets
                        if (!file_exists($file->getRealPath())) {
                            continue;
                        }

                        $fileType = is_dir($file->getRealPath())
                            ? 'dir'
                            : 'file';
                    }
                    $contents[$file->getFilename()] = array(
                        'name' => $file->getFilename(),
                        'type' => $fileType,
                        'size' => $file->getSize(),
                        'path' => $this->path.'/'.($this->_subPath ? $this->_subPath.'/' : '').$file->getFilename(),
                    );
                }
            }
            if (!empty($contents)) {
                uksort($contents, 'strcasecmp');
                //F2DArray::sort($contents, 'type');
                $node->appendChild('files', $contNode = new FVISNode('SONE_FILES_FILEITEM', FVISNode::VISNODE_ARRAY, $env->getVIS()));
                $contNode->addDataArray($contents)->sort('type');
            }
            if ($this->_subPath) {
                $node->addData('upPath', $this->path.'/'.implode('/', array_slice(explode('/', trim($this->_subPath, '/')), 0, -1)));
            }

            $node->addData('canEdit', $this->isActionAllowed('edit', $env->getUser()) ? 1 : null);
            $node->addData('canUpload', $this->canUpload($env->user) ? 1 : null);

            return $node;
        }
    }

    /**
     * @param string $subPath
     * @param SOne_Request $request
     * @param SOne_Environment $env
     * @return SOne_Model_Object
     */
    public function routeSubPath($subPath, SOne_Request $request, SOne_Environment $env)
    {
        if (preg_match('#(^|/)\.+(/|$)#', $subPath)) {
            return new SOne_Model_Object_Page403(array('path' => $this->path.'/'.$subPath));
        }

        $this->_subPath = trim($subPath, '/');

        $realPath = strtr($this->_subPath, array('/' => DIRECTORY_SEPARATOR));
        if ($this->basePath) {
            $realPath = $this->basePath.DIRECTORY_SEPARATOR.$realPath;
        }

        if (!file_exists($realPath)) {
            return new SOne_Model_Object_Page404(array('path' => $this->path.'/'.$subPath));
        }

        if (!is_readable($realPath)) {
            return new SOne_Model_Object_Page403(array('path' => $this->path.'/'.$subPath));
        }

        return $this;
    }

    /**
     * @param SOne_Model_User $user
     * @return bool
     */
    public function canUpload(SOne_Model_User $user)
    {
        return $this->uploadAllowed && $this->uploadLevel <= $user->accessLevel;
    }

    /**
     * @param SOne_Environment $env
     * @param bool $updated
     */
    protected function saveAction(SOne_Environment $env, &$updated = false)
    {
        parent::saveAction($env, $updated);
        $this->pool['basePath']       = $env->request->getString('basePath', K3_Request::POST, K3_Util_String::FILTER_PATH);
        $this->pool['m3uEnabled']     = $env->request->getBinary('m3uEnabled', K3_Request::POST, false);
        $this->pool['xAccelLocation'] = $env->request->getString('xAccelLocation', K3_Request::POST);

        $updated = true;
    }

    /**
     * @param array $data
     * @return SOne_Model_Object_HTMLPage
     */
    public function setData(array $data)
    {
        $this->pool['data'] = $data + array(
            'basePath'       => F_SITE_ROOT,
            'disposition'    => 'attachment',
            'xAccelLocation' => false,
            'm3uEnabled'    => false,
            'uploadAllowed' => false,
            'uploadAllow'   => 'image,application/x-shockwave-flash,video,audio',
            'uploadLevel'   => $this->editLevel,
        );

        $this->pool['basePath'] =& $this->pool['data']['basePath'];
        $this->pool['xAccelLocation'] =& $this->pool['data']['xAccelLocation'];
        $this->pool['m3uEnabled'] =& $this->pool['data']['m3uEnabled'];
        $this->pool['uploadAllowed'] =& $this->pool['data']['uploadAllowed'];
        $this->pool['uploadAllow'] =& $this->pool['data']['uploadAllow'];
        $this->pool['uploadLevel'] =& $this->pool['data']['uploadLevel'];

        return $this;
    }

    /**
     * @param string $dirPath
     * @param bool $recursive
     * @param string|null $filter
     * @return DirectoryIterator|RecursiveIteratorIterator|RegexIterator
     */
    protected function _getFSIterator($dirPath, $recursive = false, $filter = null)
    {
        if ($recursive) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath));
        } else {
            $iterator = new DirectoryIterator($dirPath);
        }


        if ($filter) {
            $iterator = new RegexIterator($iterator, $filter, RecursiveRegexIterator::MATCH);
        }

        return $iterator;
    }
}
