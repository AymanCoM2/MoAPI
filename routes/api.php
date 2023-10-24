<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });



/**
 * ^ In THE 
 * ! Return Of the Doc Entries we Need also TO get the Dates For those DOCS 
 * * Add this Tomorrow for the APIS endpoint for it  ; 
 */
function ubuntuConnectionDB($inputQuery)
{
    $serverName = "10.10.10.100";
    $databaseName = "AljouaiT";
    $uid = "ayman";
    $pwd = "admin@1234";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        "TrustServerCertificate" => true,
    ];
    $conn = new PDO("sqlsrv:server = $serverName; Database = $databaseName;", $uid, $pwd, $options);
    $stmt = $conn->query($inputQuery);
    return $stmt;
} // ? DONE 

function getAllCustomerDocEntries($mobileNumber)
{
    //* MobileNumber is a View The Has Phone Number and the DocEntry For this User 
    //* Row is Object Because of 'PDO::FETCH_OBJ' , $data is Array 
    $phoneQuery  = "SELECT * FROM MobileNumber WHERE [Mobile Number] = '" . $mobileNumber . "'";
    $stmt  = ubuntuConnectionDB($phoneQuery);
    while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $data[] = $row->DocEntry;
    }
    return $data;
} // ? DONE 

function getSingleInvoiceGeneralData($docEntry)
{
    $generalInfoQuery = "
    SELECT T0.DocEntry, CASE WHEN ISNULL(T0.LicTradNum, '') = '' THEN N'فاتورة ضريبية مبسطة'
    ELSE N'فاتورة ضريبية' END AS 'InvoiceTitle',
    T0.CardName, T0.CardCode , T0.LicTradNum , T0.DocDate , T0.DocDueDate ,
    CONCAT(ISNULL(N1.SeriesName,'') ,T0.DocNum )  'DocNum',
    (T0.DocTotal + T0.DiscSum - T0.RoundDif - T0.VatSum) 'NetTotalBefDisc',
    T0.DiscPrcnt , T0.DiscSum ,
    (T0.DocTotal - T0.RoundDif - T0.VatSum) 'NetTotalBefVAT',
    T0.VatSum , T0.DocTotal , T00.U_NAME , T0.Comments 
    FROM AljouaiT.DBO.OINV T0
    LEFT JOIN AljouaiT.DBO.NNM1 N1 ON N1.Series = T0.Series
    LEFT JOIN  AljouaiT.DBO.OUSR T00 ON T0.USERSIGN = T00.INTERNAL_K
    WHERE 
    T0.CANCELED ='N' and T0.DocEntry = " . $docEntry;
    $stmt  = ubuntuConnectionDB($generalInfoQuery);
    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $data[] = $row;
    }
    // // Check If Data length is 1 Then Return It Only 
    // if (count($data) == 1) {
    //     return $data[0];
    // } // Could be More than One ? 
    return $data[0]; // -> Object Of General Data 
} // ? DONE 

function getSingleInvoiceItemsData($docEntry)
{
    $invoiceItemsQuery = "
    SELECT
    T0.DocNum , T1.DocEntry, T1.ItemCode, T1.Dscription, T1.Quantity , T1.unitMsr , L0.Location ,
    T1.PriceBefDi , T1.DiscPrcnt , T1.Price , 
    ROUND(T1.Price * T1.Quantity,2) 'TotalBefVAT',
    ROUND(ROUND(T1.Price * T1.Quantity,2) * 1.05 ,2) 'TotalAftVAT'
    FROM 
    (TM.DBO.OINV T0 inner join TM.DBO.INV1 T1 on T1.DocEntry= T0.DocEntry)
    LEFT JOIN (TM.DBO.OWHS W0 LEFT JOIN TM.DBO.OLCT L0 ON W0.Location = L0.Code)
    ON W0.WhsCode = T1.WhsCode
    WHERE T1.DocEntry IN (SELECT T0.DocEntry FROM TM.DBO.OINV T0 WHERE T0.CANCELED ='N') and 
    T1.DocEntry = " . $docEntry;
    $data = [];
    $stmt  = ubuntuConnectionDB($invoiceItemsQuery);
    while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $data[] = $row;
    }
    return $data; // -> Array Of Objects 
} // ? DONE 

function getSingleInvoiceTotalData($docEntry)
{
    $invoiceGeneralData  = getSingleInvoiceGeneralData($docEntry);
    $invoiceItemsData  = getSingleInvoiceItemsData($docEntry);
    $totalInvoiceData  = [
        'invoiceGeneralData' => $invoiceGeneralData,
        'invoiceItemsData' => $invoiceItemsData
    ];
    return $totalInvoiceData;
} // ? DONE 

