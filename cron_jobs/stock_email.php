<?php
    require_once '../php_includes/conn.php';
    require_once '../php_includes/stock.php';
    
    // Send an email to every subscriber if any of the stock prices has changed at least +/- 2%
    
    function isTodayWeekend() {
        $currentDate = new DateTime("now", new DateTimeZone("Europe/Budapest"));
        return $currentDate->format('N') >= 6;
    }
    
    $data = getDataStock();
    $pieces = explode("|||", $data);
    $dateNow = new DateTime(date('Y-m-d H:i:s'));
    $dateClose = new DateTime(date('Y-m-d 17:10:00'));
    $dateOpen = new DateTime(date('Y-m-d 09:00:00'));
    if($dateNow > $dateClose){
        $zero = 0;
        $sql = "UPDATE stockai_email SET count=?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i",$zero);
		$stmt->execute();
		$stmt->close();
		exit();
    }
    // -1 cuz we have a ||| at the end as well
    for($i = 0; $i < count($pieces) - 1; $i++){
        $final = json_decode($pieces[$i], true);
        $open = intval($final['open']);
        $current = intval($final['last']);
        $stock = $final['ticker'];
        if((($current / ($open / 100) >= 102) || ($current / ($open / 100) <= 98)) && !isTodayWeekend($dateNow)){
            date_default_timezone_set('Europe/Budapest');
            if($dateNow >= $dateOpen && $dateNow <= $dateClose){
                // Send email
                $change = number_format(floatval($current / ($open / 100)), 2);
                if($change < 100){
                    $change = '<span style="color: red">'.($change - 100).'%</span>';
                }else{
                    $change = '<span style="color: green">+'.($change - 100).'%</span>';
                }
                $sql = "SELECT email, count FROM stockai_email WHERE count < 7";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $e = $row["email"];
                    $cnt = $row["count"];
                    $cnt++;
                    $to = "$e";
        			$from = "Stock AI <stockai@pearscom.com>";
        			$subject = 'Stock AI - '.$stock;
        			$message = '
        			<!DOCTYPE html>
                    <html>
                           <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                                <title>Stock AI - '.$stock.'</title>
                    		    <link rel="stylesheet" type="text/css" href="https://www.pearscom.com/style/style_ai.css">
                    		    <style>
                    		        a, a:hover{
                    		            color: red;
                    		            text-decoration: none;
                    		        }
                    		    </style>
                           </head>
                    
                           <body>
                               <header>
                    			<a href="https://www.pearscom.com/stockai">Stock AI</a>
                    		    </header>
                    		    <br><br><br><br>
                    		    <div class="hStocks" id="hStocks" style="text-align: center;">
                    		        <h1>Big movers of the day</h1>
                                    <p>'.$stock.' has changed '.$change.'. <a href="https://www.pearscom.com/stockai">Check it now!</a></p>
                                </div>
                           </body>
                    </html>';
                    $sql = "UPDATE stockai_email SET count=? WHERE email=? LIMIT 1";
            		$stmt = $conn->prepare($sql);
            		$stmt->bind_param("is",$cnt,$e);
            		$stmt->execute();
            		$stmt->close();
        			$headers = "From: $from\n";
        	        $headers .= "MIME-Version: 1.0\n";
        	        $headers .= "Content-type: text/html; charset=iso-8859-1\n";
        	        
        			mail($to, $subject, $message, $headers);
                }
            }
        }
    }
?>
