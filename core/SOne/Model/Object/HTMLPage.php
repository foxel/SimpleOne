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

/**
 * @property string $content
 */
class SOne_Model_Object_HTMLPage extends SOne_Model_Object_PlainPage
    implements SOne_Interface_Object_AcceptChildren
{
    /**
     * @param SOne_Environment $env
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env)
    {
        $node = parent::visualize($env);
        $node->setType('SONE_OBJECT_HTMLPAGE');
        return $node;
    }

    protected function saveAction(SOne_Environment $env, &$updated = false)
    {
        parent::saveAction($env, $updated);

        $user = $env->getUser();

        $dom = new K3_DOM('4.0', F::INTERNAL_ENCODING);
        if ($dom->loadHTML(mb_convert_encoding($this->content, 'HTML-ENTITIES', F::INTERNAL_ENCODING))) {
            $dom->encoding = F::INTERNAL_ENCODING;
            if (!$user->adminLevel) {
                $dom->stripXSSVulnerableCode(array('youtube.com'));
            }
            $content       = $dom->saveXML($dom->getElementsByTagName('body')->item(0));
            $this->content = str_replace(array('<body>', '</body>', '&#13;'), '', $content);
        } else {
            $this->content = '';
        }
    }

}
