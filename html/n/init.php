<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, user-scalable=no">
<link rel="stylesheet" href="/style.css">
<title>NFC Tag Inventory</title>
</head>
<body>

<h2>Initialize Google Spreadsheet</h2>

<?php

include("./settings.php");
include("./common.php");

// setup Google Spreadsheet handler
require __DIR__. '/vendor/autoload.php';
$keyFile = __DIR__. "/credentials.json";
$sheet = setupGoogleSheet($keyFile);

function init_sheet($sheet, $sheet_id, $sheet_name, $column) {
    $len = count($column);
    $values = get_cell($sheet, $sheet_id, $sheet_name."!A1:A1");

    if ($values[0][0] != NULL and strcmp($values[0][0], "ID") == 0) { // already initialized
        echo("<p>sheet_id: ".$sheet_id." is already initialized.");
        return;
    }

    $range = $sheet_name.'!A1:'.chr(ord('A')+$len-1).'1';
    echo("<p>UPDATE: ".$range);
    
    set_cell($sheet, $sheet_id, $range, [$column]);

    echo("<P>CELL added");

    $sid = sheetname2id($sheet, $sheet_id, $sheet_name);

    try {
        $request_data = [
            'repeatCell' => [
                'fields' => 'userEnteredFormat(backgroundColor)',
                'range' => [
                    'sheetId' => $sid,
                    'startRowIndex' => 0,       // 行の開始位置
                    'endRowIndex' => 1,        // 行の終了位置
                    'startColumnIndex' => 0,    // 列の開始位置
                    'endColumnIndex' => $len,      // 列の終了位置
                ],
                'cell' => [
                    'userEnteredFormat' => [
                        'backgroundColor' => [  // 色はRGB形式
                            'red' => 180/255,
                            'green' => 180/255,
                            'blue' => 200/255
                        ]
                    ],
                ],
            ],
        ];
    
        $requests = [new \Google_Service_Sheets_Request($request_data)];
        $batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
            'requests' => $requests
        ]);
        $response = $sheet->spreadsheets->batchUpdate($sheet_id, $batchUpdateRequest);
    } catch (\Exception $e) {
        echo("<p>Can't initialize:<br>".$e);
    }
}

function add_sheet($sheet, $sheet_id, $sheet_name) {
    try {
        $body = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
            'requests' => [
                'addSheet' => [
                    'properties' => [
                        'title' => $sheet_name
                    ]
                ]
            ]
        ]);
        $response = $sheet->spreadsheets->batchUpdate($sheet_id, $body);
        $new_sheet_id = $response->getReplies()[0]
            ->getAddSheet()
            ->getProperties()
            ->sheetId;
        echo('<h2>Added Sheet: '.$sheet_name.' to '.$sheet_id.'</h2>');
    } catch (\Exception $e) {
        echo("<p>Can't add a sheet: ".$e);
    }
}

function setup_sheet($sheet, $sheet_id, $sheet_name,  $list) {
    add_sheet($sheet, $sheet_id, $sheet_name);
    init_sheet($sheet, $sheet_id, $sheet_name, $list);
}

setup_sheet($sheet, $sheet_id, $obj_sheet_name,  ['ID', 'Date', 'Time', 'Name', 'Type', 'Hash', 'Location', 'Update Date', 'Update Time']);
setup_sheet($sheet, $sheet_id, $log_sheet_name,  ['ID', 'Date', 'Time', 'Name', 'Timestamp', 'Type', 'Hash']);
setup_sheet($sheet, $sheet_id, $log_sheet_name2,  ['ID', 'Date', 'Time', 'Name', 'Timestamp', 'Type', 'Hash']);



// $sheet_id = '124IafXXLgC3TEo58HNyWRxBio9X5bV829Az0oIPPIiw';
/*
add_sheet($sheet, $sheet_id, $obj_sheet_name);
add_sheet($sheet, $sheet_id, $log_sheet_name);
add_sheet($sheet, $sheet_id, $log_sheet_name2);

init_sheet($sheet, $sheet_id, $obj_sheet_name,
init_sheet($sheet, $sheet_id, $log_sheet_name, ['ID', 'Date', 'Time', 'Name', 'Timestamp', 'Place', 'Hash']);
init_sheet($sheet, $sheet_id, $log_sheet_name, ['ID', 'Date', 'Time', 'Name', 'Timestamp', 'Place', 'Hash']);
*/

echo("<h2>Init Sheets done.</h2>");

?>

</body>