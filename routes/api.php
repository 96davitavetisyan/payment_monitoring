<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\AuthController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // Projects
    Route::get('projects', [ProjectController::class, 'index']);
    Route::post('projects', [ProjectController::class, 'store']);
    Route::get('projects/{project}', [ProjectController::class, 'show']);
    Route::put('projects/{project}', [ProjectController::class, 'update']);
    Route::delete('projects/{project}', [ProjectController::class, 'destroy']);
    Route::post('projects/{project}/toggle-status', [ProjectController::class, 'toggleStatus']);

    // Transactions
    Route::get('projects/{project}/transactions', [TransactionController::class, 'index']);
    Route::post('projects/{project}/transactions', [TransactionController::class, 'store']);
    Route::put('projects/{project}/transactions/{transaction}', [TransactionController::class, 'update']);
    Route::delete('projects/{project}/transactions/{transaction}', [TransactionController::class, 'destroy']);
    Route::post('projects/{project}/transactions/{transaction}/toggle-status', [TransactionController::class, 'toggleStatus']);

    // Feedback
    Route::get('projects/{project}/feedbacks', [FeedbackController::class, 'index']);
    Route::post('projects/{project}/feedbacks', [FeedbackController::class, 'store']);
    Route::put('projects/{project}/feedbacks/{feedback}', [FeedbackController::class, 'update']);
    Route::delete('projects/{project}/feedbacks/{feedback}', [FeedbackController::class, 'destroy']);

    // Products
    Route::get('products', [ProductController::class, 'index']);
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{product}', [ProductController::class, 'update']);
    Route::delete('products/{product}', [ProductController::class, 'destroy']);
    Route::get('products/{product}/companies', [ProductController::class, 'companies']);

    // Companies
    Route::get('companies', [CompanyController::class, 'index']);
    Route::post('companies', [CompanyController::class, 'store']);
    Route::get('companies/{company}', [CompanyController::class, 'show']);
    Route::put('companies/{company}', [CompanyController::class, 'update']);
    Route::delete('companies/{company}', [CompanyController::class, 'destroy']);
    Route::get('companies/{company}/subscriptions', [CompanyController::class, 'subscriptions']);

    // Users
    Route::get('/users', [UserController::class, 'index']);
});

    // Partner Companies
    Route::get('partner-companies', [\App\Http\Controllers\PartnerCompanyController::class, 'index']);
    Route::post('partner-companies', [\App\Http\Controllers\PartnerCompanyController::class, 'store']);
    Route::get('partner-companies/{partnerCompany}', [\App\Http\Controllers\PartnerCompanyController::class, 'show']);
    Route::put('partner-companies/{partnerCompany}', [\App\Http\Controllers\PartnerCompanyController::class, 'update']);
    Route::delete('partner-companies/{partnerCompany}', [\App\Http\Controllers\PartnerCompanyController::class, 'destroy']);

    // Own Companies
    Route::get('own-companies', [\App\Http\Controllers\OwnCompanyController::class, 'index']);
    Route::post('own-companies', [\App\Http\Controllers\OwnCompanyController::class, 'store']);
    Route::get('own-companies/{ownCompany}', [\App\Http\Controllers\OwnCompanyController::class, 'show']);
    Route::put('own-companies/{ownCompany}', [\App\Http\Controllers\OwnCompanyController::class, 'update']);
    Route::delete('own-companies/{ownCompany}', [\App\Http\Controllers\OwnCompanyController::class, 'destroy']);

    // Contracts
    Route::get('contracts', [\App\Http\Controllers\ContractController::class, 'index']);
    Route::post('contracts', [\App\Http\Controllers\ContractController::class, 'store']);
    Route::get('contracts/{contract}', [\App\Http\Controllers\ContractController::class, 'show']);
    Route::put('contracts/{contract}', [\App\Http\Controllers\ContractController::class, 'update']);
    Route::delete('contracts/{contract}', [\App\Http\Controllers\ContractController::class, 'destroy']);

    // Transactions (new structure)
    Route::get('transactions', [\App\Http\Controllers\TransactionController::class, 'index']);
    Route::post('transactions', [\App\Http\Controllers\TransactionController::class, 'store']);
    Route::get('transactions/{transaction}', [\App\Http\Controllers\TransactionController::class, 'show']);
    Route::put('transactions/{transaction}', [\App\Http\Controllers\TransactionController::class, 'update']);
    Route::delete('transactions/{transaction}', [\App\Http\Controllers\TransactionController::class, 'destroy']);
    Route::post('transactions/{transaction}/upload-files', [\App\Http\Controllers\TransactionController::class, 'uploadFiles']);
    Route::delete('transactions/{transaction}/files/{file}', [\App\Http\Controllers\TransactionController::class, 'deleteFile']);
    Route::post('transactions/{transaction}/send-notification', [\App\Http\Controllers\TransactionController::class, 'sendNotification']);
