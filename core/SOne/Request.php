<?php

/**
 * Special SOne Request class
 * @property K3_Request request
 * @property string path
 * @property string|null action
 */
class SOne_Request extends FBaseClass
{
    /**
     * @param K3_Environment $env
     */
    public function __construct(K3_Environment $env)
    {
        list ($path) = explode('?', preg_replace('#^index\.php/?#i', '', $env->request->url), 2);
        $query = $env->getRequest()->getURLParams();
        $action = null;
        if (reset($query) === '') { // for queries like foo/bar?edit
            $action = FStr::cast(key($query), FStr::WORD);
        } else {
            $action = $env->getRequest()->getString('action', K3_Request::POST, FStr::WORD);
        }

        $this->pool = array(
            'request' => $env->request,
            'path'    => FStr::cast($path, FStr::PATH),
            'action'  => $action,
        );
    }
}