function getAllCustomerDocEntriesWithData($docEntriesArray)
{
    $finalArrayOfAllInvoicesWithData  = [];
    foreach ($docEntriesArray as $singleDocEntry) {
        $finalArrayOfAllInvoicesWithData[$singleDocEntry] = getSingleInvoiceTotalData($singleDocEntry);
    }
    return $finalArrayOfAllInvoicesWithData;
} // ?  Ok FOR NOW  

function getAllCustomerInvoicesDates($docEntriesArray) // Internal Usage 
{
    $finalArrayOfInvoicesWithDates = [];
    foreach ($docEntriesArray as $singleDocEntry) {
        $dDate = new DateTime(getSingleInvoiceGeneralData($singleDocEntry)->DocDate);
        $ddDate = new DateTime(getSingleInvoiceGeneralData($singleDocEntry)->DocDueDate);
        $invoiceDates  = [
            "DocDate" => $dDate->format('Y-m-d'),
            "DocDueDate" => $ddDate->format('Y-m-d')
        ];
        $finalArrayOfInvoicesWithDates[$singleDocEntry] = $invoiceDates;
    }
    return $finalArrayOfInvoicesWithDates;
} // ? DONE 

function getInvoicesInRange($entriesAndDates, $startDate, $endDate)
{
    $filteredInvoices = [];
    $startDateObj = new DateTime($startDate);
    $endDateObj = new DateTime($endDate);
    foreach ($entriesAndDates as $invoiceNumber => $dates) {
        $docDateObj = new DateTime($dates['DocDate']);
        if ($docDateObj >= $startDateObj && $docDateObj <= $endDateObj) {
            $filteredInvoices[$invoiceNumber] = $dates;
        }
    }
    return $filteredInvoices;
} // ! Ok 

function getInvoiceInDate($entriesAndDates, $specificDate)
{
    $matchingEntries = array();
    // Iterate through each entry in $entriesAndDates
    foreach ($entriesAndDates as $invoiceNumber => $entry) {
        // Check if the entry has both "DocDate" and "DocDueDate" keys
        if (isset($entry["DocDate"]) && isset($entry["DocDueDate"])) {
            // Compare the "DocDate" to the $specificDate
            if ($entry["DocDate"] === $specificDate) {
                // If it matches, add it to the $matchingEntries array
                $matchingEntries[$invoiceNumber] = $entry;
            }
        }
    }
    return $matchingEntries;
} // ! Ok 


function getInvoicesOfCurrentMonth($entriesAndDates)
{
    $currentMonth = date('Y-m');
    $currentMonthInvoices = [];
    foreach ($entriesAndDates as $invoiceNumber => $dates) {
        $docDate = $dates['DocDate'];
        $docMonth = date('Y-m', strtotime($docDate));
        if ($docMonth === $currentMonth) {
            // The invoice is from the current month
            $currentMonthInvoices[$invoiceNumber] = $dates;
        }
    }
    return $currentMonthInvoices;
} // ? DONE 

// & http://127.0.0.1:8000/api/test/5
Route::get('/test/{doc}', function (Request $request) {
    $data  = getSingleInvoiceTotalData($request->doc);
    return response()->json($data);
});



// http://127.0.0.1:8000/api/from/1/to/12
Route::get('/from/{startMonth}/to/{endMonth}', function (Request $request) {
    // ! Validation For the Range ? 
    $start = $request->startMonth;
    $end = $request->endMonth;
});

// http://127.0.0.1:8000/api/specific-date/
Route::get('/specific-date/{dateInput}', function (Request $request) {
    $neededDate = $request->dateInput;
});


// http://10.10.10.66:8005/api/user-docs/0553142429
Route::get('/user-docs/{phoneNumber}', function (Request $request) {
    $inputPhoneNumber = $request->phoneNumber;
    $userDocs  = getAllCustomerDocEntries($inputPhoneNumber);
    return response()->json($userDocs);
}); // * EndPoint DONE # 1 

// http://10.10.10.66:8005/api/invoice/5
Route::get('/invoice/{docEntry}', function (Request $request) {
    $invoiceNumber = $request->docEntry;
    $invoiceData  = getSingleInvoiceTotalData($invoiceNumber);
    return response()->json($invoiceData);
}); // * EndPoint DONE # 2 

// http://10.10.10.66:8005/api/current-month/0553142429
Route::get('/current-month/{phoneNumber}', function (Request $request) {
    $inputPhoneNumber = $request->phoneNumber;
    $userInvoicesDates  = getAllCustomerInvoicesDates(getAllCustomerDocEntries($inputPhoneNumber));
    $currentMonthInvoices  = getInvoicesOfCurrentMonth($userInvoicesDates);
    return response()->json($currentMonthInvoices);
}); // * EndPoint DONE # 3 