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

class SOne_Model_Object_System_Root extends SOne_Model_Object
    implements SOne_Interface_Object_WithSubRoute
{
    /**
     * @param SOne_Environment $env
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env)
    {
        $env->response->sendRedirect($env->server->rootUrl);
    }

    /**
     * @param string $action
     * @param SOne_Model_User $user
     * @return bool
     */
    public function isActionAllowed($action, SOne_Model_User $user)
    {
        return false;
    }


    /**
     * @param string $subPath
     * @param SOne_Request $request
     * @param SOne_Environment $env
     * @return SOne_Model_Object
     */
    public function routeSubPath($subPath, SOne_Request $request, SOne_Environment $env)
    {
        $class='Page404';

        switch ($subPath) {
            case 'login':
                $class = 'LoginPage';
                break;
            case 'profile':
                $class = 'System_Profile';
                break;
        }

        return SOne_Model_Object::construct(array(
            'path'     => $this->path.'/'.$subPath,
            'isStatic' => true,
            'class'    => $class,
        ));
    }
}
