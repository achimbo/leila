<?php
require_once 'variables.php';
require_once 'tools.php';

define('FPDF_FONTPATH','fpdf/font/');
require_once 'fpdf/fpdf.php';
require_once 'fpdi/fpdi.php';

session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if ($connection->connect_error) die($connection->connect_error);
if (isset($_GET['ID']) ){
	$uid = sanitizeMySQL($connection, $_GET['ID']);
} else {
	die("missing query");
}

$query = "SELECT * FROM users WHERE user_id = " . $uid;
$result = $connection->query($query);

if (!$result) die ("Database query error" . $connection->error);

$result->data_seek(0);
$row = $result->fetch_array(MYSQLI_ASSOC);
$lastname = utf8_decode($row['lastname']);
$firstname = utf8_decode($row['firstname']);
$street = utf8_decode($row['street']);
$zipcode = utf8_decode($row['zipcode']);
$city = utf8_decode($row['city']);
$email = utf8_decode($row['email']);
$telephone = utf8_decode($row['telephone']);

// initiate FPDI
$pdf = new FPDI();
// add a page
$pdf->AddPage();
// set the source file
$pdf->setSourceFile("member.pdf");
// import page 1
$tplIdx = $pdf->importPage(1);
// use the imported page and place it at point 10,10 with a width of 100 mm
$pdf->useTemplate($tplIdx);

// now write some text above the imported page
$pdf->SetFont('Helvetica', '', 16);
$pdf->SetXY(40, 48);
$pdf->Write(0, "$lastname");
$pdf->SetXY(130, 48);
$pdf->Write(0, "$firstname");
$pdf->SetXY(40, 58);
$pdf->Write(0, "$street");
$pdf->SetXY(40, 68);
$pdf->Write(0, "$zipcode");
$pdf->SetXY(130, 68);
$pdf->Write(0, "$city");
$pdf->SetXY(40, 78);
$pdf->Write(0, "$email");
$pdf->SetXY(130, 78);
$pdf->Write(0, "$telephone");

if ($row['getsnewsletter'] == 1) {
	$pdf->SetXY(140, 209);
	$pdf->Write(0, "X");
} else {
	$pdf->SetXY(175, 209);
	$pdf->Write(0, "X");	
}

$pdf->SetXY(20, 270);
$pdf->Write(0, "Berlin, " . getcurrentdate());


$pdf->Output();

?>