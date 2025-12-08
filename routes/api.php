<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SmartInvoiceController;
use App\Http\Controllers\Api\AIAgentController;
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);//test comment by 1107
});
Route::get('/fetch-data', function () {
    return response()->json(['message' => 'fetch data API is working in branch TWO']);
});
Route::post('/ask-invoice', [SmartInvoiceController::class, 'ask']);
Route::post('/ai-agent', [AIAgentController::class, 'run']);
Route::prefix('subscription')->middleware(['auth-jwt'])->group(function () {
     return response()->json(['message' => 'Auth API is working']);
});
?>