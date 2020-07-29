<?php

namespace Arter;

class Route
{
    /**
     * Route URI from request.
     *
     * @var string
     */
    protected $routeUri;

    /**
     * HTTP Method
     *
     * @var string
     */
    protected $httpMethod;

    /**
     * Method of the resource
     * controller this should call. 
     *
     * @var string
     */
    protected $method = 'index';

    /**
     * URI parameters
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Resource Controller
     *
     * @var string
     */
    protected $controller;

    /**
     * Resource for this router.
     *
     * @var string
     */
    protected $resourceInput;

    protected $resource;

    public function __construct(string $resourceInput)
    {
        $this->resourceInput     = $resourceInput;
        $this->routeUri     = $_SERVER['REQUEST_URI'];
        $this->httpMethod   = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Method for setting resource in routes file.
     *
     * @param string $resourceInput
     * @return void|Arter\Route
     */
    public static function resource(string $resourceInput)
    {
        if( self::shouldHandleResource($resourceInput) )
        {
            return (new Route( $resourceInput ))
                ->resolveResource()
                ->resolveResourceController()
                ->resolveParameters()
                ->resolveMethod()
                ->render();
        }
    }

    /**
     * Determine if this instance should handle the request.
     *
     * @param string $resourceInput
     * @return boolean
     */
    protected static function shouldHandleResource(string $resourceInput) : bool
    {
        $resource = explode('.', $resourceInput)[0];
        return ( strpos($_SERVER['REQUEST_URI'], '/' . $resource . '/') === 0
            || strpos($_SERVER['REQUEST_URI'], '/' . $resource) === 0);
    }
    
    /**
     * Resolve the resource from the resource input.
     *
     * @return Arter\Route
     */
    protected function resolveResource()
    {
        $this->resource = explode('.', $this->resourceInput)[0];
        return $this;
    }

    /**
     * Resolves the appropriate controller
     * based on the resourceInput
     *
     * @return Arter\Route
     */
    protected function resolveResourceController() : Route
    {
        if ( strpos($this->resourceInput, '.') > 0 ) {
            $this->controller = str_replace('.', ' ', $this->resourceInput);
            $this->controller = str_replace(' ', '', ucwords($this->controller));
        } else {
            $this->controller = ucfirst( strtolower($this->resourceInput) );
        }
        $this->controller = $this->controller . 'Controller';
        return $this;
    }

    /**
     * Resolve the URI paramaters to inject
     * into the method.
     *
     * @return Arter\Route
     */
    protected function resolveParameters() : Route
    {
        $parameters = explode('/', $this->routeUri);

        foreach( $parameters as $parameter)
        {
            if($parameter !== '' 
                && $parameter !== $this->controller
                && $parameter !== $this->resource
                && strpos(strtolower($this->controller), $parameter) == false )
            {
                $this->parameters[] = $parameter;
            }
        }
        return $this;
    }

    /**
     * Resolve the method of the controller
     * based on the HTTP request and parameters
     *
     * @return Arter\Route
     */
    protected function resolveMethod() : Route
    {
        switch ($this->httpMethod) {

            case 'GET':
                if(count($this->parameters) > 0)
                {
                    $this->method = 'show';
                }
                break;

            case 'POST':
                $this->method = 'store';
                break; 

            case 'PUT':
                $this->method = 'update';
                break;

            case 'PUT':
                $this->method = 'update';
                break;

            case 'DELETE':
                $this->method = 'destroy';
                break;
        }

        return $this;
    }

    /**
     * Render the controller method.
     *
     * @return mixed
     */
    protected function render()
    {
        $controller = '\\' . __NAMESPACE__ . '\Controllers\\' . $this->controller;
        return call_user_func([ new $controller, $this->method], ... $this->parameters);
    }

}