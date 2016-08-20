<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
require_once('configlocale.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

if (isset($_POST['addtopcategory'])){

    $connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
    if ($connection->connect_error) die($connection->connect_error);

    $categoryname = sanitizeMySQL($connection, $_POST['categoryname']);
    $error = isempty($categoryname, _("category name"));

    if ($error == ""){
        $query = "INSERT INTO categories (name) VALUES ('$categoryname')" ;
        $result = $connection->query($query);
        if (!$result) die ("Database query error" . $connection->error);
    }
}

if (isset($_POST['addsubcategory'])){

    $connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
    if ($connection->connect_error) die($connection->connect_error);

    $categoryname = sanitizeMySQL($connection, $_POST['topcategory']);
    $subcategoryname = sanitizeMySQL($connection, $_POST['subcategoryname']);

    $error = isempty($categoryname, _("category name"));
    $error .= isempty($subcategoryname, _("subcategory name"));

    if ($error == ""){
        $query = "INSERT INTO categories (ischildof, name) VALUES ('$categoryname','$subcategoryname')" ;
        $result = $connection->query($query);
        if (!$result) die ("Database query error" . $connection->error);
    }
}

if (isset($_POST['deletecategories'])){
    $connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
    if ($connection->connect_error) die($connection->connect_error);

    if (isset($_POST['subcategory'])) {
        $subcat = sanitizeMySQL($connection, $_POST['subcategory']);
        $query = "DELETE FROM categories WHERE category_id = $subcat";
        echo "subcat " . $_POST['subcategory'];
        echo $query;
        $result = $connection->query($query);
        if (!$result) die ("Database delete error" . $connection->error);
    } else {
        // enabled cascading delete in mySQL
        $topcat = sanitizeMySQL($connection, $_POST['topcategory']);
        $query = "DELETE FROM categories WHERE category_id = $topcat";
        echo $query;
        $result = $connection->query($query);
        if (!$result) die ("Database delete error" . $connection->error);
    }

}
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
    <title><?= _('categories admin')?></title>
</head>
<body>
<div class="container">

    <?php include 'nav.php';?>

    <script type="text/javascript">
        document.getElementById('objectstab').className = 'active';
    </script>

    <h1> <?= _('categories admin')?> </h1>
    <div class="row margin-top">
        <div class="col-md-6">
            <?php
            if (isset ( $error ) && $error != "")
                echo "<div class='alert alert-danger'>" . _('error') . ":" . $error . " </div><p>";
            ?>
            <h3><?= _('add top category')?></h3>
            <form method="post" action="categoriesadmin.php">
                <div class="row">
                    <input type="hidden" name="addtopcategory" value="true">
                    <div class="col-md-6">

                        <input type="text" class="form-control" name="categoryname">
                    </div>
                    <div class="col-md-6">

                        <input type="submit" class="btn btn-default" value="<?= _('add category')?>">
                    </div>
                </div>
            </form>

            <h3><?= _('add subcategory')?></h3>
            <form method="post" action="categoriesadmin.php">
                <div class="row">
                    <input type="hidden" name="addsubcategory" value ="true">
                    <div class="col-md-4">
                        <select class="form-control" name="topcategory" size="1">
                            <?php gettopcategories(); ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="subcategoryname">
                    </div>
                    <div class="col-md-4">
                        <input type="submit" class="btn btn-default" value="<?= _('add subcategory')?>">
                    </div>
                </div>
            </form>



            <h3><?= _('delete subcategory')?></h3>
            <form method="post" action="categoriesadmin.php">
                <div class="row">
                    <div class="col-md-3">

                        <select class="form-control col-md-6" name="topcategory" size="1">
                    <?php gettopcategories(); ?>
                </select>
                    </div>

                    <?php
                if (isset($_POST['getsubcategories'])){
                    echo '<div class="col-md-3">';
                    echo '<select class="form-control col-md-6" name ="subcategory" size="1">';
                    getsubcategories($_POST['topcategory']);
                    echo '</select>';
                    echo '</div>';
                }
                ?>

                    <div class="col-md-3">
                <input type="submit" class="btn btn-default" name="getsubcategories" value="<?= _('show subcategory')?>">
                        </div>
                    <div class="col-md-3">
                <input type="submit" class="btn btn-default margin-left" name="deletecategories" value="<?= _('delete category')?>">
                        </div>
                </div>
        </div>
    </div>
    </form>
</div>
</body>
</html>

