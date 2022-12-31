<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, user-scalable=no">
<link rel="stylesheet" href="/style.css">
<title>NFC Tag Touch</title>
</head>
<body>

<?php
include("./settings.php");
include("./common.php");
// setup Google Spreadsheet handler
require __DIR__. '/vendor/autoload.php';
$keyFile = __DIR__. "/credentials.json";
$sheet = setupGoogleSheet($keyFile);


$id = $_GET['i']; // object id
$name = $_GET['n']; // object name
$obj = $_GET['t'];
switch ($obj) {
    case 'o':
        $objtype = 'object';
        break;
    case 'b':
        $objtype = 'box';
        break;
    case 'p':
        $objtype = 'place';
        break;
    case 'room':
        $objtype = 'room';
        break;
    default:
        $objtype = 'x';
        brak;
}

date_default_timezone_set('Asia/Tokyo');
$ts = time();
$objDateTime = new DateTime();
$day = $objDateTime->format('Y/m/d'); //2019/05/21
$hour = $objDateTime->format('H:i:s'); //13:33:59

$hash = getHostHash();
$placename = NULL;

//echo("<h1>ID:".$name." ".$log_sheet_name."</h1>");

// find the latest location
function find_place($sheet, $sheet_id, $log_sheet_name, $ts, $hash, $time_diff) {
    //echo("<p>FindPlace:". $log_sheet_name);
    $response = $sheet->spreadsheets_values->get(
        $sheet_id,
        $log_sheet_name.'!A:G'
    );
    $values = $response->getValues();
    
    foreach (array_reverse($values) as $val) {
        //echo("<p>#".($val[4]));
        if ($ts - $val[4] < $time_diff and (strcmp($val[5], "place") == 0 or strcmp($val[5], "box")==0)) {
            //echo("<p>* ".$hash." ".$val[6]." ".var_dump($val)."<br>");
            if (strcmp($hash, $val[6]) == 0) {
                $placename = $val[3];
                return ($placename);
            }
        }
    }
    return NULL;
}

//echo("<p>find locaiton:".$sheet_id);

// find object location
if (strcmp($objtype, "object") == 0 or strcmp($objtype, "box") == 0) {
    $placename = find_place($sheet, $sheet_id, $log_sheet_name, $ts, $hash, 60*10); // latest location
    echo("<p><h3>Place/Box:".$placename."<h3>");
    if (isset($placename)) { // update Object Spreadsheet
        $values = get_cell($sheet, $sheet_id, $obj_sheet_name.'!A:A');
        $cell_idx = 0;
        foreach ($values as $val) {
            //echo(var_dump($val));
            $cell_idx = $cell_idx + 1;
            if (strcmp($val[0], $id) == 0) {
                break;
            }
        }
        //echo ("<p>CELL:".$cell_idx." / id".$id." ".($cell_idx+2));
        if ($cell_idx > 0) {
            set_cell($sheet, $sheet_id, $obj_sheet_name.'!G'.$cell_idx.':I'.$cell_idx, [[$placename, $day, $hour]]);//場所を更新
        }
    }
}

// append to NFC Log spreadsheet
$body = new Google_Service_Sheets_ValueRange([
    'values' => [[$id, $day, $hour, $name, $ts, $objtype, $hash]]
]);
$response = $sheet->spreadsheets_values->append(
    $sheet_id, // 作成したスプレッドシートのIDを入力
    $log_sheet_name, //シート名
    $body, //データ
    ["valueInputOption" => 'USER_ENTERED']
);
$response = $sheet->spreadsheets_values->append(
    $sheet_id, // 作成したスプレッドシートのIDを入力
    $log_sheet_name2, //シート名
    $body, //データ
    ["valueInputOption" => 'USER_ENTERED']
);


// $log_sheet_name の行数が長すぎると処理時間がかかるので、行を削る。
$response = $sheet->spreadsheets_values->get(
    $sheet_id,
    $log_sheet_name.'!A:A'
);
$values = $response->getValues();
if (sizeof($values) > 50) {
    $sid = sheetname2id($sheet, $sheet_id, $log_sheet_name);
    $batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(array(
        'requests' => array(
        'deleteDimension' => array(
            'range' => array(
                'sheetId' => $sid, // the ID of the sheet/tab shown after 'gid=' in the URL
                'dimension' => "ROWS",
                'startIndex' => 1, // row number to delete
                'endIndex' => 10
            )
        )    
        )
    ));
    $result = $sheet->spreadsheets->batchUpdate($sheet_id, $batchUpdateRequest);
}

//var_export($response->getUpdates());

?>

<h1>NFC Tag read</h1>
<table>
    <tbody>
        <tr><td><b>ID:</b></td> <td> </td> <td><?php echo($id); ?></td></tr>
        <tr><td><b>Name:</b></td> <td> </td> <td><?php echo($name); ?></td></tr>
        <tr><td><b>Type:</b></td> <td> </td> <td><?php echo($objtype); ?></td></tr>
        <tr><td><b>Time:</b></td> <td> </td> <td><?php echo($day." ".$hour); ?></td></tr>
        <tr><td><b>Place:</b></td> <td> </td> <td><?php echo($placename); ?></td></tr>
    </tbody>
</table>

<p>
    <a href="https://docs.google.com/spreadsheets/d/<?php echo($sheet_id);?>">Google Sheet</a>
</p>

