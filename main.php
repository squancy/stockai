<?php
    require_once 'php_includes/conn.php';
    require_once 'php_includes/stock.php';

    // Get stock data as a JSON file from an outer resource (Portfolio)
    if(isset($_POST["refresh"]) && $_POST["refresh"] == "now"){
        $html = getDataStock();
		echo $html;
		exit();
	}
	
	// Validate email address on server side
	if(isset($_POST['email_addr'])){
	    $email = $_POST['value'];
	    $emailErr = "";
	    
	    $sql = "SELECT COUNT(id) FROM stockai_email WHERE email = ?";
    	$stmt = $conn->prepare($sql);
    	$stmt->bind_param("s",$email);
    	$stmt->execute();
    	$stmt->bind_result($emailCount);
    	$stmt->fetch();
    	$stmt->close();
    	
	    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format.";
        }else if($emailCount > 0){
            $emailErr = "Email already exists.";
        }else{
            $sql = "INSERT INTO stockai_email(email, count, time) VALUES(?,0,NOW())";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("s",$email);
			$stmt->execute();
			$stmt->close();	
            echo "success";
            exit();
        }
        echo $emailErr;
	    exit();
	}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Stock AI</title>
        
        <meta lang="en">
		<link rel="stylesheet" type="text/css" href="/style/style_ai.css">
        <link rel="icon" type="image/x-icon" href="/images/favstock.png">
        <meta name="description" content="An open-source service for analysing the most important Hungarian stocks.">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        	  <link rel="manifest" href="/manifest.json">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="#282828">
        <meta name="apple-mobile-web-app-title" content="Pearscom">
        <link rel="apple-touch-icon" href="/images/icons/icon-152x152.png">
        <meta name="theme-color" content="#282828" />
    </head>
    <body>
		<header>
			<a href="/main.php">Stock AI</a>
			<div class="headerRight">
				<a href="/downloads.php">Downloads</a>
				<a href="/cont.php">Contribute</a>
				<a href="/about.php">Information</a>
			</div>
		</header>
		<br><br><br><br>
		<span id="emailText"></span>
		<div id="outerEmail"></div>
		<div id="status"></div>
        <div class="hStocks" id="hStocks"></div>
		<?php require_once 'template_pageBottom.php'; ?>
        <script src="/analysis.js" type="text/javascript"></script>
        <script src="/email.js" type="text/javascript"></script>
    </body>
</html>
