<?php
return call_user_func(function(){

    $collection = new \Phalcon\Mvc\Micro\Collection();

    $collection
        // VERSION NUMBER SHOULD BE FIRST URL PARAMETER, ALWAYS
        ->setPrefix('/api/groups')
        // Must be a string in order to support lazy loading
        ->setHandler('\Api\Modules\Account\Controllers\GroupsController')
        ->setLazy(true);

    // Set Access-Control-Allow headers.
    $collection->options('/', 'optionsBase');
    $collection->options('/{id}', 'optionsOne');

    // First paramter is the route, which with the collection prefix here would be GET /example/
    // Second paramter is the function name of the Controller.
    $collection->get('/', 'index');
    // This is exactly the same execution as GET, but the Response has no body.
    $collection->head('/', 'index');

    // $id will be passed as a parameter to the Controller's specified function
    $collection->get('/{id:[a-zA-Z0-9]+}', 'view');
    $collection->head('/{id:[a-zA-Z0-9]+}', 'view');
    $collection->post('/', 'add');
    $collection->delete('/{id:[a-zA-Z0-9+}', 'delete');
    $collection->put('/{id:[a-zA-Z0-9]+}', 'edit');
    $collection->patch('/{id:[a-zA-Z0-9]+}', 'patch');

    return $collection;
});