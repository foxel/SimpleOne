<?php

class SOne_Request extends FBaseClass
{
    public function __construct(K3_Environment $env)
    {
        list ($path) = explode('?', preg_replace('#^index\.php/?#i', '', $env->requestUrl), 2);
        $query = $env->request->getURLParams();
        $action = null;
        if (reset($query) === '') { // for queries like foo/bar?edit
            $action = FStr::cast(key($query), FStr::WORD);
        } else {
            $action = $env->request->getString('action', K3_Request::POST, FStr::WORD);
        }

        $this->pool = array(
            'request' => $env->request,
            'path'    => FStr::cast($path, FStr::PATH),
            'action'  => $action,
        );
    }
}
