<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, user-scalable=no">
<link rel="stylesheet" href="/style.css">
<title>NFC Tag Registration</title>
</head>
<body>

<h2>Copy the following URL and write to an NFC tag using 'NFC Tools'</h2>

<?php

include("./settings.php");
include("./common.php");



$id="x".strtoupper(dechex(time()-1672099294));
$name=$_POST['name'];
$objtype=$_POST['type'];
switch ($objtype) {
    case 'object':
        $obj = 'o';
        break;
    case 'box':
        $obj = 'b';
        break;
    case 'place':
        $obj = 'p';
        break;
    case 'room':
        $obj = 'r';
        break;
    default:
        $obj = 'x';
        brak;
}

$url = $base_url."/r.php?i=".$id."&t=".$obj."&n=".urlencode($name);

echo($url."</p>");

// setup Google Spreadsheet handler
require __DIR__. '/vendor/autoload.php';
$keyFile = __DIR__. "/credentials.json";
$sheet = setupGoogleSheet($keyFile);

date_default_timezone_set('Asia/Tokyo');
$ts = time();
$objDateTime = new DateTime();
$day = $objDateTime->format('Y/m/d'); //2019/05/21
$hour = $objDateTime->format('H:i:s'); //13:33:59
$hash = getHostHash();

// append to Object Spreadsheet
$body = new Google_Service_Sheets_ValueRange([
    'values' => [[$id, $day, $hour, $name, $objtype, $hash]]
]);
$response = $sheet->spreadsheets_values->append(
    $sheet_id,
    $obj_sheet_name, //range
    $body, // data
    ["valueInputOption" => 'USER_ENTERED']
);


?>

<p>
    <input id="copyTarget" type="text" value="<?php echo($url)?>" readonly><br>
    <button onclick="copyToClipboard()">Copy URL</button>

    <p>
    <!-- copy to clicpboard -->
    <script>
        function copyToClipboard() {
            var copyTarget = document.getElementById("copyTarget");
            copyTarget.select();
            document.execCommand("Copy");
            alert("URL Copied.\n\nWrite the copied URL to the NFC tag.");
        }
    </script>
</body>
