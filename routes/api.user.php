<?php

Route::group([
    'middleware' => [
        'api.user',
        'api.currency',
        'api.detect_hub',
        'api.customer_groups',
        'api.channels',
        'api.locale',
        'api.tax',
    ],
    'prefix' => 'api/'.config('app.api_version', 'v1'),
    'namespace' => 'GetCandy\Api\Http\Controllers',
], function ($router) {


    $router->post('addresses', 'Addresses\AddressController@store');

    // Address Route
    $router->delete('addresses/{id}', 'Addresses\AddressController@destroy');
    $router->put('addresses/{id}', 'Addresses\AddressController@update');
    $router->post('addresses/{id}/default', 'Addresses\AddressController@makeDefault');
    $router->post('addresses/{id}/default/remove', 'Addresses\AddressController@removeDefault');
    /*
        |--------------------------------------------------------------------------
        | API Client Routes
        |--------------------------------------------------------------------------
        |
        | Here is where you can register API Client routes for GetCandy
        | These are READ ONLY routes
        |
     */

    /*
     * Customers
     */
    $router->resource('customers', 'Customers\CustomerController', [
        'except' => ['index', 'edit', 'create', 'show'],
    ]);

    /*
     * Orders
     */

    $router->post('orders/process', 'Orders\OrderController@process');
    $router->post('orders/{id}/expire', 'Orders\OrderController@expire');
    $router->put('orders/{id}/shipping/address', 'Orders\OrderController@shippingAddress');
    $router->put('orders/{id}/shipping/methods', 'Orders\OrderController@shippingMethod');
    $router->get('orders/{id}/shipping/methods', 'Orders\OrderController@shippingMethods');
    $router->put('orders/{id}/shipping/cost', 'Orders\OrderController@shippingCost');
    $router->put('orders/{id}/contact', 'Orders\OrderController@addContact');
    $router->put('orders/{id}/billing/address', 'Orders\OrderController@billingAddress');

    

    $router->post('orders/{id}/lines', 'Orders\OrderLineController@store');
    $router->delete('orders/lines/{id}', 'Orders\OrderLineController@destroy');

    $router->resource('orders', 'Orders\OrderController', [
        'only' => ['store', 'show'],
    ]);

    /*
     * Payments
     */
    $router->post('payments/3d-secure', 'Payments\PaymentController@validateThreeD');
    $router->get('payments/provider/{name}', 'Payments\PaymentController@provider');
    $router->get('payments/providers', 'Payments\PaymentController@providers');
    $router->get('payments/types', 'Payments\PaymentTypeController@index');

    $router->get('routes', 'Routes\RouteController@index');
    $router->get('routes/{slug}', [
        'uses' => 'Routes\RouteController@show',
    ])->where(['slug' => '.*']);

    /*
     * Shipping
     */
    $router->get('shipping', 'Shipping\ShippingMethodController@index');
    $router->get('shipping/prices/estimate', 'Shipping\ShippingPriceController@estimate');

    // disable user update for client


    /*
     * Users
     */
    $router->get('users/fields', 'Users\UserController@fields');
    $router->get('users/current', 'Users\UserController@getCurrentUser');
    $router->resource('users', 'Users\UserController', [
        'except' => ['create', 'store'],
    ]);
    $router->get('plugins', 'Plugins\PluginController@index');


    /*
     * Basket Lines
     */
    $router->post('basket-lines', 'Baskets\BasketLineController@store');
    $router->put('basket-lines/{id}', 'Baskets\BasketLineController@update');
    $router->post('basket-lines/{id}/add', 'Baskets\BasketLineController@addQuantity');
    $router->post('basket-lines/{id}/remove', 'Baskets\BasketLineController@removeQuantity');
    $router->delete('basket-lines', 'Baskets\BasketLineController@destroy');
    $router->delete('basket-lines/{id}', 'Baskets\BasketLineController@destroyById');
    

    /*
     * Baskets
     */

    $router->put('baskets/{id}/discounts', 'Baskets\BasketController@addDiscount');
    $router->delete('baskets/{id}/discounts', 'Baskets\BasketController@deleteDiscount');

    Route::group(['middleware', ['api:channels']], function ($router) {
        $router->post('baskets/resolve', 'Baskets\BasketController@resolve');
        $router->get('baskets/current', 'Baskets\BasketController@current');
        $router->get('baskets/saved', 'Baskets\BasketController@saved');
        $router->post('baskets/{id}/save', 'Baskets\BasketController@save');
        $router->post('baskets/{id}/claim', 'Baskets\BasketController@claim');
        $router->delete('baskets/{basket}', 'Baskets\BasketController@destroy');
        $router->put('baskets/saved/{basket}', 'Baskets\SavedBasketController@update');
    });
    $router->resource('baskets', 'Baskets\BasketController', [
        'except' => ['edit', 'create', 'destroy', 'update'],
    ]);

        /*
     * Orders
     */
    $router->post('orders/bulk', 'Orders\OrderController@bulkUpdate');
    $router->get('orders/types', 'Orders\OrderController@getTypes');
    $router->get('orders/export', 'Orders\OrderController@getExport');
    $router->post('orders/email-preview/{status}', 'Orders\OrderController@emailPreview');
    $router->resource('orders', 'Orders\OrderController', [
        'only' => ['index', 'update'],
    ]);



    /*
     * Account
     */
    $router->post('account/password', [
        'as' => 'account.password.reset',
        'uses' => 'Auth\AccountController@resetPassword',
    ]);


    /*
     * Account
     */
    $router->post('account/password', [
        'as' => 'account.password.reset',
        'uses' => 'Auth\AccountController@resetPassword',
    ]);

    $router->get('search/suggest', 'Search\SearchController@suggest');
    $router->get('search/products', 'Search\SearchController@products');
});
