# Onefileapi

A simple CRUD REST API based on file document store for PHP. No dependencies.

No database required, all data is stored in one single JSON file.

## Usage

```php
$router->get("/:resource", function($response, $body, $args){
	$response->success($response->all($args["resource"]), $args["resource"]);
});
```

Then `GET '/index.php/products'` results in

```javascript
{
	success: true,
	data: [
		{
			"id":"5c030fef1cd75",
			"name":"Product 1"
		},{
			"id":"5c043c2131fc5",
			"name":"Product 2"
		}
	]
}
```

```php
$router->get("/:resource/:id", function($response, $body, $args){
	$response->success($response->one($args["resource"]), $args["id"], $args["resource"]);
});
```

Then `GET '/index.php/products/5c030fef1cd75'` results in

```javascript
{
	success: true,
	data: [
		{
			"id":"5c030fef1cd75",
			"name":"Product 1"
		}
	]
}
```

See `index.php` for POST, PUT and DELETE examples.

## Permissions

Make sure to set correct permissions on both `index.php` and `store.json`.

## Disclaimer

Not intended for large datasets, nor for production webapps. Intended to quickly pull up a fully functional REST API.