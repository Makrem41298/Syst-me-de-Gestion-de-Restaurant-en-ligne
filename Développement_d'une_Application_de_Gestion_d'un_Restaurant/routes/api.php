<?php

use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\Authentication\AuthAdminController;
use App\Http\Controllers\API\Authentication\AuthUserController;
use App\Http\Controllers\API\Authentication\ResttingPassowrd;
use App\Http\Controllers\API\Authentication\VerificationController;
use App\Http\Controllers\API\BookingsController;
use App\Http\Controllers\API\CategoriesController;
use App\Http\Controllers\API\ContractsController;
use App\Http\Controllers\API\DashboredController;
use App\Http\Controllers\API\DeliveriesController;
use App\Http\Controllers\API\DriversController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\ItemsController;
use App\Http\Controllers\API\OrdersController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\RestaurantSettingController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\SerivecsController;
use App\Http\Controllers\API\SubCategoriesController;
use App\Http\Controllers\API\TablesController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::group([
    'middleware' => 'api',
    'prefix'=>'auth'

], function ($router) {
    Route::post('user/login', [AuthUserController::class, 'login']);
    Route::post('user/register', [AuthUserController::class, 'register']);
    Route::post('user/logout', [AuthUserController::class, 'logout']);
    Route::post('user/refresh', [AuthUserController::class, 'refresh']);
    Route::get('user/profile', [AuthUserController::class, 'userProfile']);

    Route::post('admin/login', [AuthAdminController::class, 'login']);
    Route::post('admin/logout', [AuthAdminController::class, 'logout']);
    Route::post('admin/refresh', [AuthAdminController::class, 'refresh']);
    Route::get('admin/profile', [AuthAdminController::class, 'userProfile']);
    //Resting Password
    Route::post('sendLinkRestting',[ResttingPassowrd::class,'sendLinkRestEmail'])->name('password.email');
    Route::get('/reset-password/{email}/{token}', function ($email, $token) {
        return response()->json([
            'data' => ['token' => $token, 'email' => $email,], 'message' => 'This token and email ready'], 200);
    })->name('password.reset');
    Route::post('/reset-password',[ResttingPassowrd::class,'resetPassword'])->name('password.update');
//verification Email
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verificationEmail'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/resend', [VerificationController::class, 'resendVerificationEmail'])
        ->middleware('throttle:2,1')
        ->name('verification.resend')->middleware('jwt.verify:users');



});

