<?php


namespace App\Http\Controllers\Api;

use DateTime;
use PDO;

class AlJouaiRequests
{
    /**
     * Summary of establishConnectionDB
     * @param mixed $inputQuery
     * @return \PDOStatement|bool
     */
    public static function establishConnectionDB($inputQuery)
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
    }

    /**
     * Summary of getAllCustomerDocEntries
     * @param mixed $mobileNumber
     * @return array
     */
    public static function getAllCustomerDocEntries($mobileNumber)
    {
        //* MobileNumber is a View The Has Phone Number and the DocEntry For this User 
        //* Row is Object Because of 'PDO::FETCH_OBJ' , $data is Array 
        $phoneQuery  = "SELECT * FROM MobileNumber WHERE [Mobile Number] = '" . $mobileNumber . "'";
        $stmt  = self::establishConnectionDB($phoneQuery);
        $data  = [];
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $data[] = (string) $row->DocEntry;
        }
        return $data;
    }

    public static function getPhoneNumberFromDocEntry($docEntry)
    {
        $phoneQuery  = "SELECT Phone FROM [@MobileNumber] WHERE DocEntry = '" . $docEntry . "'";
        $stmt  = self::establishConnectionDB($phoneQuery);
        $phone = $stmt->fetch(PDO::FETCH_OBJ);
        // return $phone;
        return $phone->Phone;
    }

    public static function getSingleInvoiceGeneralData($docEntry)
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
        $stmt  = self::establishConnectionDB($generalInfoQuery);
        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $data[] = $row;
        }
        //  Check If Data length is 1 Then Return It Only 
        // if (count($data) == 1) {
        //     return $data[0];
        // } // Could be More than One ? 
        return $data[0]; // -> Object Of General Data 
    }

    public static function getInvoiceDocTotal($docEntry)
    {
        $generalInfoQuery = "
    SELECT T0.DocTotal
    FROM AljouaiT.DBO.OINV T0
    LEFT JOIN AljouaiT.DBO.NNM1 N1 ON N1.Series = T0.Series
    LEFT JOIN  AljouaiT.DBO.OUSR T00 ON T0.USERSIGN = T00.INTERNAL_K
    WHERE 
    T0.CANCELED ='N' and T0.DocEntry = " . $docEntry;
        $stmt  = self::establishConnectionDB($generalInfoQuery);
        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $data[] = $row;
        }
        return $data[0]->DocTotal;
    }

    public static function getSingleInvoiceItemsData($docEntry)
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
        $stmt  = self::establishConnectionDB($invoiceItemsQuery);
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $data[] = $row;
        }
        return $data; // -> Array Of Objects 
    }

    public static function getCountOfNumbers($docEntry)
    {
        $invoiceItemsQuery = "
    SELECT
    T1.DocEntry , count(T1.ItemCode) as Totalz
    FROM 
    (TM.DBO.OINV T0 inner join TM.DBO.INV1 T1 on T1.DocEntry= T0.DocEntry)
    WHERE T1.DocEntry = " . $docEntry . "
    GROUP BY T1.DocEntry ";
        $data = [];
        $stmt  = self::establishConnectionDB($invoiceItemsQuery);
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $data[] = $row;
        }
        if ($data) {
            return $data[0]->Totalz;
        } else {
            return 0;
        }
    }

    public static function  getSingleInvoiceTotalData($docEntry)
    {
        $invoiceGeneralData  = self::getSingleInvoiceGeneralData($docEntry);
        $invoiceItemsData  = self::getSingleInvoiceItemsData($docEntry);
        $totalInvoiceData  = [
            'invoiceGeneralData' => $invoiceGeneralData,
            'invoiceItemsData' => $invoiceItemsData
        ];
        return $totalInvoiceData;
    }

    public static  function getAllCustomerDocEntriesWithData($docEntriesArray)
    {
        $finalArrayOfAllInvoicesWithData  = [];
        foreach ($docEntriesArray as $singleDocEntry) {
            $finalArrayOfAllInvoicesWithData[$singleDocEntry] = self::getSingleInvoiceTotalData($singleDocEntry);
        }
        return $finalArrayOfAllInvoicesWithData;
    }

    public static function getAllCustomerInvoicesDates($docEntriesArray) // Internal Usage 
    {
        // docEntriesArray Come From getAllCustomerDocEntries() ; 
        $finalArrayOfInvoicesWithDates = [];
        foreach ($docEntriesArray as $singleDocEntry) {
            $dDate = new DateTime(self::getSingleInvoiceGeneralData($singleDocEntry)->DocDate);
            $ddDate = new DateTime(self::getSingleInvoiceGeneralData($singleDocEntry)->DocDueDate);
            $invoiceDates  = [
                "DocDate" => $dDate->format('Y-m-d'),
                "DocDueDate" => $ddDate->format('Y-m-d')
            ];
            $finalArrayOfInvoicesWithDates[$singleDocEntry] = $invoiceDates;
        }
        return $finalArrayOfInvoicesWithDates;
    }

    public static function getInvoicesInRange($entriesAndDates, $startDate, $endDate)
    {
        // entriesAndDates Are Coming From this Function getAllCustomerInvoicesDates()
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
    }

    public static function getInvoiceInDate($entriesAndDates, $specificDate)
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
    }

    public static function getInvoicesOfCurrentMonth($entriesAndDates)
    {
        $currentMonth = date('Y-m');
        $currentMonthInvoices = [];
        foreach ($entriesAndDates as $invoiceNumber => $dates) {
            $docDate = $dates['DocDate'];
            $docMonth = date('Y-m', strtotime($docDate));
            if ($docMonth === $currentMonth) {
                // The invoice is from the current month
                $currentMonthInvoices[$invoiceNumber] = $dates;
                // + TOTAL && Number Of items 
            }
        }
        return $currentMonthInvoices;
    }

    public static function getTotalOfInvoice($docEntry)
    {
        $res  = self::getSingleInvoiceGeneralData($docEntry);
        return $res->DocTotal;
    }

    public static function  getNumberOfItemsInvoice($docEntry)
    {
        $res = self::getSingleInvoiceItemsData($docEntry);
        return count($res);
    }

    public static function getInvoiceDatesOnly($docEntry)
    {
        $res = self::getSingleInvoiceGeneralData($docEntry);
        $ddate = new DateTime($res->DocDate);
        $dddate =  new DateTime($res->DocDueDate);
        return [
            'DocDate' => $ddate->format('Y-m-d'),
            'DocDueDate' =>  $dddate->format('Y-m-d')
        ];
    }
}
