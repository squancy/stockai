<?php
    require_once '../php_includes/conn.php';
    require_once '../php_includes/stock.php';
    
    // Send an email to every subscriber if any of the stock prices has changed at least +/- 2%
    
    $data = getDataStock();
    $pieces = explode("|||", $data);
    
    // -1 cuz we have a ||| at the end as well
    for($i = 0; $i < count($pieces) - 1; $i++){
        $final = json_decode($pieces[$i], true);
        $open = intval($final['open']);
        $current = intval($final['last']);
        $stock = $final['ticker'];
        if(($current / ($open / 100) >= 102) || ($current / ($open / 100) <= 98)){
            date_default_timezone_set('Europe/Budapest');
            $dateNow = new DateTime(date('Y-m-d H:i:s'));
            $dateClose = new DateTime(date('Y-m-d 17:10:00'));
            if($dateNow > $dateClose){
                // Send email
                $change = number_format(floatval($current / ($open / 100)), 2);
                if($change < 100){
                    $change = '<span style="color: red">'.($change - 100).'%</span>';
                }else{
                    $change = '<span style="color: green">+'.($change - 100).'%</span>';
                }
                $sql = "SELECT email FROM stockai_email";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $e = $row["email"];
                    $to = "$e";
        			$from = "Stock AI <stockai@pearscom.com>";
        			$subject = 'Stock AI - '.$stock;
        			$message = '<!DOCTYPE html>
                    <html>
                           <head>
                                  <meta charset="UTF-8">
                                  <title>Stock AI - '.$stock.'</title>
                           </head>
                           <style type="text/css">
                                  div > a:hover, a{
                                         text-decoration: none;
                                  }
                    
                                  #link:hover{
                                         background-color: #ab0000;
                                  }
                    
                                  @media only screen and (max-width: 768px){
                                         #atp{
                                                width: 100% !important;
                                         }
                                  }
                           </style>
                           <body style="font-family: Arial, sans-serif; background-color: #fafafa; box-sizing: border-box; margin: 0 auto; margin-top: 10px; max-width: 800px;">
                                <p>Hello, '.$stock.' has changed '.$change.'. <a href="https://www.pearscom.com/stockai">Check it now!</a></p>
                           </body>
                    </html>       ';
        			$headers = "From: $from\n";
        	        $headers .= "MIME-Version: 1.0\n";
        	        $headers .= "Content-type: text/html; charset=iso-8859-1\n";
        	        
        			mail($to, $subject, $message, $headers);
                }
            }
        }
    }
?>
