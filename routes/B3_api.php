<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AlJouaiRequests;

Route::get('/test-test/{phoneNumber}', function (Request $request) {
    $inputPhoneNumber = $request->phoneNumber;
    $result  = AlJouaiRequests::tst($inputPhoneNumber);
    return response()->json($result);
});



// http://10.10.20.11:8000/api/f/last-five/0535575165
Route::get('/f/last-five/{phoneNumber}', function (Request $request) {
    // Standatd MOAPI 
    // $inputPhoneNumber = $request->phoneNumber;
    // $userEntries  = AlJouaiRequests::getAllCustomerDocEntries($inputPhoneNumber);
    // natsort($userEntries);
    // $reversed  = array_reverse($userEntries);
    // $lastFiveArray  = array_slice($reversed, 0, 5);
    // $userInvoicesDates  = AlJouaiRequests::getAllCustomerInvoicesDates($lastFiveArray);
    // $otherArray  = [];
    // foreach ($userInvoicesDates as $key => $value) {
    //     $newKey  = $value['DocDate'];
    //     if (isset($otherArray[$newKey])) {
    //         $otherArray[$newKey][] = $key;
    //     } else {
    //         $kwys = [];
    //         $kwys[] = $key;
    //         $otherArray[$newKey]  = $kwys;
    //     }
    // }
    // ksort($otherArray);
    // $newArrayForDate = [];
    // $newContainer = [];
    // foreach ($otherArray as $date => $arrOfEntries) {
    //     foreach ($arrOfEntries as $index => $docEntry) {
    //         $newArrayForDate[$docEntry] = [
    //             'Total' => AlJouaiRequests::getInvoiceDocTotal($docEntry),
    //             'NumberOfItems' => AlJouaiRequests::getCountOfNumbers($docEntry),
    //             'Dates' => AlJouaiRequests::getInvoiceDatesOnly($docEntry),
    //         ];
    //         if (isset($newContainer[$date])) {
    //             $newContainer[$date][$docEntry][] = $newArrayForDate[$docEntry];
    //         } else {
    //             $newContainer[$date] = [];
    //             $newContainer[$date][$docEntry][] = $newArrayForDate[$docEntry];
    //         }
    //     }
    // }
    return response()->json([]); // Data is Now Sorted 
    // return response()->json($newContainer); // Data is Now Sorted 
}); // * DONE 