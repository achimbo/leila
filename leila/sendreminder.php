<?php

// call daily via cron job, send email when item is due in three days

require_once 'variables.php';

session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

$connection = new mysqli ( $db_hostname, $db_username, $db_password, $db_database );
if ($connection->connect_error)
	die ( $connection->connect_error );

$query = "SELECT r.duedate, o.name AS objectname, u.firstname, u.lastname, u.email 
		FROM rented r INNER JOIN objects o ON o.ID = r.objects_ID INNER JOIN users u ON u.ID = r.users_ID 
		WHERE DATEDIFF(duedate, curdate()) = 3  AND r.givenback IS NULL AND u.email != '' ";

$result = $connection->query($query);
if (!$result) die ("Database query error" . $connection->error);
$rows = $result->num_rows;

$subject = "Eine Erinnerung vom Leihladen";
$headers = "From: info@leihladen.at\r\n";
$headers .= "Mime-Version: 1.0\r\n";
$headers .= "Content-type: text/plain; charset=utf-8\r\n";

for ($r = 0; $r < $rows; ++$r) {
	$result->data_seek($r);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	
	$message = "Hallo {$row['firstname']} \n
Eine kleine Erinnerung: Du hast dir ein(e) {$row['objectname']} im Leihladen ausgeborgt und solltest es bis {$row['duedate']} zurückgeben. \n
Liebe Grüße Leihladen Wien\n";
	
	if (mail($row['email'], $subject, $message, $headers)) {
		echo date('Y-m-d G:i:s', time()) . " Email sent to {$row['email']}\n";
	} else {
		echo date('Y-m-d G:i:s', time()) . " Email to {$row['email']} failed\n";
	}
}