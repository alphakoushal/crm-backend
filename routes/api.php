<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SmartInvoiceController;
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});
Route::get('/fetch-data', function () {
    return response()->json(['message' => 'fetch data API is working in branch one']);
});
Route::post('/ask-invoice', [SmartInvoiceController::class, 'ask']);
Route::prefix('subscription')->middleware(['auth-jwt'])->group(function () {
     return response()->json(['message' => 'Auth API is working']);
});
?>