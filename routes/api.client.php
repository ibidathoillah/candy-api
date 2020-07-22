<?php

Route::group([
    'middleware' => [
        'api.client',
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

    $router->get('/', function () {
        $channel = app()->make(GetCandy\Api\Core\Channels\Interfaces\ChannelFactoryInterface::class);
        $currency = app()->make(GetCandy\Api\Core\Currencies\Interfaces\CurrencyConverterInterface::class);
        return response()->json([
            'version' => GetCandy\Api\Core\CandyApi::version(),
            'locale' => app()->getLocale(),
            'channel' => new GetCandy\Api\Http\Resources\Channels\ChannelResource($channel->getChannel()),
            'currency' => new GetCandy\Api\Http\Resources\Currencies\CurrencyResource($currency->get()),
        ]);
    });



    $router->get('products', 'Products\ProductController@index');
    $router->get('products/recommended', 'Products\ProductController@recommended');
    $router->get('products/{product}', 'Products\ProductController@show');
    $router->get('search', 'Search\SearchController@search');
    $router->get('search/sku', 'Search\SearchController@sku');


    /*
     * Customers
     */
    $router->post('customers', 'Customers\CustomerController@store');

    /*
     * Password
     */
    $router->post('password/reset', 'Auth\ResetPasswordController@reset');
    $router->post('password/reset/request', 'Auth\ForgotPasswordController@sendResetLinkEmail');



    // $router->get('channels', 'Channels\ChannelController@index');
    $router->get('channels/{id}', 'Channels\ChannelController@show');
    $router->get('collections', 'Collections\CollectionController@index');
    $router->get('collections/{id}', 'Collections\CollectionController@show');
    $router->get('categories/{id}', 'Categories\CategoryController@show');

    /*
     * Categories
     */
    $router->get('categories', 'Categories\CategoryController@index');
    $router->get('categories/{category}/children', 'Categories\CategoryController@children');

    /*
     * Countries
     */
    $router->get('countries', 'Countries\CountryController@index');



});

