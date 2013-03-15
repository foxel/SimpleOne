<?php
/**
 * Copyright (C) 2013 Andrey F. Kupreychik (Foxel)
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
 * Class ElFinder_SOneVolumeDriver
 *
 * @author Foxel
 */
class ElFinder_SOneVolumeDriver extends elFinderVolumeLocalFileSystem
{
    /** @var SOne_Environment */
    protected $_env;

    protected $_fileInfoCache = array();
    protected $_addedHashes   = array();
    protected $_removedHashes = array();
    protected $_accessLevel   = 0;

    /**
     * @return bool
     * @throws FException
     */
    protected function init()
    {
        if (parent::init()) {
            $env = $this->options['env'];
            if (!$env instanceof SOne_Environment) {
                throw new FException('env should be instance of SOne_Environment');
            }
            $this->_env = $env;

            FMisc::addShutdownCallback(array($this, '_flushDbChanges'));

            return true;
        }

        return false;
    }

    /**
     * @param string $path
     * @param string $name
     * @param mixed $val
     * @return bool
     */
    protected function attr($path, $name, $val = null)
    {
        $perm = parent::attr($path, $name, $val);

        if ($name == 'write' || $name == 'locked') {
            $info = $this->_getFileInfo($path);
            $writeAllowed = ($info[0] == $this->_env->user->id) || ($this->_env->user->modLevel && $this->_env->user->modLevel >= $info[1]);
            if (!$writeAllowed) {
                $perm = ($name == 'locked');
            }
        } elseif ($name == 'hidden' && !$perm) {
            $info = $this->_getFileInfo($path);
            $perm = ($info[0] != $this->_env->user->id) && ($this->_env->user->accessLevel < $info[1]);
        }

        return $perm;
    }

    /**
     * Hook to prefetch cache
     * @param string $path
     * @return string[]
     */
    protected function _scandir($path)
    {
        $files = parent::_scandir($path);
        $this->_fetchInfoCache($files);
        return $files;
    }

    /**
     * @param string|string[] $paths
     */
    protected function _fetchInfoCache($paths)
    {
        $paths = (array) $paths;
        $hashes = array();
        foreach ($paths as $path) {
            $hashes[$path] = sha1($path);
        }

        $rows = $this->_env->db->doSelectAll('files', '*', array('path_sha1' => $hashes));
        foreach ($rows as $row) {
            if (array_key_exists($row['path_sha1'], $this->_fileInfoCache)) {
                continue;
            }
            $this->_fileInfoCache[$row['path_sha1']] = array(
                (int) $row['user_id'],
                (int) $row['acc_lvl'],
            );
        }

        $this->_fileInfoCache += array_fill_keys($hashes, null);
    }

    /**
     * @param string $path
     * @return array
     */
    protected function _getFileInfo($path)
    {
        $hash = sha1($path);
        if (!array_key_exists($hash, $this->_fileInfoCache)) {
            $this->_fetchInfoCache($path);
        }
        $info = $this->_fileInfoCache[$hash];
        return $info !== null ? $info : array($this->_env->user->id, $this->_accessLevel);
    }

    /**
     * @param string $path
     * @param string $srcPath
     */
    protected function _addFileInfo($path, $srcPath = null)
    {
        $hash = sha1($path);
        $this->_fileInfoCache[$hash] = $srcPath
            ? $this->_getFileInfo($srcPath)
            : array(
                (int) $this->_env->user->id,
                (int) $this->_accessLevel,
            );
        $this->_addedHashes[$hash] = 1;
    }

    /**
     * @param string $path
     */
    protected function _removeFileInfo($path)
    {
        $hash = sha1($path);
        $this->_fileInfoCache[$hash] = null;
        $this->_removedHashes[$hash] = 1;
        unset($this->_addedHashes[$hash]);
    }

