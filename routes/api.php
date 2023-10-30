<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AlJouaiRequests;
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


// Route::get('/koko', function (Request $request) {
//     $res = AlJouaiRequests::getPhoneNumberFromDocEntry('356948');
//     return response()->json($res);
// });
Route::group([], __DIR__ . '/apiOne.php'); // * Ok 
Route::group([], __DIR__ . '/apiTwo.php'); // ! WORKING On 




// http://10.10.10.66:8005/api/current-month/
Route::get('/current-month/{phoneNumber}', function (Request $request) {
    // $inputPhoneNumber = $request->phoneNumber;
    // $userInvoicesDates  = getAllCustomerInvoicesDates(getAllCustomerDocEntries($inputPhoneNumber));
    // $currentMonthInvoices  = getInvoicesOfCurrentMonth($userInvoicesDates);
    // return response()->json($currentMonthInvoices);
    $data  = '{
    "357480": {
        "DocDate": "2023-10-26",
        "DocDueDate": "2023-10-26"
    },
    "357481": {
        "DocDate": "2023-10-26",
        "DocDueDate": "2023-10-26"
    },
    "357209": {
        "DocDate": "2023-10-25",
        "DocDueDate": "2023-10-25"
    },
    "353829": {
        "DocDate": "2023-10-04",
        "DocDueDate": "2023-10-04"
    },
    "356948": {
        "DocDate": "2023-10-23",
        "DocDueDate": "2023-10-23"
    },
    "356950": {
        "DocDate": "2023-10-23",
        "DocDueDate": "2023-10-23"
    },
    "355402": {
        "DocDate": "2023-10-14",
        "DocDueDate": "2023-10-14"
    } , 
    "777": {
        "DocDate": "2024-01-01",
        "DocDueDate": "2024-01-01"
    } , 
    "666": {
        "DocDate": "2024-12-12",
        "DocDueDate": "2024-12-12"
    },
    "999": {
        "DocDate": "2024-12-12",
        "DocDueDate": "2024-12-12"
    }
}';
    $jsonData = json_decode($data, true); // Convert the JSON string to an associative array
    return response()->json($jsonData);
}); // * EndPoint DONE # 3 




Route::post('/verify', function (Request $request) {
    // Get in Json Data : Phone Number && An Invoice Number 
    $jsonData = $request->json()->all();
    $userPhone = $jsonData['phone'];
    $userInvoice  = $jsonData['invoice'];
    $userInvoice = (string) $userInvoice;
    // ! 1 - Get all Invoices using Phone Number 
    // ? 2 - Compare the Invoice with the Array  ; 
    // ^ If Yes Then Send the Reset Password and Make it With "Mobile" Not mail 
    $entriesArray  = AlJouaiRequests::getAllCustomerDocEntries($userPhone);

    if (in_array($userInvoice, $entriesArray)) {
        return response()->json([
            "res" => 1
        ]);
    } else {
        return response()->json([
            "res" => 0
        ]);
    }
});
