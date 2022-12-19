<?php
  class connection{

  // define a variable for the database connection
  private $conn;

  // when an instance of 'connection' class is created a connection is made through mysqli
  public function __construct()
  {
    $servername = "localhost";
    $username = "voz";
    $password = "2r5sT6bjeCGVbeUe";
    $database="voz";

	// the connection is stored inside the private variable
    $this->conn = new mysqli($servername, $username, $password,$database);

    // Check connection
    if ($this->conn->connect_error) {
        die("Connection failed: " . $this->conn->connect_error);
        return false;
    } else{
        return true;
    }       
  }

  // method used to send a query to database
  public function query($sql)
  {
    // here you use the connection made on __construct() and apply the method query. Basically we are using inside our method (called query) a method (call query too) from the mysqli class
    return $this->conn->query($sql);

  }
	
  public function real_escape_string($string){
	return $this->conn->real_escape_string($string);
  }

}
?>