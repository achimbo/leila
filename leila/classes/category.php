<?php
class Category implements JsonSerializable{
	// $id can only be set during instantiation, afterwards read access only
	private $id = NULL;
	// access only through getter and setter, because this requires a DB write
	private $name = '';
	// access only through addKid() and getKids()
	private $kids = array();

	// save to DB and get id
	function __construct() {
		include '../variables.php';
		$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
		if ($connection->connect_error) die($connection->connect_error);
		
		$query = "INSERT INTO categories (name) VALUES ('')" ;
		$result = $connection->query($query);
		if (!$result) die ("Database query error" . $connection->error);
		
		$this->id = mysqli_insert_id($connection);
	}
	
	// read from DB
	public static function withId ($id) {
		include '../variables.php';
		$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
		
		if ($connection->connect_error) die($connection->connect_error);
		$query = "SELECT * FROM categories WHERE category_id = $id";
		$result = $connection->query($query);
		if (!$result) die ("Database query error" . $connection->error);
		
		if ($result->num_rows == 0) {
			$connection->close();
			return NULL;
		}
		
		$result->data_seek ( 0 );
		$row = $result->fetch_array ( MYSQLI_ASSOC );
		
		$category = new self();
		$category->id = $row['category_id'];
		$category->name = $row['name'];
		Category::generateLineage($category);
		return $category;
	}
	
	// Set name, save to DB and get id
	public static function withName($name) {
		include '../variables.php';
		$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
		if ($connection->connect_error) die($connection->connect_error);
		
		$query = "INSERT INTO categories (name) VALUES ('$name')" ;
		$result = $connection->query($query);
		if (!$result) die ("Database query error" . $connection->error);
		
		$category = new self();
		$category->id = mysqli_insert_id($connection);
		$category->name = $name;
		return $category;
	}
	
	// create new Category, no DB save, used only by getDirectKids() and addKid() hence private
	private static function withIdAndName($id, $name) {
		$category = new self();
		$category->id = $id;
		$category->name = $name;
		return $category;
	}
	
	// return $id
	function getId() {
		return $this->id;
	}
	
	// return $name
	function getName() {
		return $this->name;
	}
	
	// Set name and save to DB
	function setName($name) {
		$this->name = $name;
		
		include '../variables.php';
		$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
		if ($connection->connect_error) die($connection->connect_error);
		
		$query = "INSERT INTO categories (name) VALUES ('$name') WHERE category_id = '$this->id'" ;
		$result = $connection->query($query);
		if (!$result) die ("Database query error" . $connection->error);
		
	}
	
	// return $kids
	function getKids() {
		return $this->kids;
	}
	
	// Save to DB, and add kid
	function addKid($name) {
		include '../variables.php';	
		$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
		if ($connection->connect_error) die($connection->connect_error);

		$query = "INSERT INTO categories (ischildof, name) VALUES ('$this->id','$name')" ;
		$result = $connection->query($query);
		if (!$result) die ("Database query error" . $connection->error);
	
		$kid = Category::withIdAndName(mysqli_insert_id($connection), $name);
		$this->kids[] = $kid;
	}
	
	// return direct kids of $c, if there are no kids return NULL.
	// return topcategories if passed a $cat with id = 0
	static function getDirectKids(Category $cat) {
		include '../variables.php';
		$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
		
		if ($connection->connect_error) die($connection->connect_error);
		if ($cat->id == 0) {
			$query = "SELECT * FROM categories WHERE ischildof IS NULL";
		} else {
			$query = "SELECT * FROM categories WHERE ischildof = $cat->id";
		}
		$result = $connection->query($query);
		if (!$result) die ("Database query error" . $connection->error);
		$rows = $result->num_rows;
		
		if ($rows == 0) {
			$connection->close();
			return array();
		}
		
		for ($r = 0; $r < $rows; ++$r) {
			$result->data_seek($r);
			$row = $result->fetch_array(MYSQLI_ASSOC);
			$kid = Category::withIdAndName($row['category_id'], $row['name']);
			$kids[] = $kid;
		}
		$connection->close();
		return $kids;
	}
	
	// recursively seek for kids and append them to $cat->kids, passing by reference!
	static function generateLineage(Category &$cat) {
		$kidsarray = Category::getDirectKids($cat);
		foreach ($kidsarray as $kid) {
			if (Category::getDirectKids($kid) === NULL) {
				$cat->kids[] = $kid;
			} else {
				Category::generateLineage($kid);
				$cat->kids[] = $kid;
			}
		}
	}
	
	public function jsonSerialize() {
		
		return array('id' => $this->getId(), 'name' => $this->getName(), 'kids' => $this->getKids());
	}
	
}


$haushalt = Category::withId(1);

print_r($haushalt);

echo "<p>";

echo json_encode($haushalt)

?>