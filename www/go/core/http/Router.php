<?php

namespace go\core\http;

use Exception as ExceptionAlias;
use go\core\exception\Forbidden;
use go\core\exception\NotFound;
use go\core\ErrorHandler;
use go\core\util\StringUtil;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

/**
 * Simple RESTful router
 *
 * It works with:
 *
 * script.php/edit/1/1
 *
 * Or access pretty with /script/edit/1/1 with an alias:
 *
 * Alias /script script.php
 *
 * Or with a rewrite rule:
 *
 * RewriteRule ^/script(.*)$ script.php [E=PATH_INFO:/$1]
 * 
 * @example
 * ```
 * $router = (new Router())
 *   ->addRoute('/edit\/([0-9]+)\/([0-9]+)/', 'GET', Wopi::class, 'edit')
 *   ->addRoute('/files\/([0-9]+)/', "LOCK", Wopi::class, 'lock')
 *   ->addRoute('/files\/([0-9]+)/', "GET_LOCK", Wopi::class, 'getLock')
 *   ->addRoute('/files\/([0-9]+)/', "REFRESH_LOCK", Wopi::class, 'refreshLock')
 *   ->addRoute('/files\/([0-9]+)/', "UNLOCK", Wopi::class, 'unlock')
 *   ->addRoute('/files\/([0-9]+)/', "PUT_RELATIVE", Wopi::class, 'putRelative')
 *   ->addRoute('/files\/([0-9]+)/', "RENAME_FILE", Wopi::class, 'renameFile')
 *   ->addRoute('/files\/([0-9]+)/', "DELETE", Wopi::class, 'delete')
 *   ->addRoute('/files\/([0-9]+)/', "PUT_USER_INFO", Wopi::class, 'putUserInfo')
 * 
 *   ->addRoute('/files\/([0-9]+)\/contents/', "GET", Wopi::class, 'GetFile')
 *   ->addRoute('/files\/([0-9]+)\/contents/', "POST", Wopi::class, 'PutFile')
 *   ->addRoute('/files\/([0-9]+)\/contents/', "PUT", Wopi::class, 'PutFile')
 * 
 *   ->run();
 *   ```
 */
class Router {

  private $routes = [];

  /**
   * Add a route
   * 
   * @param string $regex The regular expression to match with the router path. The path is relative to the script the router is created in.
   * @param string $httpMethod eg. GET, POST, PUT etc.
   * @param string $controller Controller class name
   * @param string $method Controller method This method may output directly or return data to pass to Response::get()->output(). (An array for json);
   * 
   * @return $this 
   */
  public function addRoute(string $regex, string $httpMethod, string $controller, string $method): Router
  {
    if(!isset($this->routes[$regex])) {
      $this->routes[$regex] = [];
    }
    $this->routes[$regex][$httpMethod] = ['controller' => $controller, 'method' => $method];

    return $this;
  }

	/**
	 * Run the controller method that matches with the route.
	 *
	 * @return void
	 * @throws NotFound
	 */
  public function run() {
    
    $route = $this->findRoute();
    if(!$route) {
      throw new NotFound();
    }

    try {
      $c = new $route['controller'];
      go()->debug("################   Router: ". $route['controller']."::".$route['method'] ." ################");
      go()->debug($route['params']);

      $response = call_user_func_array([$c, $route['method']], $route['params']);		
      
    } catch(Forbidden $e) {
    	if(!headers_sent()) {
		    Response::get()->setStatus(401, $e->getMessage());
	    }
	    $this->printException($e);
    } catch(NotFound $e) {
	    if(!headers_sent()) {
		    Response::get()->setStatus(404, $e->getMessage());
	    }
	    $this->printException($e);
    } catch(Exception $e) {
	    if(!headers_sent()) {
		    Response::get()->setStatus($e->code, $e->getMessage());
	    }
	    $this->printException($e);;
    } catch(Throwable $e) {
	    if(!headers_sent()) {
		    Response::get()->setStatus(500, StringUtil::normalizeCrlf($e->getMessage(), " - "));
			}

			$this->printException($e);

    }

	  if(isset($response) && $response instanceof \GuzzleHttp\Psr7\Response) {
		  $this->emitPsr7Response($response);
	  } else{
		  Response::get()->output($response ?? null);
	  }

  }

	private function printException(Throwable $e) {

		ErrorHandler::logException($e);

		require(go()->getEnvironment()->getInstallFolder() . '/views/Extjs3/themes/Paper/pageHeader.php');

		echo '<h1>' . get_class($e) .'</h1><p>'.$e->getMessage();

		if(go()->getDebugger()->enabled) {

			echo " in file " . $e->getFile() .' at line '. $e->getLine();

			echo '</p>';
			echo '<pre>';
			echo $e->getTraceAsString();
			echo '</pre>';
		} else{
			echo '</p>';
		}

		require(go()->getEnvironment()->getInstallFolder() . '/views/Extjs3/themes/Paper/pageFooter.php');

	}

  private function emitPsr7Response(ResponseInterface $response){
	  if (headers_sent()) {
		  throw new RuntimeException('Headers were already sent. The response could not be emitted!');
	  }

// Step 1: Send the "status line".
	  $statusLine = sprintf('HTTP/%s %s %s'
		  , $response->getProtocolVersion()
		  , $response->getStatusCode()
		  , $response->getReasonPhrase()
	  );
	  header($statusLine, TRUE); /* The header replaces a previous similar header. */

// Step 2: Send the response headers from the headers list.
	  foreach ($response->getHeaders() as $name => $values) {
		  $responseHeader = sprintf('%s: %s'
			  , $name
			  , $response->getHeaderLine($name)
		  );
		  header($responseHeader, FALSE); /* The header doesn't replace a previous similar header. */
	  }

// Step 3: Output the message body.
	  echo $response->getBody();
  }

  private function findRoute()
  {
    $path = ltrim(Request::get()->getPath(), '/');
    
    $method = Request::get()->getHeader('X-WOPI-Override');
    if(!$method) {
      $method = Request::get()->getMethod();
    }

    foreach ($this->routes as $regex => $methods) {
			if(isset($methods[$method]) && preg_match($regex, $path, $params)) {
        array_shift($params); //shift full match
        return array_merge($methods[$method], ['params' => $params]);
      }
    }

    go()->debug("ROUTE NOT FOUND: " . $path . '['.$method.']');

    return false;
  }

}