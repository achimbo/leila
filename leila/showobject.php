<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
require_once('configlocale.php');

if ($allowguests == 0 && (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin")) die (_("please <a href='login.php'>login</a>"));

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
	<title><?= _('show object')?></title>
</head>
<body>
<div class="container">
	<?php
	if (isset($_SESSION['usertype']) && $_SESSION['usertype'] == "admin") include 'nav.php';
	?>
	<script type="text/javascript">
		document.getElementById('objectstab').className = 'active';
	</script>
	<div class="col-md-6">
		<h1><?= $row['name']?></h1>
		<a class="img-rounded pull-right" href="showimage.php?ID=<?=$row['object_id']?>"><img src="showimage.php?ID=<?=$row['object_id']?>&showthumb"></a><br>
		<b><?= _('object ID: ')?></b> <?= $row['object_id']?> <p>
			<?php
			foreach (getcategories($oid) as $cat){
				echo '<b>' . _('category: ') . '</b> <a href="listobjects.php?catid=' . $cat['catid'] . '">' . $cat['name'] . '</a><br>';
			}
			?>
			<b><?= _('description: ')?></b> <?= nl2br($row['description'])?><br>
			<b><?= _('shelf: ')?></b> <?= $row['shelf']?><br>
			<b><?= _('date added: ')?></b> <?= $row['dateadded']?> <br>
			<br>
			<?php
			if (isset($_SESSION['usertype']) && $_SESSION['usertype'] == "admin") {
				echo <<<_END
	<a class="btn btn-default" href="editobject.php?ID=$oid"><b>Objekt Editieren</b></a><p>
	<a class="btn btn-default" href="lendobject.php?objectid=$oid"><b>Objekt verleihen</b></a><p>
_END;

				$rentals = getrentalsbyobject($oid);
				echo "<table id='rentallist' class='margin-top table table-bordered table-striped'>";
				switch (objectisavailable($oid)) {
					case -1:
						echo "<caption><div class='invalid'>Falscher Status</div></caption>";
						break;

					case 0:
						echo "<caption><div class='invalid'>Objekt verliehen</div></caption>";
						break;

					case 1:
						echo "<caption><div class='valid'>Objekt verleihbar</div></caption>";
						break;
				}
				echo "<thead><tr><th>Username</th><th>Von</th><th>Bis</th><th>Zur&uuml;ck</th><th>Kommentar</th></thead>";

				foreach ($rentals as $rent) {
					echo "<tr><td><a href='editmember.php?ID=" . $rent['userid'] . "'>" . $rent['firstname'] . " " . $rent['lastname'] . "</a></td>";
					echo "<td><a href='lendobject.php?edit=1&userid=" . $rent['userid'] . "&objectid=" . $oid . "&loanedout=" . $rent['loanedout'] . "'>". $rent['loanedout'] . "</a></td>" ;
					echo "<td>" . $rent['duedate'] . "</td><td>" . $rent['givenback'] . "</td><td>" . $rent['comment'] . "</td></tr>";
				}
				echo "</table>";
			} else {
				switch (objectisavailable($oid)) {
					case -1:
						echo "<span class='invalid'>Falscher Status</span>";
						break;

					case 0:
						echo "<span class='invalid'>Objekt verliehen</span>";
						break;

					case 1:
						echo "<span class='valid'>Objekt verleihbar</span>";
						break;
				}
			}

			?>
	</div>
</div>
</body>
</html>