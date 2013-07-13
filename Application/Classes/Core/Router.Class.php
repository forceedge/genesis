<?php

namespace Application\Core;



class Router extends AppMethods{

    private
            $url,
            $lastRoute,
            $lastURL,
            $funcVariables,
            $routePattern,
            $route,
            $params,
            $Router,
            $ObjectArguments = array(),
            $pattern;

    public static $Route = array(), $LastRoute;

    public function __construct() {

        $this->url = $_SERVER['PHP_SELF'];

        $this->SetPattern()->SetParams();
    }

    public function GetPattern(){

        return $this->pattern;
    }

    /**
     *
     * @return boolean - true on success, false on failure<br />
     * <br />Get pattern appended to index.php in url
     */
    protected function SetPattern(){

        $this->pattern = (isset($_SERVER['PATH_INFO']) ? str_replace('//', '/', $_SERVER['PATH_INFO'] . '/') : '/');

        return $this;

    }

    /**
     * Exploder pattern to an array in $this->params;
     */
    private function SetParams(){

        $this->params = explode('/', $this->pattern);
        return $this;
    }

    /**
     *
     * @return boolean - true on success, false on failure<br />
     * <br />Forwards the request to the appropriate controller once the params are read.
     */
    public function ForwardRequest(){

        $value = array();

        if(\Get::Config('Application.Environment.UnderDevelopmentPage.State'))
        {
            if(!$this->Variable(getenv('REMOTE_ADDR'))->Has(\Get::Config('Application.Environment.UnderDevelopmentPage.ExemptIPs')))
            {
                $controllerAction = explode(':', \Get::Config('Application.Environment.UnderDevelopmentPage.Controller'));
                $this->CallAction($this->GetControllerNamespace($controllerAction), $controllerAction[2] . 'Action');
            }
        }

        $this->funcVariables = array();

        // Should the value contain a regular expression?
        // Render the right controller;
        foreach(self::$Route as $key => $value)
        {
            if($this->ExtractVariable($value['Pattern']) == $this->pattern)
            {
                $this->lastRoute = $key;

                if(isset($value['Method']) and strtoupper($value['Method']) != getenv('REQUEST_METHOD'))
                    $this
                        ->SetErrorArgs('Access request denied', 'Router', '0')
                            ->ThrowException();

                if(isset($value['Requirements']))
                    $this->ValidateVariables($value['Requirements']);

                if(isset($value['Inject']))
                    $this->ObjectArguments = $value['Inject'];

                $controllerAction = explode(':', $value['Controller']);

                $this->CallAction($this->GetControllerNamespace($controllerAction), $controllerAction[2] . 'Action', $this->funcVariables);
            }
        }

        return false;
    }

    private function ValidateVariables($requirement)
    {
        foreach($requirement as $key => $pattern)
        {
            if(!preg_match($pattern, $this->funcVariables['{'.$key.'}']))
            {
                $this
                    ->SetErrorArgs('Route \''.$this->lastRoute.'\' expects variable \'$'.$key.'='.$this->funcVariables[$key].'\' to match \''.$pattern.'\' pattern', 'Route file', 'unknown')
                        ->ThrowError();
            }

        }
    }

    /**
     *
     * @param type $route
     * @return string extract variables in the url
     */
    private function ExtractVariable($route){

        if(strpos($route,'{'))
        {
            $pattern = '(\\{.*?\\})';

            $routeParams = explode('/', $route);

            $index = 0;

            foreach($routeParams as $param)
            {
                if(preg_match($pattern, $param))
                {
                    if(isset($this->params[$index]))
                    {
                        $param = $this->funcVariables[$param] = $this->params[$index];
                    }
                }

                $routeParams[$index] = $param;

                $index++;
            }

            return implode('/', $routeParams);
        }

        return $route;
    }

    private function GetControllerNamespace($controllerAction){

        if($controllerAction[0] == null)
            return '\\Application\\Controllers\\'.$controllerAction[1] . 'Controller';
        else
            return '\\Bundles\\'.$controllerAction[0].'\\Controllers\\' . $controllerAction[1] . 'Controller';
    }

    /**
     *
     * @param type $route
     * @param type $variable
     * @return string The complete route, to be used in templates.
     */
    public function SetRoute($route, array $variable = array()){

        $this->funcVariables = $variable;

        $this->GetRawRoute($route);

        if(!empty($variable))
            $this->ExtractAndReplaceVariable();

        $this->lastURL = getenv('SCRIPT_NAME') . $this->routePattern;

        return $this->lastURL;
    }

    /**
     *
     * @param type $route
     * @return string Gets a route and its details
     */
    protected function GetRoute($route)
    {
        return  $this->SetRoute($route);
    }

