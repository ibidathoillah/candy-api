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



        /*
     * Currencies
     */
    $router->resource('currencies', 'Currencies\CurrencyController', [
        'except' => ['edit', 'create'],
    ]);

    $router->resource('assets', 'Assets\AssetController', [
        'except' => ['edit', 'create'],
    ]);
    $router->resource('attributes', 'Attributes\AttributeController', [
        'except' => ['edit', 'create'],
    ]);

    $router->resource('attribute-groups', 'Attributes\AttributeGroupController', [
        'except' => ['edit', 'create'],
    ]);

    $router->resource('categories', 'Categories\CategoryController', [
        'except' => ['index', 'edit', 'create', 'show'],
    ]);

    /*
     * Channels
     */
    $router->resource('channels', 'Channels\ChannelController', [
        'except' => ['edit', 'create', 'show'],
    ]);

    $router->resource('collections', 'Collections\CollectionController', [
        'except' => ['index', 'edit', 'create', 'show'],
    ]);

    /*
     * Customers
     */
    $router->resource('customers/groups', 'Customers\CustomerGroupController', [
        'except' => ['edit', 'create', 'show'],
    ]);

    $router->resource('customers', 'Customers\CustomerController', [
        'except' => ['edit', 'create', 'store'],
    ]);

    /*
     * Discounts
     */
    $router->resource('discounts', 'Discounts\DiscountController', [
        'except' => ['edit', 'create'],
    ]);

    /*
     * Languages
     */
    $router->resource('languages', 'Languages\LanguageController', [
        'except' => ['edit', 'create'],
    ]);

   /*
     * Layouts
     */
    $router->resource('layouts', 'Layouts\LayoutController', [
        'except' => ['edit', 'create'],
    ]);

    // /*
    //  * Pages
    //  */
    // $router->get('/pages/{channel}/{lang}/{slug?}', 'Pages\PageController@show');
    // $router->resource('pages', 'Pages\PageController', [
    //     'except' => ['edit', 'create'],
    // ]);

    /*
     * Product variants
     */
    $router->resource('products/variants', 'Products\ProductVariantController', [
        'except' => ['edit', 'create', 'store'],
    ]);

        /*
     * Resource routes
     */
    $router->resource('products', 'Products\ProductController', [
        'except' => ['edit', 'create', 'show'],
    ]);

    /*
     * Product families
     */
    $router->resource('product-families', 'Products\ProductFamilyController', [
        'except' => ['edit', 'create'],
    ]);

    /*
     * Routes
     */
    $router->resource('routes', 'Routes\RouteController', [
        'except' => ['index', 'show', 'edit', 'create'],
    ]);

    $router->resource('shipping/zones', 'Shipping\ShippingZoneController', [
        'except' => ['edit', 'create'],
    ]);

    $router->resource('shipping', 'Shipping\ShippingMethodController', [
        'except' => ['index', 'edit', 'create'],
    ]);

    /*
     * Tags
     */
    $router->resource('tags', 'Tags\TagController', [
        'except' => ['edit', 'create'],
    ]);


    /*
     * Taxes
     */
    $router->resource('taxes', 'Taxes\TaxController', [
        'except' => ['edit', 'create'],
    ]);



});