    /**
     * flushes Info changes to DB
     */
    public function _flushDbChanges()
    {
        $hashesToRemove = array_keys($this->_addedHashes + $this->_removedHashes);
        $hashesToAdd = array_keys($this->_addedHashes);

        if (!empty($hashesToRemove)) {
            $this->_env->db->doDelete('files', array(
                'path_sha1' => $hashesToRemove,
            ));
        }

        if (!empty($hashesToAdd)) {
            $inserts = array();
            foreach ($hashesToAdd as $pathHash) {
                if (isset($this->_fileInfoCache[$pathHash])) {
                    $info = $this->_fileInfoCache[$pathHash];
                    $inserts[] = array(
                        'path_sha1' => $pathHash,
                        'user_id'   => $info[0],
                        'acc_lvl'   => $info[1],
                    );
                } else {
                    $inserts[] = array(
                        'path_sha1' => $pathHash,
                        'user_id' => $this->_env->user->id,
                        'acc_lvl' => $this->_accessLevel,
                    );
                }
            }
            $this->_env->db->doInsert('files', $inserts, false, FDataBase::SQL_MULINSERT);
        }
    }

    /** Here come methods overrides */

    /**
     * @param string $dst
     * @param string $name
     * @return array|bool
     */
    public function mkdir($dst, $name)
    {
        if ($stat = parent::mkdir($dst, $name)) {
            $this->_addFileInfo($this->decode($stat['hash']));
        }
        return $stat;
    }

    /**
     * @param string $dst
     * @param string $name
     * @return array|bool
     */
    public function mkfile($dst, $name)
    {
        if ($stat = parent::mkfile($dst, $name)) {
            $this->_addFileInfo($this->decode($stat['hash']));
        }
        return $stat;
    }

    /**
     * @param string $hash
     * @param string $name
     * @return array|bool
     */
    public function rename($hash, $name)
    {
        if ($stat = parent::rename($hash, $name)) {
            $this->_addFileInfo($this->decode($stat['hash']), $this->decode($hash));
            $this->_removeFileInfo($this->decode($hash));
        }
        return $stat;
    }

    /**
     * @param string $hash
     * @param string $suffix
     * @return array|bool
     */
    public function duplicate($hash, $suffix = 'copy')
    {
        if ($stat = parent::duplicate($hash, $suffix)) {
            $this->_addFileInfo($this->decode($stat['hash']), $this->decode($hash));
        }
        return $stat;
    }

    /**
     * @param Resource $fp
     * @param string $dst
     * @param string $name
     * @param string $tmpname
     * @return array|bool
     */
    public function upload($fp, $dst, $name, $tmpname)
    {
        if ($stat = parent::upload($fp, $dst, $name, $tmpname)) {
            $this->_addFileInfo($this->decode($stat['hash']));
        }
        return $stat;
    }

    /**
     * @param Object $volume
     * @param string $src
     * @param string $destination
     * @param string $name
     * @return string|bool
     */
    protected function copyFrom($volume, $src, $destination, $name)
    {
        if ($path = parent::copyFrom($volume, $src, $destination, $name)) {
            $this->_addFileInfo($path);
        }
        return $path;
    }

    /**
     * @param string $src
     * @param string $dst
     * @param string $name
     * @return string|bool
     */
    protected function copy($src, $dst, $name)
    {
        if ($path = parent::copy($src, $dst, $name)) {
            $this->_addFileInfo($path);
        }
        return $path;
    }

    /**
     * @param string $src
     * @param string $dst
     * @param string $name
     * @return string|bool
     */
    protected function move($src, $dst, $name)
    {
        if ($path = parent::move($src, $dst, $name)) {
            $this->_removeFileInfo($this->decode($src));
            $this->_addFileInfo($path);
        }
        return $path;
    }


    /**
     * @param string $hash
     * @return array|bool
     */
    public function extract($hash)
    {
        // TODO: implement file info setting
        return parent::extract($hash); 
    }

    /**
     * @param array $hashes
     * @param string $mime
     * @return array|bool
     */
    public function archive($hashes, $mime)
    {
        if ($stat = parent::archive($hashes, $mime)) {
            $this->_addFileInfo($this->decode($stat['hash']));
        }
        return $stat;
    }

    /**
     * @param string $path
     * @return bool
     */
    protected function remove($path)
    {
        if ($res = parent::remove($path)) {
            $this->_removeFileInfo($path);
        }
        return $res;
    }
}

/**
 * Class elFinderVolumeSOne
 *
 * @author Foxel
 */
class elFinderVolumeSOneFileSystem extends ElFinder_SOneVolumeDriver {}