//Route Table:
Route::middleware('jwt.verify:admins')->prefix('admin')->group(function (){
    Route::put('/restaurant-settings', [RestaurantSettingController::class, 'update']);

    //Route Table:
    Route::controller(TablesController::class)->prefix('table')->group(function (){
            Route::post('/','store');
            Route::get('/','index');
            Route::get('/{id}','show');
            Route::put('/{id}','update');
            Route::delete('/{id}','destroy');
            Route::DELETE('/','deleteAll');
    });
    //Route Category
    Route::controller(CategoriesController::class)->prefix('Category')->group(function (){
        Route::post('/','store');
        Route::put('/{id}','update');
        Route::delete('/{id}','destroy');
        Route::DELETE('/','deleteAll');

    });
    //Route Driver
    Route::controller(DriversController::class)->prefix('driver')->group(function (){
        Route::post('/','store');
        Route::get('/{id}','show');
        Route::get('/','index');
        Route::put('/{id}','update');
        Route::delete('/{id}','destroy');
        Route::DELETE('/','deleteAll');

    });
    //Route Serivecs

    Route::controller(SerivecsController::class)->prefix('service')->group(function (){
        Route::post('/','store');
        Route::put('/{id}','update');
        Route::delete('/{id}','destroy');
        Route::DELETE('/','deleteAll');
    });
    //Route SubCategories

    Route::controller(SubCategoriesController::class)->prefix('subcategory')->group(function (){
        Route::post('/','store');
        Route::delete('/{id}','destroy');
        Route::DELETE('/','deleteAll');
        Route::put('/items/{id}','addItem');
        Route::delete('/items/{item_id}','deleteItem');
        Route::put('/{id}','update');


    });
    //Route item
    Route::controller(ItemsController::class)->prefix('item')->group(function (){
        Route::post('/','store');
        Route::post('/{id}','update');
        Route::delete('/{id}','destroy');
        Route::DELETE('/','deleteAll');


    });
    //Route Order
    Route::controller(OrdersController::class)->prefix('order')->group(function (){
        Route::get('/','index');
        Route::put('/{orderId}','updateOrder');
        Route::post('/','storeOrder');
        Route::post('/table/{tableId}','storeOrderTable');
        Route::delete('/{id}','destroy');
        Route::delete('/','deleteAll');
        Route::get('/{id}','showOrder');
        Route::get('/showMyOrder/employ','showMyOrderAdmin');
    });
    //Route Booking

    Route::controller(BookingsController::class)->prefix('booking')->group(function (){
        Route::post('/customer','bookingCustomer');
        Route::get('/{id}','show');
        Route::get('/','index');
        Route::put('/{id}','updateBookingAdmin');
        Route::delete('/{id}','destroy');
        Route::DELETE('/','deleteAll');


    });
    //Route User
    Route::controller(UserController::class)->prefix('users')->group(function (){
        Route::get('/{id}','show');
        Route::get('/','index');
        Route::put('/{id}','update');
        Route::delete('/{id}','destroy');
        Route::DELETE('/','deleteAll');

    });
    //Route Contract
    Route::controller(ContractsController::class)->prefix('contract')->group(function (){
        Route::post('/','store');
        Route::get('/{id}','show');
        Route::get('/','index');
        Route::put('/{id}','update');
        Route::delete('/{id}','destroy');
        Route::DELETE('/','deleteAll');

    });
    Route::controller(DeliveriesController::class)->prefix('delivery')->group(function (){
        Route::put('/','addDriver');
        Route::get('/','index');
        Route::get('{id}','show');
        Route::put('/{id}','update');
    });
    Route::get('/role',[RoleController::class,'index']);
    Route::post('/role',[RoleController::class,'storeRole']);
    Route::put('/role/{id}',[RoleController::class,'updateRole']);
    Route::delete('/role/{id}',[RoleController::class,'deleteRole']);
    Route::get('/role/{id}',[RoleController::class,'showRole']);

Route::prefix('employ')->group(function (){
    Route::post('/',[AdminController::class,'storeEmployee']);
    Route::put('/{id}',[AdminController::class,'updateEmployee']);
    Route::post('/changeMyPassword/',[AdminController::class,'updateMyPassword']);
    Route::get('/',[AdminController::class,'index']);
    Route::get('/{id}',[AdminController::class,'showEmployee']);
    Route::delete('/{id}',[AdminController::class,'deleteEmployee']);
    Route::delete('/',[AdminController::class,'deleteAllEmployee']);

});
Route::get('/permission',PermissionController::class);
Route::get('/dashboard',[DashboredController::class,'index']);




});



//Route User
Route::middleware(['jwt.verify:users','verified'])->prefix('user')->group(function (){
    //Route Rating Service user

    Route::controller(SerivecsController::class)->prefix('service')->group(function (){
        Route::post('/rating/{serviceId}','ratingService');
        Route::put('/rating/{serviceId}','updateRatingService');

    });

    //Route Item user
    Route::controller(ItemsController::class)->prefix('item')->group(function (){
        Route::post('/rating/{itemId}','ratingItme');
        Route::put('/rating/{itemId}','updateRatingItem');

    });
    //Route Order user
    Route::controller(OrdersController::class)->prefix('order')->group(function (){
        Route::post('/','storeOrderUser');
        Route::get('/','showOrderUser');
        Route::put('/{id}','updateOrderUser');


    });
    Route::put('delivery/{order_id}',[DeliveriesController::class,'confirmationDeliveryUser']);
    //Route Booking user
    Route::controller(BookingsController::class)->prefix('booking')->group(function (){
        Route::get('/','showBookingUser');
        Route::post('/','bookingUser');
        Route::put('/{id}','updateBookingUser');

    });
    //Route User
    Route::controller(UserController::class)->group(function (){
        Route::put('/change-password','changePassword');
        Route::put('/profile','UpdateProfile');
    });

});



//Route service gust
Route::get('/service/{id}',[SerivecsController::class,'show']);
Route::get('/service',[SerivecsController::class,'index']);
//Route Category gust
Route::get('/Category/{id}',[CategoriesController::class,'show']);
Route::get('/Category',[CategoriesController::class,'index']);
//Rout sub category gust
Route::get('/subcategory/{id}',[SubCategoriesController::class,'show']);
Route::get('/subcategory',[SubCategoriesController::class,'index']);
//Rout item gust
Route::get('/item/{id}',[ItemsController::class,'show']);
Route::get('/item',[ItemsController::class,'index']);
Route::get('/home',HomeController::class);

Route::get('/restaurant-settings', [RestaurantSettingController::class, 'index']);










