![logo](http://eden.openovate.com/assets/images/cloud-social.png) Eden Server
====
[![Build Status](https://api.travis-ci.org/Eden-PHP/Server.svg)](https://travis-ci.org/Eden-PHP/Server)
====

 - [Install](#install)
 - [Introduction](#intro)
 - [API](#api)
    - [add](#add)
    - [all](#all)
    - [child](#child)
    - [delete](#delete)
    - [error](#error)
    - [get](#get)
    - [getRequest](#getRequest)
    - [getResponse](#getResponse)
    - [getParent](#getParent)
    - [output](#output)
    - [post](#post)
    - [process](#process)
    - [put](#put)
    - [redirect](#redirect)
    - [render](#render)
    - [route](#route)
    - [setParent](#setParent)
    - [success](#success)
 - [Contributing](#contributing)

====

<a name="install"></a>
## Install

`composer install eden/server`

====

<a name="intro"></a>
## Introduction

Eden Server is an Express style web service. It allows all kinds of webframeworks to be developed because heavily relies on external middleware. A quick example of this usage is found below.

```
eden('server')
	->route('*', function($request, $response) {
		$response->set('body', 'Hello World!');
	})
	->render();
```

There are 3 kinds of middleware it accepts and are called during specific times during the response process.

### Global Middleware

Global Middleware are called before any response is generated. Some examples of global middleware can be

 - Security - like CSRF checking, Captcha, CORS, HTPASSWD, etc.
 - API - like Facebook Login, Paypal, etc.
 - Utility - like geoip, localization, internationalization, etc.
 
You can simply add global middleware in this fashion.

```
eden('server')->add(function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

### Route Middleware

Route Middleware are called when the request is formed right after the Global Middleware. To make a route available you will need the request method, desired route path and the callback handler.

You can simply add route middleware in this fashion.

```
eden('server')->route('POST', '/some/path/*/foo', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

Routes can accept dynamic variables denoted as `*`, described in the example route `/some/path/*/foo`. These variables are accessable by calling `$id = $request->get('variables', 0);` in your route handler callback. If your route is using a common request method like `POST`, `GET`, `PUT`, `DELETE`, there are wrapper methods recommended to use instead.


```
eden('server')->post('/some/path/*/foo', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

```
eden('server')->get('/some/path/*/foo', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

```
eden('server')->put('/some/path/*/foo', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

```
eden('server')->delete('/some/path/*/foo', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

For all the above methods you can also set the response by returning the string like below.

```
eden('server')->get('/some/path/*/foo', function($request, $response) {
	return 'Hello World';
});
```

### Error Middleware

Error Middleware are called when either the Global or the Route Middleware throws an Exception. You can simply add an error middleware in this fashion.

```
eden('server')->error(function(
		$request, 
		$response, 
		$type,
		$level,
		$class,
		$file,
		$line,
		$message
	) {
		$response->set('body', 'Hello World!');
	});
```

====

<a name="api"></a>
## API

==== 

<a name="add"></a>

### add

Adds global middleware 

#### Usage

```
eden('server')->add(function $callback);
```

#### Parameters

 - `function $callback` - The middleware handler

Returns `Eden\Server\Index`

#### Example

```
eden('server')->add(function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

==== 

<a name="all"></a>

### all

Adds routing middleware for all methods 

#### Usage

```
eden('server')->all(string $path, function $callback);
```

#### Parameters

 - `string $path` - The route path
 - `function $callback` - The middleware handler

Returns `Eden\Server\Index`

#### Example

```
eden('server')->all('/some/*/path', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

==== 

<a name="child"></a>

### child

Returns a new instance with the same configuration 

#### Usage

```
eden('server')->child();
```

#### Parameters

Returns `Eden\Server\Index`

==== 

<a name="delete"></a>

### delete

Adds routing middleware for delete method 

#### Usage

```
eden('server')->delete(string $path, function $callback);
```

#### Parameters

 - `string $path` - The route path
 - `function $callback` - The middleware handler

Returns `Eden\Server\Index`

#### Example

```
eden('server')->delete('/some/*/path', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

==== 

<a name="error"></a>

### error

Adds error middleware 

#### Usage

```
eden('server')->error(function $callback);
```

#### Parameters

 - `function $callback` - The middleware handler

Returns `Eden\Server\Index`

#### Example

```
eden('server')->error('/some/*/path', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

==== 

<a name="get"></a>

### get

Adds routing middleware for get method 

#### Usage

```
eden('server')->get(string $path, function $callback);
```

#### Parameters

 - `string $path` - The route path
 - `function $callback` - The middleware handler

Returns `Eden\Server\Index`

#### Example

```
eden('server')->get('/some/*/path', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

==== 

<a name="getRequest"></a>

### getRequest

Returns a request object 

#### Usage

```
eden('server')->getRequest();
```

#### Parameters

Returns `Eden\Registry\Index`

==== 

<a name="getResponse"></a>

### getResponse

Returns a response object 

#### Usage

```
eden('server')->getResponse();
```

#### Parameters

Returns `Eden\Registry\Index`

==== 

<a name="getParent"></a>

### getParent

Returns the parent server 

#### Usage

```
eden('server')->getParent();
```

#### Parameters

Returns `Eden\Server\Index`

==== 

<a name="output"></a>

### output

Evaluates the response in order to determine the output. Then of course, output it 

#### Usage

```
eden('server')->output(Eden\Registry\Index $response);
```

#### Parameters

 - `Eden\Registry\Index $response` - The response object to evaluate

Returns `Eden\Server\Index`

#### Example

```
eden('server')->output($response);
```

==== 

<a name="post"></a>

### post

Adds routing middleware for post method 

#### Usage

```
eden('server')->post(string $path, function $callback);
```

#### Parameters

 - `string $path` - The route path
 - `function $callback` - The middleware handler

Returns `Eden\Server\Index`

#### Example

```
eden('server')->post('/some/*/path', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

==== 

<a name="process"></a>

### process

Starts to process the request 

#### Usage

```
eden('server')->process();
```

#### Parameters

Returns `array` - with request and response inside

==== 

<a name="put"></a>

### put

Adds routing middleware for put method 

#### Usage

```
eden('server')->put(string $path, function $callback);
```

#### Parameters

 - `string $path` - The route path
 - `function $callback` - The middleware handler

Returns `Eden\Server\Index`

#### Example

```
eden('server')->put('/some/*/path', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

==== 

<a name="redirect"></a>

### redirect

Browser redirect 

#### Usage

```
eden('server')->redirect(string $path);
```

#### Parameters

 - `string $path` - Where to redirect to

Returns `mixed`

#### Example

```
eden('server')->redirect();
```

==== 

<a name="render"></a>

### render

Process and output 

#### Usage

```
eden('server')->render();
```

#### Parameters

Returns `Eden\Server\Index`

==== 

<a name="route"></a>

### route

Adds routing middleware 

#### Usage

```
eden('server')->route(string $method, string $path, function $callback);
```

#### Parameters

 - `string $method` - The request method
 - `string $path` - The route path
 - `function $callback` - The middleware handler

Returns `Eden\Server\Index`

#### Example

```
eden('server')->route('POST', '/some/*/path', function($request, $response) {
	$response->set('body', 'Hello World!');
});
```

==== 

<a name="setParent"></a>

### setParent

Returns if we were able to output something 

#### Usage

```
eden('server')->setParent(Eden\Server\Index $parent);
```

#### Parameters

 - `Eden\Server\Index $parent` - The parent server

Returns `Eden\Server\Index`

#### Example

```
eden('server')->setParent($parent);
```

==== 

<a name="success"></a>

### success

Returns if we were able to output something 

#### Usage

```
eden('server')->success();
```

#### Parameters

Returns `bool`

==== 

<a name="contributing"></a>
#Contributing to Eden

Contributions to *Eden* are following the Github work flow. Please read up before contributing.

##Setting up your machine with the Eden repository and your fork

1. Fork the repository
2. Fire up your local terminal create a new branch from the `v4` branch of your 
fork with a branch name describing what your changes are. 
 Possible branch name types:
    - bugfix
    - feature
    - improvement
3. Make your changes. Always make sure to sign-off (-s) on all commits made (git commit -s -m "Commit message")

##Making pull requests

1. Please ensure to run `phpunit` before making a pull request.
2. Push your code to your remote forked version.
3. Go back to your forked version on GitHub and submit a pull request.
4. An Eden developer will review your code and merge it in when it has been classified as suitable.