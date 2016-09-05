<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
require_once('configlocale.php');

if ($allowguests == 0 && (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin")) die (_("please <a href='login.php'>login</a>"));


$mylist = '';
$message = '';

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if ($connection->connect_error) die($connection->connect_error);

if (isset($_GET['catid']) ){
	$catid = sanitizeMySQL($connection, $_GET['catid']);
};

if (isset($_GET['searchstring']) ){
	$searchstring = sanitizeMySQL($connection, $_GET['searchstring']);
};

if (isset($_GET['searchid']) ){
	$searchid = sanitizeMySQL($connection, $_GET['searchid']);
};

if (isset($catid) ){
	$query = "SELECT COUNT(*) AS count FROM objects o
	INNER JOIN objects_has_categories ohc ON o.object_id = ohc.object_id		
    INNER JOIN categories c on ohc.category_id = c.category_id 
	WHERE ohc.category_id = $catid OR c.ischildof = $catid ORDER BY o.name";
	$message = _("in category ") . getcategoryname($catid);
	$result = $connection->query($query);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$count = $row['count'];
	$pag = paginate($count);

	$query = "SELECT o.* FROM objects o
	INNER JOIN objects_has_categories ohc ON o.object_id = ohc.object_ID
	INNER JOIN categories c on ohc.category_id = c.category_id
	WHERE ohc.category_id = $catid OR c.ischildof = $catid ORDER BY o.name" . $pag['query'];
} elseif (isset($searchstring)){
	$query = "SELECT COUNT(*) AS count FROM objects WHERE (name LIKE '%$searchstring%') OR (description LIKE '%$searchstring%') ORDER BY name";
	$message = _("with content ") . $searchstring;
	$result = $connection->query($query);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$count = $row['count'];
	$pag = paginate($count);

	$query = "SELECT * FROM objects WHERE (name LIKE '%$searchstring%') OR (description LIKE '%$searchstring%') ORDER BY name" . $pag['query'];
} elseif (isset($searchid)) {
	$query = "SELECT * FROM objects WHERE object_id = '$searchid'";
	$pag['footer'] = "";
	$message = _("with ID ") . $searchid;
} else {
	$query = "SELECT COUNT(*) AS count FROM objects;";
	$result = $connection->query($query);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$count = $row['count'];
	$pag = paginate($count);

	$query = "SELECT * FROM objects ORDER BY name" . $pag['query'];
}


// echo $query;
$result = $connection->query($query);
if (!$result) die (_("Database query error") . $connection->error);
$rows = $result->num_rows;

$mylist .= "<table id='objectslist' class='margin-top table table-bordered table-striped'>";

for ($r = 0; $r < $rows; ++$r) {
	$result->data_seek($r);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	if (objectisavailable($row['object_id']) < 1 ) {
		$class = "class=unavailable";
	} else {
		$class = "class=available";
	}

	$mylist .= '<tr ' . $class . '><td><a href="showobject.php?ID=' .$row['object_id'] . '">' . $row['name'] . '
			<img src="showimage.php?ID=' . $row['object_id'] . '&amp;showthumb" class="img-rounded pull-right" alt="object image"></a></td></tr> ';
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
	<title><?= _('list objects')?></title>
</head>
<body>

<div class="container">
	<?php
	if (isset($_SESSION['usertype']) && $_SESSION['usertype'] == "admin") include 'nav.php';
	?>
	<script type="text/javascript">
		document.getElementById('objectstab').className = 'active';
	</script>

	<h1><?= _('list objects')?></h1>

	<div class="row margin-top">
		<div class="col-md-6">

			<h3><?= _('search categories')?></h3>
			<?php echo "<div id='cats'>";
			echo "<a href='listobjects.php'>Alle </a>";
			getcategoriesaslinks();
			echo "</div>";
			?>
			<form method="get" class="margin-top" action="listobjects.php">

				<div class="input-group">
					<input type="text" class="form-control" id="searchstring" name="searchstring" placeholder="<?= _('search in description and title')?>">
					<span class="input-group-btn">
			<input type="submit" class="btn btn-default" value="<?= _('search')?>">
		</span>
				</div>
			</form>

			<form method="get" action="listobjects.php">

				<div class="input-group margin-top">
					<input type="text" class="form-control" id="searchid" name="searchid" placeholder="<?= _('search ID')?>">
					<span class="input-group-btn">
			<input type="submit" class="btn btn-default" value="<?= _('search ID')?>">
		</span>
				</div>
			</form>
			<h3 class="margin-top"><?= _('list objects') . ' ' . $message ?></h3>
			<?= $mylist?>
			<?= $pag['footer']?>

		</div>
	</div>
</div>
</body>
</html>