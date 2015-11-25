<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

$connection = new mysqli ( $db_hostname, $db_username, $db_password, $db_database );
if ($connection->connect_error)
	die ( $connection->connect_error );

$message = "";
$mylist = '';

if (isset($_GET['showoverdue'])) {
	$query = "SELECT COUNT(*) AS count FROM rented WHERE DATEDIFF(duedate, curdate()) < 0  AND givenback IS NULL";
	$result = $connection->query($query);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$count = $row['count'];
	$pag = paginate($count);	
	
	$query = "SELECT o.object_id AS objectid, o.name, u.user_id AS userid, u.firstname, u.lastname, r.loanedout, r.duedate, r.givenback
		 FROM objects o INNER JOIN rented r ON o.object_id = r.object_id INNER JOIN users u on r.user_id = u.user_id 
			WHERE DATEDIFF(duedate, curdate()) < 0  AND givenback IS NULL ORDER BY r.loanedout ASC " . $pag['query'];
	$message = "die &uuml;berzogen sind";
} elseif (isset($_GET['showrented'])) {
	$query = "SELECT COUNT(*) AS count FROM rented WHERE givenback IS NULL ";
	$result = $connection->query($query);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$count = $row['count'];
	$pag = paginate($count);
	
	$query = "SELECT o.object_id AS objectid, o.name, u.user_id AS userid, u.firstname, u.lastname, r.loanedout, r.duedate, r.givenback
		 FROM objects o INNER JOIN rented r ON o.object_id = r.object_id INNER JOIN users u on r.user_id = u.user_id
			WHERE givenback IS NULL ORDER BY r.loanedout ASC " . $pag['query'];
	$message = "die gerade verliehen sind";
} elseif (isset($_GET['datefrom']) && isset($_GET['dateuntil'])) {
	$from = sanitizeMySQL($connection, $_GET['datefrom']);
	$until = sanitizeMySQL($connection, $_GET['dateuntil']);
	if (datepresent($from) == "" && datepresent($until) == "") {
		
	$query = "SELECT COUNT(*) AS count FROM rented WHERE loanedout BETWEEN CAST('$from' AS DATE) AND CAST('$until' AS DATE) ";
	$result = $connection->query($query);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$count = $row['count'];
	$pag = paginate($count);
	
	$query = "SELECT o.object_id AS objectid, o.name, u.user_id AS userid, u.firstname, u.lastname, r.loanedout, r.duedate, r.givenback
		 FROM objects o INNER JOIN rented r ON o.object_id = r.object_id INNER JOIN users u on r.user_id = u.user_id
					WHERE loanedout BETWEEN CAST('$from' AS DATE) AND CAST('$until' AS DATE) ORDER BY r.loanedout ASC " . $pag['query'];	
	$message = "die zwischen $from und $until verliehen wurden";
	} else {
		$query = "SELECT COUNT(*) AS count FROM rented";
		$result = $connection->query($query);
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$count = $row['count'];
		$pag = paginate($count);
		
		$error = "Datum fehlerhaft";
		
		$query = "SELECT o.object_id AS objectid, o.name, u.user_id AS userid, u.firstname, u.lastname, r.loanedout, r.duedate, r.givenback
		 FROM objects o INNER JOIN rented r ON o.object_id = r.object_id INNER JOIN users u on r.user_id = u.user_id ORDER BY r.loanedout ASC " . $pag['query'];
	}
} else {
	$query = "SELECT COUNT(*) AS count FROM rented";
	$result = $connection->query($query);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$count = $row['count'];
	$pag = paginate($count);
	
	$query = "SELECT o.object_id AS objectid, o.name, u.user_id AS userid, u.firstname, u.lastname, r.loanedout, r.duedate, r.givenback
		 FROM objects o INNER JOIN rented r ON o.object_id = r.object_id INNER JOIN users u on r.user_id = u.user_id ORDER BY r.loanedout ASC " . $pag['query'];
}

$result = $connection->query($query);
if (!$result) die ("Database query error" . $connection->error);
$rows = $result->num_rows;

$mylist .= "<table id='rentedlist'>";
$mylist .= "<tr><th>Objekt</th><th>User</th><th>Ausgeliehen</th><th>F&auml;llig</th><th>Zur&uuml;ckgegeben</th></tr>";

for ($r = 0; $r < $rows; ++$r) {
	$result->data_seek($r);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	
	$mylist .= '<tr><td><a href="showobject.php?ID=' . $row["objectid"] . '">' .$row['name'] . '</a></td>'
			. '<td><a href="editmember.php?ID=' . $row['userid'] . '">' . $row['firstname'] . ' ' .  $row['lastname'] . '</a></td>'
	. '<td><a href="lendobject.php?edit=1&objectid=' . $row['objectid'] . '&userid=' . $row['userid'] . '&loanedout=' . $row['loanedout'] . '">' . $row['loanedout']  . '</a></td>' 
	. '<td>' . $row['duedate'] . '</td><td>' . $row['givenback'] . '</td></tr> ' . "\n";
	//$mylist .= 'Description ' . $row['description'] . '<br>';
}

$mylist .= "</table>";
?>

<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="leila.css" type="text/css">
	<link rel="stylesheet" href="jquery-ui/jquery-ui.min.css">
<title>Verleih &Uuml;bersicht</title>
</head>
<body>
<script src="jquery/jquery.js"></script>
<script src="jquery-ui/jquery-ui.min.js"></script>

<?php include 'menu.php';?>
<div id="content">
<h1>Verleih &Uuml;bersicht</h1>
<?php if (isset ( $error ) && $error != "") echo "<span class='errorclass'>Fehler: $error </span>" ?>
<form method="get" action="listlendedobjects.php">
	<label for="datefrom">Datum Von: &#x1f4c5;</label>
	<input type="text" id="datefrom" name="datefrom"><br>
	<script type="text/javascript">
		$( "#datefrom" ).datepicker({
			dateFormat: "yy-mm-dd",
			firstDay: 1,
			defaultDate: -365,
			changeYear: true
		});						
	</script>
	<label for="dateuntil">Datum Bis: &#x1f4c5;</label>
	<input type="text" id="dateuntil" name="dateuntil">	
	<script type="text/javascript">
		$( "#dateuntil" ).datepicker({
			dateFormat: "yy-mm-dd",
			firstDay: 1,
			changeYear: true
		});				
	</script>
	<input type="submit" value="Suchen">
</form><p>
<form>
	<input type="submit" name="showrented" value="Verliehene anzeigen">
	<input type="submit" name="showoverdue" value="&Uuml;berzogene anzeigen">
</form><p>
<h3>Verliehene Objekte <?=$message?></h3>

<?= $mylist?>
<?= $pag['footer']?>

</div>
</body>
</html>