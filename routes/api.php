<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\Api\AuthController;


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
    Route::get('projects/{project}/toggle-status', [ProjectController::class, 'toggleStatus']);

    // Transactions
    Route::get('projects/{project}/transactions', [TransactionController::class, 'index']);
    Route::post('projects/{project}/transactions', [TransactionController::class, 'store']);
    Route::put('projects/{project}/transactions/{transaction}', [TransactionController::class, 'update']);
    Route::delete('projects/{project}/transactions/{transaction}', [TransactionController::class, 'destroy']);

    // Feedback
    Route::get('projects/{project}/feedbacks', [FeedbackController::class, 'index']);
    Route::post('projects/{project}/feedbacks', [FeedbackController::class, 'store']);
    Route::put('projects/{project}/feedbacks/{feedback}', [FeedbackController::class, 'update']);
    Route::delete('projects/{project}/feedbacks/{feedback}', [FeedbackController::class, 'destroy']);

    Route::get('/users', [UserController::class, 'index'])->middleware('auth:sanctum');
});
