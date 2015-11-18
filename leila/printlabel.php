<?php
require_once 'variables.php';
require_once 'tools.php';

define('FPDF_FONTPATH','fpdf/font/');
require_once 'fpdf/fpdf.php';

session_start();
if ($allowguests == 0 && (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin")) die ("Bitte <a href='login.php'>anmelden</a>");

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if ($connection->connect_error) die($connection->connect_error);
if (isset($_GET['ID']) ){
	$oid = sanitizeMySQL($connection, $_GET['ID']);
} else {
	die("missing query");
}

$query = "SELECT * FROM objects WHERE object_id = " . $oid;
$result = $connection->query($query);

if (!$result) die ("Database query error" . $connection->error);

$result->data_seek(0);
$row = $result->fetch_array(MYSQLI_ASSOC);
$name = utf8_decode($row['name']);  

$pdf = new FPDF();
$pdf->SetMargins(2,5);
$pdf->SetAutoPageBreak(false, 2);
$pdf->AddPage('L', array(27,88));
$pdf->Image('logo.png', 53, 3);
$pdf->SetFont('Arial','b',14);
$pdf->Cell(3,3,"$name");
$pdf->Ln(7);
$pdf->Cell(3,3,"InvNr {$row['object_id']}");
$pdf->Ln(7);
$pdf->Cell(0,0,"Regal {$row['shelf']}");
$pdf->Output();
?>