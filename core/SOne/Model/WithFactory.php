<?php
/**
 * Copyright (C) 2015 Andrey F. Kupreychik (Foxel)
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
 * Class SOne_Model_WithFactory
 * @author Andrey F. Kupreychik
 */
abstract class SOne_Model_WithFactory extends SOne_Model
{
    /** @var array[] */
    private static $_namespaces = array();
    /** @var array[] */
    private static $_prefixedNamespaces = array();
    /** @var string */
    protected static $_defaultClass;

    /**
     * adds new namespace for object classes lookup
     * @param string $namespace
     * @param string $prefix
     */
    public static function addNamespace($namespace, $prefix = null)
    {
        $baseClass = self::_checkClass();

        $namespace = (string) $namespace;
        if ($prefix) {
            self::$_prefixedNamespaces[$baseClass][$prefix] = $namespace;
        } elseif (!in_array($namespace, self::$_namespaces[$baseClass])) {
            array_unshift(self::$_namespaces[$baseClass], $namespace);
        }
    }

    /**
     * @param  array $init
     * @throws FException
     * @return static
     */
    public static function construct(array $init)
    {
        if (!isset($init['class'])) {
            throw new FException('SOne object construct without class specified');
        }

        if ($className = static::_getClassName(get_called_class(), $init['class'])) {
            return new $className($init);
        }

        return new static::$_defaultClass($init);
    }

    /**
     * @param string $baseClass
     * @param string $class
     * @return null|string
     */
    protected static function _getClassName($baseClass, $class)
    {
        $baseClass = self::_checkClass($baseClass);

        $classNames = array();

        foreach (self::$_prefixedNamespaces[$baseClass] as $prefix => $namespace) {
            if (strpos($class, $prefix.'_') !== 0) {
                continue;
            }
            $className = $namespace.'_'.ucfirst(substr($class, strlen($prefix)+1));
            if (class_exists($className, true)) {
                $classNames[] = $className;
            }
        }

        foreach (self::$_namespaces[$baseClass] as $namespace) {
            $className = $namespace.'_'.ucfirst($class);
            if (class_exists($className, true)) {
                $classNames[] = $className;
            }
        }

        $className = $baseClass.'_'.ucfirst($class);
        if (class_exists($className, true)) {
            $classNames[] = $className;
        }

        return reset($classNames);
    }

    /**
     * @param string $baseClass
     * @return array
     */
    protected static function _getClassReplaceMap($baseClass)
    {
        $baseClass = self::_checkClass($baseClass);

        $out = array();
        foreach (self::$_prefixedNamespaces[$baseClass] as $prefix => $namespace) {
            $out[$namespace.'_'] = $prefix.'_';
        }
        foreach (self::$_namespaces[$baseClass] as $namespace) {
            $out[$namespace.'_'] = '';
        }
        $out[$baseClass.'_'] = '';

        return $out;
    }

    /**
     * @param string $baseClass
     * @return string
     * @throws FException
     */
    private static function _checkClass($baseClass = null)
    {
        if (!$baseClass) {
            $baseClass = get_called_class();
        }

        if (!isset(self::$_namespaces[$baseClass])) {
            if (get_parent_class($baseClass) !== __CLASS__) {
                throw new FException(array('Factory works only on %s direct subclasses', __CLASS__));
            }

            if (empty(static::$_defaultClass)) {
                throw new FException(array('%s::$_defaultClass is not defined properly', get_called_class()));
            }

            self::$_namespaces[$baseClass] = array();
            self::$_prefixedNamespaces[$baseClass] = array();
        }

        return $baseClass;
    }

} 
