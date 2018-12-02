# Onefileapi

A simple CRUD REST API based on file document store. No dependencies.


## Usage

```php
$router->get("/:resource", function($response, $body, $args){
	$response->success($response->all($args["resource"]), $args["resource"]);
});

$router->get("/:resource/:id", function($response, $body, $args){
	$response->success($response->one($args["resource"]), $args["id"], $args["resource"]);
});
```

Calling `GET '/products'` or `GET '/products/5c030fef1cd75'`

Results in

```javascript
{
	success: true,
	data: [
		{
			id: "5c030fef1cd75",
			name: "Product 1"
		}
	]
}
```



