<?php
require_once 'variables.php';

$mylist = '';

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);

if ($connection->connect_error) die($connection->connect_error);

$query = "SELECT * FROM objects";
$result = $connection->query($query);

if (!$result) die ("Database query error" . $connection->error);

$rows = $result->num_rows;

for ($r = 0; $r < $rows; ++$r) {
	$result->data_seek($r);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	
	$mylist .= 'Name <a href="showobject.php?ID=' .$row['ID'] . '">' . $row['name'] . '</a><br>';
	//$mylist .= 'Description ' . $row['description'] . '<br>';
}

?>

<html>
<body>
<?= $mylist?>
</body>
</html>