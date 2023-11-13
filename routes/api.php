<?php

use App\Http\Controllers\Api\AlJouaiRequests;
use App\Models\User;
use App\Models\UserSetting;
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
    if (Auth::attempt(['phone' => $userPhone, 'password' => $userPass])) {
        $user = Auth::user();
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

Route::post('/verify', function (Request $request) {
    $jsonData = $request->json()->all();
    $userPhone = $jsonData['phone'];
    $userInvoice  = $jsonData['invoice'];
    $userInvoice = (string) $userInvoice;
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
}); // * EndPoint#4

// http://10.10.20.11:8000/api/f/last-five/0535575165
Route::get('/f/last-five/{phoneNumber}', function (Request $request) {
    // Standatd MOAPI 
    $inputPhoneNumber = $request->phoneNumber;
    $userEntries  = AlJouaiRequests::getAllCustomerDocEntries($inputPhoneNumber);
    natsort($userEntries);
    $reversed  = array_reverse($userEntries);
    $lastFiveArray  = array_slice($reversed, 0, 5);
    $userInvoicesDates  = AlJouaiRequests::getAllCustomerInvoicesDates($lastFiveArray);
    $otherArray  = [];
    foreach ($userInvoicesDates as $key => $value) {
        $newKey  = $value['DocDate'];
        if (isset($otherArray[$newKey])) {
            $otherArray[$newKey][] = $key;
        } else {
            $kwys = [];
            $kwys[] = $key;
            $otherArray[$newKey]  = $kwys;
        }
    }
    ksort($otherArray);
    $newArrayForDate = [];
    $newContainer = [];
    foreach ($otherArray as $date => $arrOfEntries) {
        foreach ($arrOfEntries as $index => $docEntry) {
            $newArrayForDate[$docEntry] = [
                'Total' => AlJouaiRequests::getInvoiceDocTotal($docEntry),
                'NumberOfItems' => AlJouaiRequests::getCountOfNumbers($docEntry),
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
    return response()->json($newContainer); // Data is Now Sorted 
}); // * DONE 

// Route::get('/test-test/{doc}', function (Request $request) {
//     $doc = $request->doc  ; 
//     $result  =AlJouaiRequests::getSingleInvoiceItemsData($doc);
//     return response()->json($result);
// });


function desiredFormat($docEntry)
{
    // Get the DocEntry and Return the Desired Format Needed For the Invoice 
    $dateAsKey  = null;   // ^ This is the Date We Get from the Invoice and use it as key
    $finalCombination  = [];
    $invoiceNumber = $docEntry;
    $newArrayForDate = [];
    $datesWholeObject  = AlJouaiRequests::getInvoiceDatesOnly($invoiceNumber);
    $newArrayForDate[] = [
        'Total' => AlJouaiRequests::getInvoiceDocTotal($invoiceNumber),
        'NumberOfItems' => AlJouaiRequests::getCountOfNumbers($invoiceNumber),
        'Dates' => $datesWholeObject,
    ];
    $dateAsKey = $datesWholeObject['DocDate'];
    $finalCombination[$dateAsKey][$invoiceNumber] = $newArrayForDate;
    return $finalCombination;
} // ! Done and Generic For any Invoice 

// http://10.10.20.11:8000/api/f/invoice/353829
Route::get('/f/invoice/{docEntry}', function (Request $request) {
    $result  = desiredFormat($request->docEntry);
    return response()->json($result);
}); // * DONE 

Route::get('/invoice/{docEntry}', function (Request $request) {
    $invoiceNumber = $request->docEntry;
    $invoiceData  = AlJouaiRequests::getSingleInvoiceTotalData($invoiceNumber);
    return response()->json($invoiceData);
}); // * EndPoint#2 


// http://10.10.20.11:8000/api/f/get-invoices-within-range
// ! POST
// {
//     "enddate": "2023-05-05",
//     "startdate": "2023-01-01",
//     "phone": "0535575165"
// }
Route::post('/f/get-invoices-within-range', function (Request $request) {
    // $jsonData = $request->json()->all();
    // $start = $jsonData['startdate'];
    // $phone = $jsonData['phone'];
    // $end = $jsonData['enddate'];
    // if (isset($end)) {
    //     $end  = $jsonData['enddate'];
    // } else {
    //     $end = date("Y-m-d");
    // }
    // $userDocEntries  = AlJouaiRequests::getAllCustomerDocEntries($phone);
    // $entriesAndDates = AlJouaiRequests::getAllCustomerInvoicesDates($userDocEntries);
    // $r  = AlJouaiRequests::getInvoiceEntriesONLYInRange($entriesAndDates, $start, $end);
    // $userInvoicesDates  = AlJouaiRequests::getAllCustomerInvoicesDates($r);
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


// http: //10.10.20.11:8000/api/f/specific-date
// ! POST
// {
//     "date": "2023-10-28",
//     "phone": "0535575165"
// }
Route::post('/f/specific-date', function (Request $request) {
    $jsonData = $request->json()->all();
    $specificDate  = $jsonData['date'];
    $userPhone  = $jsonData['phone'];
    $userDocEntries  = AlJouaiRequests::getAllCustomerDocEntries($userPhone);
    $entriesAndDates = AlJouaiRequests::getAllCustomerInvoicesDates($userDocEntries);
    $r = AlJouaiRequests::getInvoicesInDateEntryONLY($entriesAndDates, $specificDate);
    $userInvoicesDates  = AlJouaiRequests::getAllCustomerInvoicesDates($r);
    $otherArray  = [];
    foreach ($userInvoicesDates as $key => $value) {
        $newKey  = $value['DocDate'];
        if (isset($otherArray[$newKey])) {
            $otherArray[$newKey][] = $key;
        } else {
            $kwys = [];
            $kwys[] = $key;
            $otherArray[$newKey]  = $kwys;
        }
    }

    ksort($otherArray);
    $newArrayForDate = [];
    $newContainer = [];
    foreach ($otherArray as $date => $arrOfEntries) {
        foreach ($arrOfEntries as $index => $docEntry) {
            $newArrayForDate[$docEntry] = [
                'Total' => AlJouaiRequests::getInvoiceDocTotal($docEntry),
                'NumberOfItems' => AlJouaiRequests::getCountOfNumbers($docEntry),
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
    return response()->json($newContainer); // Data is Now Sorted 
}); // * DONE 

// Route::get('/qr/{docEntry}', function (Request $request) {
//     $entry  = $request->docEntry;
//     $qr  =  AlJouaiRequests::getQrCode($entry);
//     return response()->json($qr);
// }); // * DONE 


// Route::get('/toto/{docEntry}', function (Request $request) {
//     $entry  = $request->docEntry;
//     $total  =  AlJouaiRequests::getNumberOfItemsInvoice($entry);
//     $total2  =  AlJouaiRequests::getCountOfNumbers($entry);
//     return response()->json([
//         'total_1' => $total,
//         'total_2' => $total2
//     ]);
// }); // * DONE 

Route::get('/current-month/{phoneNumber}', function (Request $request) {
    $inputPhoneNumber = $request->phoneNumber;
    $userEntries  = AlJouaiRequests::getAllCustomerDocEntries($inputPhoneNumber);
    $userInvoicesDates  = AlJouaiRequests::getAllCustomerInvoicesDates($userEntries);
    $currentMonthInvoices  = AlJouaiRequests::getInvoicesOfCurrentMonth($userInvoicesDates);
    $otherArray  = [];
    foreach ($currentMonthInvoices as $key => $value) {
        $newKey  = $value['DocDate'];
        if (isset($otherArray[$newKey])) {
            $otherArray[$newKey][] = $key;
        } else {
            $kwys = [];
            $kwys[] = $key;
            $otherArray[$newKey]  = $kwys;
        }
    }
    ksort($otherArray);
    $newArrayForDate = [];
    $newContainer = [];
    foreach ($otherArray as $date => $arrOfEntries) {
        foreach ($arrOfEntries as $index => $docEntry) {
            $newArrayForDate[$docEntry] = [
                'Total' => AlJouaiRequests::getInvoiceDocTotal($docEntry),
                'NumberOfItems' => AlJouaiRequests::getCountOfNumbers($docEntry),
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
    return response()->json($newContainer); // Data is Now Sorted 
}); // * EndPoint#3 


Route::get('/top-header-only/{phone}', function (Request $request) {
    $inputPhoneNumber = $request->phone;
    $res = AlJouaiRequests::headerFooterTopFive($inputPhoneNumber);
    return response()->json($res);
});
Route::get('/top-items-only/{phone}', function (Request $request) {
    $inputPhoneNumber = $request->phone;
    $res = AlJouaiRequests::itemsDataTopFive($inputPhoneNumber);
    return response()->json($res);
});


Route::get('/get-version', function (Request $request) {
    $asps = UserSetting::first();
    if ($asps) {
        return response()->json($asps->app_version);
    } else {
        return response()->json(null); // Return null as JSON
    }
});


Route::post('/post-version', function (Request $request) {
    $jsonData = $request->json()->all();
    $version = $jsonData['version'];
    $asps = UserSetting::first();
    if ($asps) {
        $asps->app_version = $version;
        $asps->save();
    } else {
        $asps = new UserSetting();
        $asps->app_version = $version;
        $asps->save();
    }
    return response()->json($asps);
});
