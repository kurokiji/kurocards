<?php

use App\Http\Controllers\CardsController;
use App\Http\Controllers\OffersController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\UsersController;
use App\Http\Middleware\AdminValidator;
use App\Http\Middleware\PrivateAndProValidator;
use App\Http\Middleware\TokenValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::put('login', [UsersController::class, 'login']);
Route::put('register', [UsersController::class, 'register']);
Route::put('passwordrecover', [UsersController::class, 'passwordRecover']);
Route::put('search', [SalesController::class, 'searchCard']);
Route::put('searchoffer', [OffersController::class, 'searchOffer']);


Route::middleware(TokenValidator::class)->prefix('loggeduser')->group(function(){
    Route::put('card', [CardsController::class, 'addCard'])->middleware(AdminValidator::class);
    Route::put('collection', [CardsController::class, 'addCollection'])->middleware(AdminValidator::class);
    Route::put('addexistcardtocollection', [CardsController::class, 'addExistsCardToCollection'])->middleware(AdminValidator::class);
    Route::put('addandoffer', [OffersController::class, 'addCardAndOffer'])->middleware(PrivateAndProValidator::class);
    Route::put('searchcard', [OffersController::class, 'searchCard'])->middleware(PrivateAndProValidator::class);
});

