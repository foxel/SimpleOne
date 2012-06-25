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
 */
class SOne_Model_Object_FileIndex extends SOne_Model_Object implements SOne_Interface_Object_WithSubRoute
{
    protected $_subPath = '';

    /**
     * @param  array $init
     */
    public function __construct(array $init = array())
    {
        parent::__construct($init);

        $this->setData((array)$this->pool['data']);
    }

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

        if (is_file($realPath)) {
            $fInfo = new finfo(FILEINFO_MIME_TYPE);
            $params = array(
                'contentType' => $fInfo->file($realPath),
            );
            $env->response->sendFile($realPath, $params);
        } else {
            $node = new FVISNode('SONE_FILES_FILEINDEX', 0, $env->get('VIS'));
            $node->addDataArray($this->pool)->addData('curPath', $this->_subPath);
            $dir = new DirectoryIterator($realPath ?: '.');
            $contents = array();
            foreach ($dir as $file) {
                /** @var $file DirectoryIterator */
                if ($file->isDot()) {
                    continue;
                }

                $contents[$file->getFilename()] = array(
                    'name' => $file->getFilename(),
                    'type' => $file->getType(),
                    'size' => $file->getSize(),
                    'path' => $this->path.'/'.($this->_subPath ? $this->_subPath.'/' : '').$file->getFilename(),
                );
            }
            if (!empty($contents)) {
                uksort($contents, 'strcasecmp');
                $node->appendChild('files', $contNode = new FVISNode('SONE_FILES_FILEITEM', FVISNode::VISNODE_ARRAY, $env->get('VIS')));
                $contNode->addDataArray($contents);
            }
            if ($this->_subPath) {
                $node->addData('upPath', $this->path.'/'.implode('/', array_slice(explode('/', trim($this->_subPath, '/')), 0, -1)));
            }

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
        $this->pool['updateTime'] = time();

        $updated = true;
    }

    /**
     * @param array $data
     * @return SOne_Model_Object_HTMLPage
     */
    protected function setData(array $data)
    {
        $this->pool['data'] = $data + array(
            'basePath' => F_SITE_ROOT,
        );

        $this->pool['basePath'] =& $this->pool['data']['basePath'];
        
        return $this;
    }
}
