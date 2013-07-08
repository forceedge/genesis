<?php

namespace Application\Controllers;



use \Application\Core\Application;

use Application\Interfaces\Controllers\Error;


class ErrorsController extends Application implements Error{

    public function RouteNotFoundAction($route, $pattern, $backtrace){

        $params = null;

        $this->prex($route);

        $params['PageTitle'] = 'Route Not Found!';

        $params['Error'] = array(

            'Route' => $route,
            'Pattern' => $pattern,
            'Backtrace' => $backtrace
        );

        $this ->GetResponseManager() ->SetNotFound();

        $this->Render(':Errors/RouteNotFound.html.php', $params);
    }

    public function ActionNotFoundAction($action, $class, $controller, $route, $backtrace){

        $params = null;

        $params['PageTitle'] = 'Action Not Found!';

        $params['Error'] = array(

            'Action' => $action,
            'Class' => $class,
            'Controller' => $controller,
            'Route' => $route,
            'Backtrace' => $backtrace
        );

        $this ->GetResponseManager() ->SetStatus(400);

        $this->Render(':Errors/ActionNotFound.html.php', $params);
    }

    public function ClassNotFoundAction($action, $class, $controller, $route, $backtrace){

        $params = null;

        $params['PageTitle'] = 'Class Not Found!';

        $params['Error'] = array(

            'Action' => $action,
            'Class' => $class,
            'Controller' => $controller,
            'Route' => $route,
            'Backtrace' => $backtrace
        );

        $this ->GetResponseManager() ->SetStatus(400);

        $this->Render(':Errors/ClassNotFound.html.php', $params);
    }

    public function NotFoundError404Action(){

        $this->Render(':Errors/error404.html.php', array('pageTitle' => 'Error 404: Page not found'));
    }

    public function ServerError500Action(){

        $this->Render(':Errors/error500.html.php', array('pageTitle' => 'Error 500: Internal server error'));
    }
}