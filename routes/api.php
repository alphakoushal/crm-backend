<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SmartInvoiceController;
use App\Http\Controllers\Api\AIAgentController;
use App\Http\Controllers\Api\HRAgentController;
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});
Route::get('/fetch-leave', [HRAgentController::class,'pendingLeave']);
Route::post('/ask-invoice', [SmartInvoiceController::class, 'ask']);
Route::post('/ai-agent', [AIAgentController::class, 'run']);
Route::post('/hr-agent', [HRAgentController::class, 'run']);
Route::prefix('subscription')->middleware(['auth-jwt'])->group(function () {
     return response()->json(['message' => 'Auth API is working']);
});
?>