<?php


// Route::middleware()
//     ->namespace('')
//     ->prefix($apiPrefix)
//     ->group(__DIR__ . '../../routes/api.php');

Route::group([
    'middleware' => [
        'auth:api',
        'api.currency',
        'api.customer_groups',
        'api.locale',
        'api.tax',
        'api.channels',
        'api.detect_hub',
        'api.admin',
    ],
    'prefix' => 'api/'.config('app.api_version', 'v1'),
    'namespace' => 'GetCandy\Api\Http\Controllers',
], function ($router) {

    /*
    * Imports
    */
    $router->post('import', 'Utils\ImportController@process');

    $router->get('activity-log', [
        'as' => 'activitylog.index',
        'uses' => 'ActivityLog\ActivityLogController@index',
    ]);

    $router->post('activity-log', [
        'as' => 'activitylog.store',
        'uses' => 'ActivityLog\ActivityLogController@store',
    ]);

    $router->post('auth/impersonate', [
        'as' => 'auth.impersonate',
        'uses' => 'Auth\ImpersonateController@process',
    ]);

    /*
     * Assets
     */

    $router->put('assets', 'Assets\AssetController@updateAll');
    $router->post('assets/simple', 'Assets\AssetController@storeSimple');
    $router->resource('assets', 'Assets\AssetController', [
        'except' => ['edit', 'create'],
    ]);

    /*
     * Associations
     */

    $router->get('associations/groups', 'Associations\AssociationGroupController@index');
    /*
     * Attributes
     */
    $router->put('attributes/order', 'Attributes\AttributeController@reorder');
    $router->resource('attributes', 'Attributes\AttributeController', [
        'except' => ['edit', 'create'],
    ]);

    /*
     * Attribute Groups
     */
    $router->put('attribute-groups/order', 'Attributes\AttributeGroupController@reorder');
    $router->resource('attribute-groups', 'Attributes\AttributeGroupController', [
        'except' => ['edit', 'create'],
    ]);

    /*
     * Baskets
     */
    $router->get('baskets', 'Products\ProductController@index');
    $router->post('baskets/{id}/meta', 'Baskets\BasketController@addMeta');
    $router->put('baskets/{id}/user', 'Baskets\BasketController@putUser');
    $router->delete('baskets/{id}/user', 'Baskets\BasketController@deleteUser');

    /*
     * Payments
     */
    $router->post('payments/{id}/refund', 'Payments\PaymentController@refund');
    $router->post('payments/{id}/void', 'Payments\PaymentController@void');

    /*
     * Categories
     */
    $router->get('categories/parent/{parentID?}', 'Categories\CategoryController@getByParent');
    $router->post('categories/reorder', 'Categories\CategoryController@reorder');
    $router->post('categories/{category}/products/attach', 'Products\ProductCategoryController@attach');
    $router->put('categories/{category}/products', 'Categories\CategoryController@putProducts');
    $router->put('categories/{category}/layouts', 'Categories\LayoutController@store');

    $router->post('categories/{category}/routes', 'Categories\CategoryRouteController@store');
    $router->resource('categories', 'Categories\CategoryController', [
        'except' => ['index', 'edit', 'create', 'show'],
    ]);

    /*
     * Channels
     */
    $router->resource('channels', 'Channels\ChannelController', [
        'except' => ['edit', 'create', 'show'],
    ]);

    /*
     * Channels
     */
    $router->post('collections/{collection}/routes', 'Collections\CollectionRouteController@store');
    $router->put('collections/{collection}/products', 'Collections\CollectionProductController@store');
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
     * Products
     */
    $router->prefix('products')->namespace('Products')->group(__DIR__.'/../routes/catalogue-manager/products.php');

    /*
     * Reporting
     */

    $router->prefix('reports')->namespace('Reports')->group(function ($router) {
        $router->get('/sales', 'ReportController@sales');
        $router->get('/orders', 'ReportController@orders');
        $router->get('/metrics/{subject}', 'ReportController@metrics');
    });
    
    /*
     * Resource routes
     */
    $router->resource('products', 'Products\ProductController', [
        'except' => ['edit', 'create', 'show'],
    ]);


    /*
     * Routes
     */
    $router->resource('routes', 'Routes\RouteController', [
        'except' => ['index', 'show', 'edit', 'create'],
    ]);

    /*
     * Saved search
     */
    $router->post('saved-searches', 'Search\SavedSearchController@store');
    $router->delete('saved-searches/{id}', 'Search\SavedSearchController@destroy');
    $router->get('saved-searches/{type}', 'Search\SavedSearchController@getByType');

    /*
     * Settings
     */
    $router->get('settings/{handle}', 'Settings\SettingController@show');

    /*
     * Shipping
     */
    $router->resource('shipping/zones', 'Shipping\ShippingZoneController', [
        'except' => ['edit', 'create'],
    ]);
    $router->post('shipping/{id}/prices', 'Shipping\ShippingPriceController@store');
    $router->delete('shipping/prices/{id}', 'Shipping\ShippingPriceController@destroy');
    $router->put('shipping/prices/{id}', 'Shipping\ShippingPriceController@update');
    $router->put('shipping/{id}/zones', 'Shipping\ShippingMethodController@updateZones');
    $router->put('shipping/{id}/users', 'Shipping\ShippingMethodController@updateUsers');
    $router->delete('shipping/{id}/users/{user}', 'Shipping\ShippingMethodController@deleteUser');
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

    /*
     * Users
     */
    $router->delete('users/payments/{id}', 'Users\UserController@deleteReusablePayment');
    $router->post('users', 'Users\UserController@store');
    $router->post('users/{userid}', 'Users\UserController@update');

});
