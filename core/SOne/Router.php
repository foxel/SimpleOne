<?php
/**
 * Copyright (C) 2016 Andrey F. Kupreychik (Foxel)
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

class SOne_Router extends FBaseClass
    implements SOne_Interface_Router
{
    /** @var SOne_Environment */
    protected $_staticRoutes;
    /** @var array */
    protected $_env;

    /**
     * @param SOne_Environment $env
     * @param K3_Config $config
     */
    public function __construct(SOne_Environment $env, K3_Config $config)
    {
        $this->_env = $env;

        if (($staticRoutes = $config->staticRoutes) && $staticRoutes instanceof K3_Config) {
            $this->_staticRoutes = $staticRoutes->toArray();
        }

        if ($systemRoute = $config->systemRoute) {
            $this->_staticRoutes[$systemRoute] = array(
                'class'       => 'System_Root',
                'path'        => $systemRoute,
                'accessLevel' => 1
            );
        }
    }

    /**
     * @param SOne_Request $request
     * @return SOne_Model_Object
     */
    public function routeRequest(SOne_Request $request)
    {
        /** @var $tipObject SOne_Model_Object */
        $tipObject = null;

        foreach ($this->_staticRoutes as $route => $data) {
            if ($request->path == $route || strpos($request->path, $route.'/') === 0) {
                if (!is_array($data)) {
                    $data = array(
                        'isStatic' => true,
                        'class'    => $data,
                        'path'     => $route,
                    );
                } else {
                    $data['path'] = $route;
                    $data['isStatic'] = true;
                }
                $tipObject = SOne_Model_Object::construct($data);
            }
        }

        if (!$tipObject) {
            $objectsRepository = SOne_Repository_Object::getInstance($this->_env->db);
            $navis = $objectsRepository->loadNavigationByPath($request->path);
            $tipObjectNavi = !empty($navis) ? end($navis) : null;
            $tipObject = $tipObjectNavi ? $objectsRepository->loadOne($tipObjectNavi['id']) : null;
        }

        if (($tipObject instanceof SOne_Model_Object) && (trim($tipObject->path, '/') == $request->path)) {
            // Routed OK
        } elseif ($tipObject instanceof SOne_Interface_Object_WithSubRoute) {
            $subPath = preg_replace('#'.preg_quote(trim($tipObject->path, '/').'/', '#').'#i', '', $request->path);
            /** @var $tipObject SOne_Interface_Object_WithSubRoute */
            $tipObject = $tipObject->routeSubPath($subPath, $request, $this->_env);
        } else {
            $tipObject = new SOne_Model_Object_Page404(array('path' => $request->path));
        }

        if ($tipObject->accessLevel > $this->_env->getUser()->accessLevel) {
            $tipObject = new SOne_Model_Object_Page403(array('path' => $request->path));
        }

        return $tipObject;
    }

}
