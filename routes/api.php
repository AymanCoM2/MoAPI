<?php

use App\Http\Controllers\Api\AlJouaiRequests;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([], __DIR__ . '/apiOne.php'); // * Ok 
Route::group([], __DIR__ . '/apiTwo.php'); // * ok
Route::group([], __DIR__ . '/apiThree.php'); // * ok
Route::group([], __DIR__ . '/apiFour.php'); // ! WORKING On 


Route::post('/login', function (Request $request) {
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
        return response()->json(1);
    }
    return response()->json(0, 200);
});


Route::post('/register-user', function (Request $request) {
    $jsonData = $request->json()->all();
    $userPass = $jsonData['password'];
    $userPhone = $jsonData['phone'];
    $existUser  = User::where('phone', $userPhone)->first();
    if ($existUser) {
        return response()->json(0);
    } else {
        $registeredUser  = new User();
        $registeredUser->phone = $userPhone;
        $registeredUser->password = Hash::make($userPass);
        $registeredUser->save();
        return response()->json(1);
    }
});



Route::get('/test-test/{doc}', function (Request $request) {
    $doc = $request->doc  ; 
    $result  =AlJouaiRequests::getSingleInvoiceItemsData($doc);
    return response()->json($result);
});
