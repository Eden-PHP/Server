<?php //-->
/**
 * This file is part of the Eden PHP Library.
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Eden\Server;

/**
 * Express style server class implementation
 *
 * @vendor   Eden
 * @package  Server
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Index extends Base
{
    /**
     * @const string BACK The back keyword for redirect
     */
    const BACK = '<back>';

    /**
     * @const string NOT_FOUND 404 Error template
     */
    const NOT_FOUND = 'Not Found.';
       
    /**
     * @const string UNEXPECTED_GLOBAL Error template
     */
    const UNEXPECTED_GLOBAL = 'Unexpected end before routing. Please check global middlewares.';
       
    /**
     * @const string RESPONSE_ERROR_TYPE Default Exception type
     */
    const RESPONSE_ERROR_TYPE = 'RESPONSE';
       
    /**
     * @var array $globalMiddleware A list of global middleware callbacks
     */
    protected $globalMiddleware = array();
       
    /**
     * @var array $routeMiddleware A list of route middleware callbacks
     */
    protected $routeMiddleware = array();
       
    /**
     * @var array $errorMiddleware A list of error middleware callbacks
     */
    protected $errorMiddleware = array();
       
    /**
     * @var bool $successful If we were able to process all middleware
     */
    protected $successful = false;
       
    /**
     * @var Eden\Server\Index $parentServer Allows for parent/child
     */
    protected $parentServer = null;
    
    /**
     * We might as well...
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->render();
        } catch (Exception $e) {
        }
        
        return '';
    }
    
    /**
     * Adds global middleware
     *
     * @param function $callback The middleware handler
     *
     * @return Eden\Server\Index
     */
    public function add($callback)
    {
        //argument 1 should be callable
        Argument::i()->test(1, 'callable');
        
        $this->globalMiddleware[] = $callback;
        return $this;
    }
    
    /**
     * Adds routing middleware for all methods
     *
     * @param string   $path     The route path
     * @param function $callback The middleware handler
     *
     * @return Eden\Server\Index
     */
    public function all($path, $callback)
    {
        Argument::i()
            //argument 1 should be a string
            ->test(1, 'string')
            //argument 2 should be callable
            ->test(2, 'callable');
        
        return $this->route('all', $path, $callback);
    }
    
    /**
     * Returns a new instance with the same configuration
     *
     * @return Eden\Server\Index
     */
    public function child()
    {
        $child = self::i()->setParent($this);
        
        foreach ($this->globalMiddleware as $callback) {
            $child->add($callback);
        }
        
        foreach ($this->routeMiddleware as $method => $route) {
            $child->route($method, $route[0], $route[1]);
        }
        
        foreach ($this->errorMiddleware as $callback) {
            $child->error($callback);
        }
        
        return $child;
    }
    
    /**
     * Adds routing middleware for delete method
     *
     * @param string   $path     The route path
     * @param function $callback The middleware handler
     *
     * @return Eden\Server\Index
     */
    public function delete($path, $callback)
    {
        Argument::i()
            //argument 1 should be a string
            ->test(1, 'string')
            //argument 2 should be callable
            ->test(2, 'callable');
        
        return $this->route('delete', $path, $callback);
    }
    
    /**
     * Adds error middleware
     *
     * @param function $callback The middleware handler
     *
     * @return Eden\Server\Index
     */
    public function error($callback)
    {
        //argument 1 should be callable
        Argument::i()->test(1, 'callable');
        $this->errorMiddleware[] = $callback;
        return $this;
    }
    
    /**
     * Adds routing middleware for get method
     *
     * @param string   $path     The route path
     * @param function $callback The middleware handler
     *
     * @return Eden\Server\Index
     */
    public function get($path, $callback)
    {
        Argument::i()
            //argument 1 should be a string
            ->test(1, 'string')
            //argument 2 should be callable
            ->test(2, 'callable');
        
        return $this->route('get', $path, $callback);
    }
    
    /**
     * Returns a request object
     *
     * @return Eden\Registry\Index
     */
    public function getRequest()
    {
        $path = $_SERVER['REQUEST_URI'];
    
        //remove ? url queries
        if (strpos($path, '?') !== false) {
            list($path, $tmp) = explode('?', $path, 2);
        }

        $array = explode('/', $path);
        
        $path = array(
            'string' => $path,
            'array' => $array);
            
        //set the request
        return $this('registry')
            ->set('method', $_SERVER['REQUEST_METHOD'])
            ->set('query', $_SERVER['QUERY_STRING'])
            ->set('body', file_get_contents('php://input'))
            ->set('server', $_SERVER)
            ->set('cookie', $_COOKIE)
            ->set('get', $_GET)
            ->set('post', $_POST)
            ->set('files', $_FILES)
            ->set('path', $path);
    }
    
    /**
     * Returns a response object
     *
     * @return Eden\Registry\Index
     */
    public function getResponse()
    {
        return $this('registry')
            ->set(
                'headers',
                'Content-Type',
                'text/html; charset=utf-8'
            )
            ->set('headers', 'Status', '200 OK');
        ;
    }
    
    /**
     * Returns the parent server
     *
     * @return Eden\Server\Index
     */
    public function getParent()
    {
        return $this->parentServer;
    }
    
    /**
     * Evaluates the response
     * in order to determine the
     * output. Then of course,
     * output it
     *
     * @param Eden\Registry\Index $response The response object to evaluate
     *
     * @return Eden\Server\Index
     */
    public function output($response)
    {
        //argument 1 should be an array or ArrayAccess
        Argument::i()->test(1, 'array', 'ArrayAccess');
        
        $code = $response['code'];
        $headers = $response['headers'];
        $body = $response['body'];
        
        if (is_int($code)) {
            http_response_code($code);
        }
        
        if (!isset($headers['Content-Type']) && !isset($headers['content-type'])) {
            $headers['Content-Type'] = 'text/html; charset=utf-8';
        }
        
        if (!$body) {
            $body = '';
        }
        
        //if it's not scalar
        if (!is_scalar($body)) {
            $body = json_encode($body, JSON_PRETTY_PRINT);
        }
        
        foreach ($headers as $name => $value) {
            if (!$value) {
                header($name);
                continue;
            }
            
            header($name.':'.$value);
        }

        $check = new \StdClass();
        $check->stop = false;

        $this->trigger('server-output', $response, $body, $check);
        
        if (!$check->stop) {
            echo (string) $body;
            $this->successful = true;
        }
        
        return $this;
    }
    
    /**
     * Adds routing middleware for post method
     *
     * @param string   $path     The route path
     * @param function $callback The middleware handler
     *
     * @return Eden\Server\Index
     */
    public function post($path, $callback)
    {
        Argument::i()
            //argument 1 should be a string
            ->test(1, 'string')
            //argument 2 should be callable
            ->test(2, 'callable');
        
        return $this->route('post', $path, $callback);
    }
    
    /**
     * Starts to process the request
     *
     * @return array with request and response inside
     */
    public function process()
    {
        //formulate the request and response
        $request = $this->getRequest();
        $response = $this->getResponse();
        
        //register shutdown
        register_shutdown_function(function ($server) {
            $server->trigger('server-end');
        }, $this);
        
        //if it's not a child
        if (!($this->parentServer instanceof Index)) {
            //handle errors in case
            $this->handleErrors($request, $response);
        }
        
        //if we are Good, route
        if ($this->processGlobal($request, $response)) {
            //if no routing on this
            if (!$this->processRoutes($request, $response)) {
                $response->set('code', 404);
                
                //throw an exception
                Exception::i()
                    ->setMessage(self::NOT_FOUND)
                    ->setType(self::RESPONSE_ERROR_TYPE)
                    ->trigger();
            }
        }
        
        //do we have a body ?
        $body = $response->get('body');
        
        if ($body === null
        || !is_scalar($body)
        || !strlen((string) $body)) {
            $response->set('code', 404);
            
            //throw an exception
            Exception::i()
                ->setMessage(self::NOT_FOUND)
                ->setType(self::RESPONSE_ERROR_TYPE)
                ->trigger();
        }
        
        return array($request, $response);
    }
    
    /**
     * Adds routing middleware for put method
     *
     * @param string   $path     The route path
     * @param function $callback The middleware handler
     *
     * @return Eden\Server\Index
     */
    public function put($path, $callback)
    {
        Argument::i()
            //argument 1 should be a string
            ->test(1, 'string')
            //argument 2 should be callable
            ->test(2, 'callable');
        
        return $this->route('put', $path, $callback);
    }
    
    /**
     * Browser redirect
     *
     * @param string $path Where to redirect to
     *
     * @return mixed
     */
    public function redirect($path)
    {
        if ($path === self::BACK) {
            $path = 'javascript://history.go(-1)';
            if (isset($_SERVER['HTTP_REFERER'])) {
                $path = $_SERVER['HTTP_REFERER'];
            }
        }

        $check = new \StdClass();
        $check->stop = false;
        
        $this->trigger('redirect', $path, $check);
        
        if (!$check->stop) {
            header('Location: ' . $path);
            
            //to many unknowns happen when redirecting while
            //executing... it's best to exit now
            exit;
        }
        
        return $this;
    }
    
    /**
     * Process and output
     *
     * @return Eden\Server\Index
     */
    public function render()
    {
        list($request, $response) = $this->process();
        return $this->output($response);
    }
    
    /**
     * Adds routing middleware
     *
     * @param string   $method   The request method
     * @param string   $path     The route path
     * @param function $callback The middleware handler
     *
     * @return Eden\Server\Index
     */
    public function route($method, $path, $callback)
    {
        Argument::i()
            //argument 1 should be a string
            ->test(1, 'string')
            //argument 2 should be a string
            ->test(2, 'string')
            //argument 3 should be callable
            ->test(3, 'callable');
        
        $method = strtoupper($method);
        
        if ($method === 'ALL') {
            return $this
                ->route('get', $path, $callback)
                ->route('post', $path, $callback)
                ->route('put', $path, $callback)
                ->route('delete', $path, $callback);
        }
        
        $this->routeMiddleware[$method][] = array($path, $callback);
        
        return $this;
    }
    
    /**
     * Returns if we were able to output
     * something
     *
     * @param Eden\Server\Index $parent The parent server
     *
     * @return Eden\Server\Index
     */
    public function setParent(Index $parent)
    {
        $this->parentServer = $parent;
        
        return $this;
    }
    
    /**
     * Returns if we were able to output
     * something
     *
     * @return bool
     */
    public function success()
    {
        return $this->successful;
    }
    
    /**
     * Returns a dynamic list of variables
     * based on the given pattern and path
     *
     * @param array $matches Matches usually from a preg method
     *
     * @return array
     */
    protected function getVariables($matches)
    {
        $variables = array();
        
        if (!is_array($matches)) {
            return $variables;
        }
        
        array_shift($matches);
        
        foreach ($matches as $path) {
            $variables = array_merge($variables, explode('/', $path));
        }
        
        foreach ($variables as $i => $variable) {
            if (!$variable) {
                unset($variables[$i]);
            }
        }
        
        return array_values($variables);
    }
    
    /**
     * Listen and handle errors
     *
     * @param Eden\Registry\Index $request  The request object
     * @param Eden\Registry\Index $response The response object
     *
     * @return void|Eden\Server\Index
     */
    protected function handleErrors($request, $response)
    {
        $errorHandler = function () use ($request, $response) {
            //there are alot of arguments
            $args = func_get_args();
            
            //remove the body
            $response->remove('body');
            
            //set the status
            $code = $response->get('code');
            
            if (!$code) {
                $response->set('code', 500);
            }
            
            array_unshift($args, $request, $response);
            
            //parse through middleware
            foreach ($this->errorMiddleware as $callback) {
                //bind callback
                $callback = $callback->bindTo($this, get_class($this));
            
                if (call_user_func_array($callback, $args) === false) {
                    break;
                }
            }
            
            //do we have a body ?
            if ((string) $response->get('body')) {
                $this->output($response);
                exit;
            }
            
            //there maybe another error/exception handler
            //let it be called since we could not do anything
        };
        
        $errorHandler = $errorHandler->bindTo($this, get_class($this));
        
        //global error/exception event
        $this
            ->on('error', $errorHandler, true)
            ->on('exception', $errorHandler, true);
        
        return $this;
    }
    
    /**
     * Process global middleware
     *
     * @param Eden\Registry\Index $request  The request object
     * @param Eden\Registry\Index $response The response object
     *
     * @return bool
     */
    protected function processGlobal($request, $response)
    {
        $args = array($request, $response);
        foreach ($this->globalMiddleware as $callback) {
            //bind callback
            $callback = $callback->bindTo($this, get_class($this));
            
            if (call_user_func_array($callback, $args) === false) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Process route middleware
     *
     * @param Eden\Registry\Index $request  The request object
     * @param Eden\Registry\Index $response The response object
     *
     * @return bool
     */
    protected function processRoutes($request, $response)
    {
        $method = strtoupper($request->get('method'));
        
        //if no routing on this
        if (!isset($this->routeMiddleware[$method])
        || !is_array($this->routeMiddleware[$method])) {
            return false;
        }
        
        $args = array($request, $response);
        $path = $request->get('path', 'string');
        
        //determine the route
        foreach ($this->routeMiddleware[$method] as $route) {
            $pattern = $route[0];
            $callback = $route[1];
            
            $regex = str_replace('**', '!!', $pattern);
            $regex = str_replace('*', '([^/]*)', $regex);
            $regex = str_replace('!!', '(.*)', $regex);
            
            $regex = '#^'.$regex.'(.*)#';
            if (!preg_match($regex, $path, $matches)) {
                continue;
            }
            
            //get dynamic variables
            $variables = $this->getVariables($matches);
            
            //and stuff it in the request object
            $request->set('variables', $variables);
            
            //bind callback
            $callback = $callback->bindTo($this, get_class($this));
            
            //call the callback
            //and keep going unles they say explicitly to stop
            if (call_user_func_array($callback, $args) === false) {
                break;
            }
        }
        
        return true;
    }
}