    /**
     *
     * @param type $route
     * @return string gets raw route without any modification.
     */
    private function GetRawRoute($route = null){

        if(!empty($route))
            $this->route = $route;

        if(\Get::Route($this->route))
        {
            $this->lastRoute = $this->routePattern = \Get::Route($this->route.'.Pattern');

            return $this->lastRoute;
        }

        $error = array(

            'Route' => $this->route,
            'Pattern' => $this->pattern,
            'Backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        );

        $this->ForwardToController('Error_Route_Not_Found', $error);
    }

    /**
     *
     * @param type $route
     * @return string Gets the controller for a route specified.
     */
    protected function GetController($route){

        if(!empty($route))
            $this->route = $route;

        if(isset(self::$Route[$this->route]))
        {
            $this->routePattern = $this->lastRoute = self::$Route[$this->route]['Pattern'];
            $controller = self::$Route[$this->route]['Controller'];

            unset($_SESSION['routeError']);

            return $controller;
        }

        $error = array(

            'routeName' => $this->route,
            'Pattern' => $this->pattern,
            'Backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)

        );

        $this->ForwardToController('Error_Route_Not_Found', $error);
    }

    /**
     *
     * @param type $route - The route to redirect to
     * @param type $urlQueryString - append this query string to the url to redirect to<br />
     * Redirects to a route.
     */
    public function ForwardTo($route, $urlQueryString = null){

        self::$LastRoute = $this->pattern;

        $this->GetCoreObject('Session')->Set('LastRoute', $route);

        $route = $this->GetRoute($route);

        session_write_close();

        header('Location: ' . $route . (!empty($urlQueryString) ? '?'.$urlQueryString : '' ));

        if(getenv('HTTP_HOST'))
            exit;
    }

    /**
     *
     * @param type $objectName
     * @param type $objectAction
     * @param type $variable<br />
     * Calls an action of a controller.
     */
    private function CallAction($objectName, $objectAction, array $variable = array())
    {
        if(!empty($variable))
            $this->funcVariables = $variable;

        if(!class_exists($objectName))
        {
            echo 'Error: '.$objectName;

            $error = array(

                'Class' => $objectName,
                'Controller' => $objectName  . ':' . str_replace('Action','',$objectAction),
                'Route' => $this->lastRoute,
                'Backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)

            );

            $this->ForwardToController ('Class_Not_Found', $error);
        }

        $controller = new $objectName();

        if(!method_exists($objectName, $objectAction)){

            $error = array(

                'Action' => $objectAction,
                'Class' => $objectName,
                'Controller' => $objectName  . ':' . str_replace('Action','',$objectAction),
                'Route' => $this->lastRoute,
                'Backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)

            );

            $this->ForwardToController('Action_Not_Found', $error);
        }

        if(\Get::Config('Cache.html.enabled'))
            Cache::CheckForCachedFile($this->GetPattern());

        if(count($this->ObjectArguments) != 0)
        {
            foreach($this->ObjectArguments as $variable => $object){

                $controller->$variable = new $object;
            }
        }

        if(sizeof($this->funcVariables) != 0)
        {
            call_user_func_array (array($controller, $objectAction) , $this->funcVariables);
        }
        else
        {
            call_user_func (array($controller, $objectAction));
        }

        unset($controller);

        die();
    }

    /**
     *
     * @param type $route
     * @param type $variable
     * <br />
     * Forward control from one controller to another without redirecting.
     */
    public function ForwardToController($route, array $variables = array())
    {
        $controller = $this->GetController($route);

        $controllerAction = explode(':', $controller);

        $this->CallAction($this->GetControllerNamespace($controllerAction), $controllerAction[2] . 'Action', $variables);
    }

    /**
     *
     * @return boolean - true on success, false on failure<br />
     * <br />Extracts multiple variables and sets them up for use in setRoute
     */
    private function ExtractAndReplaceVariable(){

        if(is_array($this->funcVariables))
        {
            foreach($this->funcVariables as $key => $value)
            {
                $this->routePattern = str_replace("\{$key\}", $value, $this->routePattern);
            }
        }

        return true;
    }

    /**
     *
     * @return boolean - true on success, false on failure<br />
     * <br />Checks wether the page landed on is an exception to session security or not.
     */
    protected function CheckExceptionRoutes(){

        $this->SetPattern();

        foreach(\Get::Config('Auth.Security.Bypass') as $pattern)
        {
            $pattern = '/'.str_replace('/', '\\/', $pattern).'/';

            if(preg_match($pattern, $this->pattern))
            {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * @return boolean<br>
     * Returns the last accessed page route
     */
    protected function LastAccessedPage(){

        if(isset(self::$LastRoute))
            return $this->GetRouteFromPattern(self::$LastRoute);

        return false;
    }

    /**
     *
     * @param type $pattern
     * @return route <br>
     * Returns a route for a given pattern
     */
    protected function GetRouteFromPattern($pattern = null){

        if(!empty($pattern))
            $this->pattern = $pattern;
        else
            $this->SetPattern ();

        foreach(self::$Route as $routeKey => $routes){

                if($routes['Pattern'] == $this->pattern){

                    $this->route = $routeKey;

                    $this->routePattern = $pattern;

                    unset($_SESSION['routeError']);

                    return $this->route;
                }
        }

        $error = array(

            'Pattern' => $this->pattern,
            'Backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)

        );

        $this->ForwardToController('Error_Route_Not_Found', $error);
    }

    /**
     *
     * @return Router
     * Returns the router object for further processing
     */
    public function GetRouter(){

        return $this->Router;
    }
}