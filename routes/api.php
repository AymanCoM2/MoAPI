<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([], __DIR__ . '/apiOne.php'); // * Ok 
Route::group([], __DIR__ . '/apiTwo.php'); // ! WORKING On 
Route::group([], __DIR__ . '/apiThree.php'); // ! WORKING On 


Route::post('/api/login', function (Request $request) {
    // Validate the incoming request data
    $jsonData = $request->json()->all();
    $userPhone = $jsonData['phone'];
    $userPass = $jsonData['password'];
    // $validator = Validator::make($request->all(), [
    //     'phone' => 'required',
    //     'password' => 'required',
    // ]);

    // if ($validator->fails()) {
    //     return response()->json(['error' => 'Validation failed'], 422);
    // }

    // Attempt to log in the user
    if (Auth::attempt(['phone' => $userPhone, 'password' => $userPass])) {
        $user = Auth::user();
        // $token = $user->createToken('MyAppToken')->plainTextToken;

        return response()->json(['access_token' => "TOken234534"]);
    }
    return response()->json(['error' => 'Invalid credentials'], 401);
});
