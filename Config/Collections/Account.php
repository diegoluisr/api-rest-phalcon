<?php

/**
 * Collections let us define groups of routes that will all use the same controller.
 * We can also set the handler to be lazy loaded.  Collections can share a common prefix.
 * @var $collection
 */

// This is an Immeidately Invoked Function in php.  The return value of the
// anonymous function will be returned to any file that "includes" it.
// e.g. $collection = include('example.php');
return call_user_func(function(){

    $collection = new \Phalcon\Mvc\Micro\Collection();

    $collection
        ->setPrefix('/api/account')
        ->setHandler('\Api\Modules\Account\Controllers\AccountController')
        ->setLazy(true);

    $collection->options('/', 'optionsBase');
    $collection->options('/me', 'optionsOne');

    $collection->get('/me', 'me');
    $collection->post('/login', 'login');
    $collection->delete('/logout', 'logout');

    return $collection;
});
