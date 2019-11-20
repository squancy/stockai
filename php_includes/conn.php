<?php
	// Database connection
	$conn = mysqli_connect('127.0.0.1', 'USERNAME', 'PASSWORD', 'DB_NAME');
	mysqli_set_charset($conn, "utf8mb4");
	// Connection error handling
	if(mysqli_connect_errno()){
		echo mysqli_connect_error();
		exit();
	}
?>
