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

/**
 * @property-read string $basePath
 * @property-read string $xAccelLocation
 * @property-read bool   $m3uEnabled
 */
class SOne_Model_Object_FileIndex extends SOne_Model_Object
    implements SOne_Interface_Object_WithSubRoute, SOne_Interface_Object_Structured
{
    protected $_subPath = '';

    /**
     * @param  K3_Environment $env
     * @return FVISNode
     */
    public function visualize(K3_Environment $env)
    {
        if (in_array($this->actionState, array('save'))) {
            $env->response->sendRedirect($this->path);
        }

        $realPath = $this->_subPath;
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
                $item = FStr::fullUrl(implode('/', array_map('rawurlencode', explode('/', $item))));
            }
            $env->getResponse()->write(implode(PHP_EOL, $out))->sendBuffer(F::INTERNAL_ENCODING, array(
                'contentType' => 'audio/x-mpegurl',
                'filename'    => FStr::basename($realPath).'.m3u',
            ), K3_Response::DISPOSITION_ATTACHMENT);

            return null;
        }

        if (is_file($realPath)) {
            // TODO: improve this
            $fInfo = new finfo(FILEINFO_MIME_TYPE);
            $params = array(
                'contentType' => $fInfo->file($realPath),
            );
            if ($this->xAccelLocation) {
                $accelRedirectPath = $this->xAccelLocation.'/'.$this->_subPath;
                $params['filename'] = FStr::basename($realPath);
                $env->getResponse()
                    ->write('nGinx redirected to '.$accelRedirectPath)
                    ->setHeader('X-Accel-Redirect', $accelRedirectPath)
                    ->sendBuffer('', $params);
            } else {
                $env->response->sendFile($realPath, $params);
            }

            return null;
        } else {
            $node = new FVISNode('SONE_FILES_FILEINDEX', 0, $env->get('VIS'));
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

                    $fileType = $file->getType();
                    // dereferencing type
                    if ($fileType == 'link') {
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
                $node->appendChild('files', $contNode = new FVISNode('SONE_FILES_FILEITEM', FVISNode::VISNODE_ARRAY, $env->get('VIS')));
                $contNode->addDataArray($contents)->sort('type');
            }
            if ($this->_subPath) {
                $node->addData('upPath', $this->path.'/'.implode('/', array_slice(explode('/', trim($this->_subPath, '/')), 0, -1)));
            }

            $node->addData('canEdit', $this->isActionAllowed('edit', $env->get('user')) ? 1 : null);

            return $node;
        }
    }

    /**
     * @param string $subPath
     * @param SOne_Request $request
     * @param K3_Environment $env
     * @return SOne_Model_Object
     */
    public function routeSubPath($subPath, SOne_Request $request, K3_Environment $env)
    {
        $this->_subPath = $subPath;

        $realPath = $this->_subPath;
        if ($this->basePath) {
            $realPath = $this->basePath.DIRECTORY_SEPARATOR.$realPath;
        }

        if (!file_exists($realPath)) {
            return new SOne_Model_Object_Page404(array('path' => $this->path.'/'.$subPath));
        }

        return $this;
    }

    /**
     * @param K3_Environment $env
     * @param bool           $updated
     */
    protected function saveAction(K3_Environment $env, &$updated = false)
    {
        parent::saveAction($env, $updated);
        $this->pool['basePath']   = $env->request->getString('basePath', K3_Request::POST, FStr::PATH);
        $this->pool['m3uEnabled'] = $env->request->getBinary('m3uEnabled', K3_Request::POST, false);

        $updated = true;
    }

    /**
     * @param array $data
     * @return SOne_Model_Object_HTMLPage
     */
    public function setData(array $data)
    {
        $this->pool['data'] = $data + array(
            'basePath' => F_SITE_ROOT,
            'disposition' => 'attachment',
            'xAccelLocation' => false,
            'm3uEnabled' => false,
        );

        $this->pool['basePath'] =& $this->pool['data']['basePath'];
        $this->pool['xAccelLocation'] =& $this->pool['data']['xAccelLocation'];
        $this->pool['m3uEnabled'] =& $this->pool['data']['m3uEnabled'];

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
