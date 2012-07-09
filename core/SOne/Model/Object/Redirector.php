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
 * @property-read string $redirectUrl
 */
class SOne_Model_Object_Redirector extends SOne_Model_Object
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
        if (!in_array($this->actionState, $this->aclEditActionsList)) {
            $env->response->sendRedirect($this->redirectUrl);
        }

        $node = new FVISNode('SONE_OBJECT_REDIRECTOR', 0, $env->get('VIS'));
        $node->addDataArray($this->pool);

        return $node;
    }

    /**
     * @param K3_Environment $env
     * @param bool           $updated
     */
    protected function saveAction(K3_Environment $env, &$updated = false)
    {
        parent::saveAction($env, $updated);
        // TODO: validation
        $this->pool['redirectUrl'] = $env->request->getString('redirectUrl', K3_Request::POST, FStr::LINE);
        $this->pool['updateTime']  = time();

        $updated = true;
    }

    /**
     * @param array $data
     * @return SOne_Model_Object_HTMLPage
     */
    protected function setData(array $data)
    {
        $this->pool['data'] = $data + array(
            'redirectUrl' => '/',
        );

        $this->pool['redirectUrl'] =& $this->pool['data']['redirectUrl'];

        return $this;
    }
}
