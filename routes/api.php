<?php

use Illuminate\Support\Facades\Route;
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });



Route::group([], __DIR__ . '/apiOne.php'); // * Ok 
Route::group([], __DIR__ . '/apiTwo.php'); // ! WORKING On 
Route::group([], __DIR__ . '/apiThree.php'); // ! WORKING On 


