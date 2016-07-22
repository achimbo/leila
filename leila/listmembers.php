<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
require_once('configlocale.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

$mylist = '';
$message = '';

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if ($connection->connect_error) die($connection->connect_error);

if (isset($_GET['searchstring'])){
	$searchstring = sanitizeMySQL($connection, $_GET['searchstring']);
	$query = "SELECT COUNT(*) AS count FROM users WHERE CONCAT(firstname, ' ', lastname) LIKE '%$searchstring%' ORDER BY lastname";
	$result = $connection->query($query);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$count = $row['count'];
	$pag = paginate($count);
	$message = _("with name ") . $searchstring;

	$query = "SELECT * FROM users WHERE CONCAT(firstname, ' ', lastname) LIKE '%$searchstring%' ORDER BY lastname" . $pag['query'];

} elseif (isset($_GET['showadmins'])) {
	$query = "SELECT COUNT(*) AS count FROM users WHERE usertype = 1 ORDER BY lastname";
	$result = $connection->query($query);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$count = $row['count'];
	$pag = paginate($count);
	$message = _("which are administrators");

	$query = "SELECT * FROM users WHERE usertype = 1 ORDER BY lastname" . $pag['query'];

} elseif (isset($_GET['searchid'])) {
	$searchid = sanitizeMySQL($connection, $_GET['searchid']);
	$query = "SELECT * FROM users WHERE user_id = '$searchid'";
	$message = _("with id ") . $searchid;
	$pag['footer'] = "";
} else {
	$query = "SELECT COUNT(*) AS count FROM users;";
	$result = $connection->query($query);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$count = $row['count'];
	$pag = paginate($count);

	$query = "SELECT * FROM users ORDER BY lastname" . $pag['query'];
}

$result = $connection->query($query);
if (!$result) die ("Database query error" . $connection->error);
$rows = $result->num_rows;

$mylist .= "<table class='margin-top table table-bordered table-striped'>";

for ($r = 0; $r < $rows; ++$r) {
	$result->data_seek($r);
	$row = $result->fetch_array(MYSQLI_ASSOC);

	$mylist .= "<tr><td><a href='editmember.php?ID=" . $row['user_id'] . "'>" . $row['firstname'] . " " . $row['lastname'] . "</a></td></tr>\n";

	// $mylist .= "<tr><td> Name <a href='showmember.php?ID=' .$row['user_id'] > $row['firstname'] . </a></td></tr> ";
	//$mylist .= 'Description ' . $row['description'] . '<br>';
}

$mylist .= "</table>";
?>

<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="leila-new.css"  type="text/css">
	<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css"  type="text/css">
	<link rel="stylesheet" href="bootstrap/css/bootstrap-theme.min.css" type="text/css">
	<link rel="stylesheet" href="jquery-ui/jquery-ui.min.css">
	<script src="jquery/jquery.js"></script>
	<script src="bootstrap/js/bootstrap.min.js"></script>
	<script src="jquery-ui/jquery-ui.min.js"></script>



	<meta charset="utf-8"/>
	<title><?= _('list members')?></title>
</head>

<body>
<div class="container">

	<?php include 'nav.php';?>

	<script type="text/javascript">
		document.getElementById('memberstab').className = 'active';
		document.getElementById('objectspane').className = 'tab-pane';
		document.getElementById('memberspane').className = 'tab-pane active';
	</script>

	<h1><?= _('list members')?></h1>

	<div class="row margin-top">
		<div class="col-md-6">
			<form method="get" action="listmembers.php">

				<div class="input-group">
					<input type="text" class="form-control" id="searchstring" name="searchstring" placeholder="<?= _('search in name')?>">
					<span class="input-group-btn">
						<input type="submit" class="btn btn-default" value="<?= _('search')?>">
					</span>
				</div>
			</form>

			<form method="get" action="listmembers.php">

				<div class="input-group margin-top">
					<input type="text" class="form-control" id="searchid" name="searchid" placeholder="<?= _('search for id')?>">
					<span class="input-group-btn">
						<input type="submit" class="btn btn-default" value="search id">
					</span>
				</div>
			</form>
			<form method="get" action="listmembers.php">
				<input type="submit" class="margin-top btn btn-default" name="showadmins" value="<?= _('show admins')?>">
			</form>
		</div>
	</div>


	<h3>Mitglieder <?= $message?></h3>
	<div class="col-md-9">
		<?= $mylist?>
		<?= $pag['footer']?>
	</div>

</div>

</body>
</html>