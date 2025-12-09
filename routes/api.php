<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\OwnCompanyController;
use App\Http\Controllers\PartnerCompanyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\FeedbackController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {

    // Products
    Route::get('products', [ProductController::class, 'index']);
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{product}', [ProductController::class, 'update']);
    Route::delete('products/{product}', [ProductController::class, 'destroy']);
    Route::get('products/{product}/companies', [ProductController::class, 'companies']);

    // Product Feedbacks
    Route::get('products/{product}/feedbacks', [FeedbackController::class, 'index']);
    Route::post('products/{product}/feedbacks', [FeedbackController::class, 'store']);
    Route::put('products/{product}/feedbacks/{feedback}', [FeedbackController::class, 'update']);
    Route::delete('products/{product}/feedbacks/{feedback}', [FeedbackController::class, 'destroy']);

    // Partner Companies
    Route::get('partner-companies', [PartnerCompanyController::class, 'index']);
    Route::post('partner-companies', [PartnerCompanyController::class, 'store']);
    Route::get('partner-companies/{partnerCompany}', [PartnerCompanyController::class, 'show']);
    Route::put('partner-companies/{partnerCompany}', [PartnerCompanyController::class, 'update']);
    Route::delete('partner-companies/{partnerCompany}', [PartnerCompanyController::class, 'destroy']);

    // Own Companies
    Route::get('own-companies', [OwnCompanyController::class, 'index']);
    Route::post('own-companies', [OwnCompanyController::class, 'store']);
    Route::get('own-companies/{ownCompany}', [OwnCompanyController::class, 'show']);
    Route::put('own-companies/{ownCompany}', [OwnCompanyController::class, 'update']);
    Route::delete('own-companies/{ownCompany}', [OwnCompanyController::class, 'destroy']);

    // Contracts
    Route::get('contracts', [ContractController::class, 'index']);
    Route::post('contracts', [ContractController::class, 'store']);
    Route::get('contracts/{contract}', [ContractController::class, 'show']);
    Route::put('contracts/{contract}', [ContractController::class, 'update']);
    Route::delete('contracts/{contract}', [ContractController::class, 'destroy']);

    // Transactions (new structure)
    Route::get('transactions', [TransactionController::class, 'index']);
    Route::post('transactions', [TransactionController::class, 'store']);
    Route::get('transactions/{transaction}', [TransactionController::class, 'show']);
    Route::put('transactions/{transaction}', [TransactionController::class, 'update']);
    Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy']);
    Route::post('transactions/{transaction}/upload-files', [TransactionController::class, 'uploadFiles']);
    Route::delete('transactions/{transaction}/files/{file}', [TransactionController::class, 'deleteFile']);
    Route::post('transactions/{transaction}/send-notification', [TransactionController::class, 'sendNotification']);
    Route::post('/transactions/{transaction}/paid-file', [TransactionController::class, 'updatePaidFile']);
    Route::get('/transaction-files/download/{file}', [TransactionController::class, 'downloadFile']);

    // Payment Statistics
    Route::get('payment-statistics', [App\Http\Controllers\PaymentStatisticsController::class, 'index']);

    // Users
    Route::get('/users', [UserController::class, 'index']);


    // Contracts
    // User Management
    Route::get('user-management', [UserManagementController::class, 'index']);
    Route::post('user-management', [UserManagementController::class, 'store']);
    Route::get('user-management/{currentUser}', [UserManagementController::class, 'show']);
    Route::put('user-management/{currentUser}', [UserManagementController::class, 'update']);
    Route::delete('user-management/{currentUser}', [UserManagementController::class, 'destroy']);

    // Roles & Permissions
    Route::get('roles', [UserManagementController::class, 'getRoles']);
    Route::post('roles', [UserManagementController::class, 'createRole']);
    Route::put('roles/{role}', [UserManagementController::class, 'updateRole']);
    Route::delete('roles/{role}', [UserManagementController::class, 'deleteRole']);
    Route::get('permissions', [UserManagementController::class, 'getPermissions']);
    Route::put('roles/{role}/permissions', [UserManagementController::class, 'updateRolePermissions']);

});


