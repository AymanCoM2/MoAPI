<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AlJouaiRequests;
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::get('/koko', function (Request $request) {
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
    $otherArray  = [];
    $jsonData = json_decode($data, true);
    foreach ($jsonData as $key => $value) {
        $newKey  = $value['DocDate'];
        if (isset($otherArray[$newKey])) {
            // Get tht old orray Of this Eky and Append the Next value 
            $otherArray[$newKey][] = $key;
        } else {
            $kwys = [];
            $kwys[] = $key;
            $otherArray[$newKey]  = $kwys; // Initialize array with First Element 

        }
    }
    return response()->json($otherArray);
});


Route::group([], __DIR__ . '/apiOne.php'); // * Ok 
Route::group([], __DIR__ . '/apiTwo.php'); // ! WORKING On 

// http://10.10.10.66:8005/api/current-month/
Route::get('/current-month/{phoneNumber}', function (Request $request) {
    $inputPhoneNumber = $request->phoneNumber;
    $userEntries  = AlJouaiRequests::getAllCustomerDocEntries($inputPhoneNumber);
    $userInvoicesDates  = AlJouaiRequests::getAllCustomerInvoicesDates($userEntries);
    $currentMonthInvoices  = AlJouaiRequests::getInvoicesOfCurrentMonth($userInvoicesDates);
    // return response()->json($currentMonthInvoices);
    $otherArray  = [];
    // $jsonData = json_decode($data, true);
    foreach ($currentMonthInvoices as $key => $value) {
        $newKey  = $value['DocDate'];
        if (isset($otherArray[$newKey])) {
            // Get tht old orray Of this Eky and Append the Next value 
            $otherArray[$newKey][] = $key;
        } else {
            $kwys = [];
            $kwys[] = $key;
            $otherArray[$newKey]  = $kwys; // Initialize array with First Element 
        }
    }
    // ! TODO looping over the aaray now  ?
    /**
     * 1- Sort by the keys 
     * 2- Key By Get Get the array elements 
     * 3- Replace the DocEntry With Docentry Refering to the array Of Data 
     * 
     */

    // Convert the Data to list 
    // and Remove the Data 0 and 1 into something else 
    // Else For this To Convert this Date Of it ; 
    // Insert 
    $newArrayForDate = [];
    $newContainer = [];
    foreach ($otherArray as $date => $arrOfEntries) {
        foreach ($arrOfEntries as $index => $docEntry) {
            $newArrayForDate[$docEntry] = [
                'Total' => AlJouaiRequests::getTotalOfInvoice($docEntry),
                'NumberOfItems' => AlJouaiRequests::getNumberOfItemsInvoice($docEntry),
                'Dates' => AlJouaiRequests::getInvoiceDatesOnly($docEntry),
            ];
            if (isset($newContainer[$date])) {
                $newContainer[$date][$docEntry][] = $newArrayForDate[$docEntry];
            } else {
                $newContainer[$date] = [];
                $newContainer[$date][$docEntry][] = $newArrayForDate[$docEntry];
            }
        }
    }
    return response()->json([$newContainer]);
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
