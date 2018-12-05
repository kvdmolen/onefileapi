<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");


$router = new Router;


$router->get("/:resource", function($response, $body, $args){
	$data = $response->all($args["resource"]);
	$response->success($data, $args["resource"]);
});

$router->get("/:resource/:id", function($response, $body, $args){
	$data = $response->one($args["resource"], $args["id"]);
	$response->success($data, $args["resource"]);
});

$router->post("/:resource", function($response, $body, $args){
	$data = $response->add($args["resource"], $body)
	$response->success($data, $args["resource"]);
});

$router->put("/:resource/:id", function($response, $body, $args){
	$data = $response->update($args["resource"], $args["id"], $body)
	$response->success($data, $args["resource"]);
});

$router->delete("/:resource/:id", function($response, $body, $args){
	$success = $response->remove($args["resource"], $args["id"]);
	$response->success(null);
});


$router->run();


class Store {

	private $store;

	public function all($resource){
		$this->getstore();
		if(isset($this->store->{$resource})){
			return $this->store->{$resource};
		}else{
			return array();
		}
	}

	public function one($resource, $id){
		$this->getstore();
		if(isset($this->store->{$resource})){
			foreach($this->store->{$resource} as $item) {
			    if ($id == $item->{"id"}) {
			        return $item;
			        break;
			    }
			}
			$this->error("Item not found");
		}else{
			$this->error("Item not found");
		}
	}

	public function add($resource, $newitem){
		$this->getstore();
		$newitem["id"] = uniqid();
		if(!isset($this->store->{$resource})){
			$this->store->{$resource} = array();
		}
		array_push($this->store->{$resource}, $newitem);
		$this->savestore();
		return $newitem;
	}

	public function update($resource, $id, $newitem){
		$this->getstore();
		if(isset($this->store->{$resource})){
			foreach($this->store->{$resource} as $index => $item) {
			    if ($id == $item->{"id"}) {
			        //foreach($item as $key => $value){
			        //	if($key != "id" && isset($newitem[$key])){
			        //		$this->store->{$resource}[$index]->{$key} = $newitem[$key];
			        // 	}
			    	//}
				$this->store->{$resource}[$index] = $newitem;
				$this->savestore();
				return $item;
			        break;
			    }
			}
			$this->error("Item not found");
		}else{
			$this->error("Item not found");
		}
	}

	public function remove($resource, $id){
		$this->getstore();
		if(isset($this->store->{$resource})){
			foreach($this->store->{$resource} as $index => $item) {
			    if ($id == $item->{"id"}) {
			    	unset($this->store->{$resource}[$index]);
			    	$this->savestore();
			        return true;
			        break;
			    }
			}
			$this->error("Item not found");
		}else{
			$this->error("Item not found");
		}
	}

	private function getstore(){
		$this->store = json_decode(file_get_contents("store.json"));
	}

	private function savestore(){
		$fp = fopen("store.json", 'w');
		fwrite($fp, json_encode($this->store));
		fclose($fp);
		return true;
	}

}


Class Router extends Store {

	private $routes = array();

	private function addroute($method, $path, $callback){
		$route = array(
			"method" => $method,
			"path" => $path,
			"callback" => $callback
		);
		array_push($this->routes, $route);
	}

	public function get($path, $callback){
		$this->addroute("GET", $path, $callback);
	}

	public function post($path, $callback){
		$this->addroute("POST", $path, $callback);
	}

	public function put($path, $callback){
		$this->addroute("PUT", $path, $callback);
	}

	public function delete($path, $callback){
		$this->addroute("DELETE", $path, $callback);
	}

	public function run(){
		$method = $_SERVER['REQUEST_METHOD'];
		$path   = explode("/", isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '');
		$body   = json_decode(file_get_contents('php://input'), true);
		$args   = array();
		$found  = false;

		if($method == "OPTIONS"){
			$this->success(null);
		}else{
			foreach ($this->routes as $route){

				if($method == $route["method"]){
					$routepath = explode("/", $route["path"]);

					if(count($routepath) == count($path)){
						$match = true;
						$index = 0;

						foreach($routepath as $p){
							if($p != ""){
								if(substr($p, 0, 1) == ":"){
									$args[substr($p, 1)] = $path[$index];
								}else{
									if($p != $path[$index]){
										$match = false;
									}
								}
							}
							$index++;
						}
						if($match){
							$route["callback"]($this, $body, $args);
							$found = true;
							break;
						}
					}
				}
			}
			if(!$found){
				$this->error("Bad route");
			}
		}
	}

	public function success($data, $resource = null){
		if($resource){
			$return = array();
			$return[$resource] = $data;
		}else{
			$return = $data;
		}
		$response = array(
			"success" => true,
			"data" => $data);
		$this->output($response);
	}

	public function error($message, $code = 400){
		$response = array(
			"success" => false,
			"message" => $message);
		$this->output($response, $code);
	}

	private function output($response, $code = 200){
		http_response_code($code);
		echo json_encode($response);
	}
}

?>
