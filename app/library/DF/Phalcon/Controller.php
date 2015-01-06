<?php
namespace DF\Phalcon;

use \DF\Session;
use \DF\Url;

class Controller extends \Phalcon\Mvc\Controller
{
    /* URL Parameter Handling */

    /**
     * Retrieve parameter from request.
     *
     * @param $param_name
     * @param null $default_value
     * @return mixed|null
     */
    public function getParam($param_name, $default_value = NULL)
    {
        if ($param = $this->dispatcher->getParam($param_name))
            return $param;
        elseif ($this->request->has($param_name))
            return $this->request->get($param_name);
        else
            return $default_value;
    }

    /**
     * Alias for getParam()
     *
     * @param $param_name
     * @param null $default_value
     * @return mixed|null
     */
    public function _getParam($param_name, $default_value = NULL)
    {
        return $this->getParam($param_name, $default_value);
    }

    /**
     * Detect if parameter is present in request.
     *
     * @param $param_name
     * @return bool
     */
    public function hasParam($param_name)
    {
        return ($this->getParam($param_name) !== null);
    }

    /**
     * Alias for hasParam()
     *
     * @param $param_name
     * @return bool
     */
    public function _hasParam($param_name)
    {
        return $this->hasParam($param_name);
    }

    /**
     * Trigger rendering of template.
     *
     * @param null $template_name
     */
    public function render($template_name = NULL)
    {
        if (!is_null($template_name))
            $this->view->pick($template_name);
    }

    /**
     * Disable rendering of template for this page view.
     */
    public function doNotRender()
    {
        $this->view->disable();
    }

    /**
     * Render the page output as the supplied JSON.
     *
     * @param $json_data
     */
    public function renderJson($json_data)
    {
        $this->doNotRender();
        $this->response->setJsonContent($json_data);
    }

    /**
     * Determines if a request is sent using the XMLHTTPRequest (AJAX) method.
     *
     * @return mixed
     */
    public function isAjax()
    {
        return $this->request->isAjax();
    }

    /* URL Redirection */

    /**
     * Redirect to the URL specified.
     *
     * @param $new_url
     * @param int $code
     */
    public function redirect($new_url, $code = 302)
    {
        $this->doNotRender();
        $this->response->redirect($new_url, $code);
    }

    /**
     * Redirect to the route specified.
     *
     * @param $route
     * @param int $code
     */
    public function redirectToRoute($route, $code = 302)
    {
        $this->doNotRender();
        $this->response->redirect(Url::route($route, $this->di), $code);
    }

    /**
     * Redirect with parameters from the current URL.
     *
     * @param $url_params
     * @param int $code
     */
    public function redirectFromHere($route, $code = 302)
    {
        $this->doNotRender();
        $this->response->redirect(Url::routeFromHere($route, $this->di), $code);
    }

    /**
     * Force redirection to a HTTPS secure URL.
     */
    protected function forceSecure()
    {
        if (DF_APPLICATION_ENV == 'production' && !DF_IS_SECURE)
        {
            $this->doNotRender();

            $url = 'https://'.$this->request->getHttpHost().$this->request->getURI();
            $this->response->redirect($url, 301);
            exit;
        }
    }

    /* Referrer storage */
    protected function storeReferrer($namespace = 'default', $loose = true)
    {
        $session = Session::get('referrer_'.$namespace);

        if( !isset($session->url) || ($loose && isset($session->url) && Url::current() != Url::referrer()) )
            $session->url = Url::referrer();
    }

    protected function getStoredReferrer($namespace = 'default')
    {
        $session = Session::get('referrer_'.$namespace);
        return $session->url;
    }

    protected function clearStoredReferrer($namespace = 'default')
    {
        $session = Session::get('referrer_'.$namespace);
        unset($session->url);
    }

    protected function redirectToStoredReferrer($namespace = 'default', $default_url = false)
    {
        $referrer = $this->getStoredReferrer($namespace);
        $this->clearStoredReferrer($namespace);

        if( trim($referrer) == '' )
            if( $default_url )
                $referrer = $default_url;
            else
                $referrer = Url::baseUrl();

        $this->redirect($referrer);
    }

    protected function redirectToReferrer($default = false)
    {
        if( !$default )
            $default = Url::baseUrl();

        $this->redirect(Url::referrer($default));
    }

    /* Notifications */

    public function flash($message, $level = \DF\Flash::INFO)
    {
        $this->alert($message, $level);
    }
    public function alert($message, $level = \DF\Flash::INFO)
    {
        \DF\Flash::addMessage($message, $level);
    }
}