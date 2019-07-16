<?php
    // Get stock data as a JSON file from an outer resource (Portfolio)
    if(isset($_POST["refresh"])){

		// ***OLD AND DEPRICATED CODE***	

        /*$arr = array();

        // Create an array with the names of the most important Hungarian stocks
        $stockNames = ["4IG", "ESTMEDIA", "FUTURAQUA", "MOL", "MTELEKOM", "WABERERS"];

        // Dynamically get the content of the JSON file
        for($i = 0; $i < count($stockNames); $i++){
			$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
            $content = file_get_contents('https://data.portfolio.hu/all/json/'.$stockNames[$i].':interval=1M',false,$context);
            array_push($arr, $content);
        }

        // Output the result of the array as chunks of strings separated by a specific delimiter (|||) since ajax cannot handle arrays
        foreach ($arr as $key) {
            echo $key."|||";
        }
        exit();	
    */

		// ***OLD AND DEPRICATED CODE***
	
		// Gather URLs into an array
		$aURLs = array("https://data.portfolio.hu/all/json/4IG:interval=1M", 
					"https://data.portfolio.hu/all/json/MOL:interval=1M",
					"https://data.portfolio.hu/all/json/ESTMEDIA:interval=1M",
					"https://data.portfolio.hu/all/json/FUTURAQUA:interval=1M",
					"https://data.portfolio.hu/all/json/WABERERS:interval=1M",
					"https://data.portfolio.hu/all/json/MTELEKOM:interval=1M");

    	// Initialize curl for async (multi) use	
		$mh = curl_multi_init();
   
		// Create array for curl handlers 
    	$aCurlHandles = array(); 

    	foreach ($aURLs as $id=>$url) {
			// Initialize a new curl instance at every iteration
        	$ch = curl_init();

			// Setup options
        	curl_setopt($ch, CURLOPT_URL, $url);
			
			// Save output for further usage with curl_multi_getcontent
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        	curl_setopt($ch, CURLOPT_HEADER, 0); 

        	$aCurlHandles[$url] = $ch;
        	curl_multi_add_handle($mh,$ch);
    	}
    
    	$active = null;
    	
		// Execute curl requests
    	do {
       		$mrc = curl_multi_exec($mh, $active);
    	} 
    	while ($mrc == CURLM_CALL_MULTI_PERFORM);

    	while ($active && $mrc == CURLM_OK) {
        	if (curl_multi_select($mh) != -1) {
            	do {
                	$mrc = curl_multi_exec($mh, $active);
            	} while ($mrc == CURLM_CALL_MULTI_PERFORM);
        	}
    	}
    	$html = "";
        
		// Iterate through the handles and get content
    	foreach ($aCurlHandles as $url=>$ch) {
			// Append it to $html with a delimeter at the end
        	$html .= curl_multi_getcontent($ch)."|||";
				
			// Remove handler
        	curl_multi_remove_handle($mh, $ch); 
    	}

		// Close curl connection
    	curl_multi_close($mh); 
		echo $html;
	}
?> 
<html>
    <head>
        <title>Stock AI</title>
        <meta charset="utf-8">
        <meta lang="en">
		<link rel="stylesheet" type="text/css" href="/style/style.css">
        <link rel="icon" type="image/x-icon" href="/images/favstock.png">
        <meta name="description" content="A legnagyobb volatilitású magyar részvények analizálárása szolgáló open-source program.">
    </head>
    <body>
		<header>
			<a href="/test.php">Stock AI</a>
			<div class="headerRight">
				<a href="/downloads.php">Downloads</a>
				<a href="/cont.php">Contribute</a>
				<a href="/about.php">About</a>
			</div>
		</header>
		<br><br><br><br>
        <div class="hStocks" id="hStocks"></div>
		<?php require_once 'template_pageBottom.php'; ?>
        <script type="text/javascript">
            // Shorten document.getElementById()
            function _(el){
                return document.getElementById(el);
            }

            // Preset data
			// ***DO NOT FORGET TO RESET***
           	setTimeout(callback, 1);
			// callback();
            _("hStocks").innerHTML = "<img src='/images/rolling.gif'>";

            // Create a callback function that will constantly pull out fresh data from the outer resource 
            function callback(){
                _("hStocks").innerHTML = "";

                // Preset ajax request for client-server communication
                let xml = new XMLHttpRequest;
                xml.open('POST', 'test.php', false);
                xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xml.onreadystatechange = function(){
                    if(xml.readyState == 4 && xml.status == 200){
                        let resp = xml.responseText;

                        // Transform back the string into an array by splitting the string at every |||
                        let tmp = resp.split("|||");
                        for(let i = 0; i < tmp.length; i++){
                            // Parse the current element of the array as a JSON file => accessing elements
                            let json = JSON.parse(tmp[i]);

                            // Save the required data for further usage
                            let min = json.min;
                            let max = json.max;
                            let change = json.chg;
                            let open = json.open;
                            let close = json.last;
                            let kotesek = json.kotesdb;
                            let ticker = json.ticker;
                            let kotesekData = json.kotesek;
                            let forgalom = json.forgalom;

                            /*kotesekData = Array(kotesekData);
                            let from = kotesekData[0].length - 9;
                            let to = kotesekData[0].length;*/

                            // Call function for analyzing the current stock
                            let indicators = analyzeStock(json.imgdata.data);

                            // Check if the current stock pays a dividend or not
                            let dividend = "";
                            if(ticker == "MTELEKOM" || ticker == "MOL"){
								dividend = "(dividend paying stock)";
							}

                            // Save the required data got back from analyzeStock()
                            let rsi = indicators[0];
                            let momentum = indicators[1];
                            let stochastic = indicators[2];
                            let perK = stochastic[2].toFixed(2);
                            let perD = stochastic[3].toFixed(2);
                            let ema3 = indicators[3];
                            let ema9 = indicators[4];
                            let ema14 = indicators[5];
                          
							let mOne = momentum[0];
							let mTwo = momentum[1];
							let mThree = momentum[2]
						
							/* 
								Get the last element of EMA and price arrays in order to compare them 
								later in the technical analysis
							*/

                            let lastPrice = json.imgdata.data[json.imgdata.data.length - 1].close;
                            let ema3Last = Number(ema3[ema3.length - 1]);
                            let ema9Last = Number(ema9[ema9.length - 1]);
                            let ema14Last = Number(ema14[ema14.length - 1]);
                            
							let ema3arr = [], ema9arr = [], ema14arr = [];

							for(let i = 0; i < 3; i++){
								ema3arr.push(ema3[i]);
								ema9arr.push(ema9[i]);
								ema14arr.push(ema14[i]);
							}

							let ema3One, ema3Two, ema3Three, ema9One, ema9Two, ema14One, ema14Two, ema14Three;
							[ema3One, ema3Two, ema3Three] = ema3arr;
							[ema9One, ema9Two, ema9Three] = ema9arr;
							[ema14One, ema14Two, ema14Three] = ema3arr;
							/* 
								Declare the required variables for holding the output text after the 
								comparison with the help of the techical analysis
							*/

                            let rsiText1, rsiText2, stochText, ema3Text, ema9Text, ema14Text;
							let pointsSell = 0, pointsBuy = 0;
						
                            // Create output for RSI
							if(rsi >= 79.5 && rsi <= 80.5){	
								rsiText1 = "<span class='oversold'>Strong sell</span> (" + rsi + ")";
								pointsSell += 2;
							}else if(rsi >= 80){
								rsiText1 = "<span class='oversold'>Sell</span> (" + rsi + ")";
								pointsSell++;
							}else if(rsi < 80 && rsi > 20){
								rsiText1 = "<span class='neutral'>Neutral</span> (" + rsi + ")";
							}else if(rsi <= 20.5 && rsi >= 19.5){
								rsiText1 = "<span class='overbought'>Strong buy</span> (" + rsi + ")";
								pointsBuy += 2;
							}else{
								rsiText1 = "<span class='overbought'>Buy</span> (" + rsi + ")";
								pointsBuy++;
							}

							// Create output for RSI Low
                            if(rsi >= 69.5 && rsi <= 70.5){	
								rsiText2 = "<span class='oversold'>Strong sell</span> (" + rsi + ")";
								if(pointsSell < 1) pointsSell += 1.5;
							}else if(rsi >= 70){
								rsiText2 = "<span class='oversold'>Sell</span> (" + rsi + ")";
								if(pointsSell < 1) pointsSell += 0.75;
							}else if(rsi < 70 && rsi > 30){
								rsiText2 = "<span class='neutral'>Neutral</span> (" + rsi + ")";
							}else if(rsi <= 30.5 && rsi >= 29.5){
								rsiText2 = "<span class='overbought'>Strong buy</span> (" + rsi + ")";
								if(pointsBuy < 1) pointsBuy += 1.5;
							}else{
								rsiText2 = "<span class='overbought'>Buy</span> (" + rsi + ")";
								if(pointsBuy < 1) pointsBuy += 0.75;
							}
							
							// Create output for Momentum
							if((mOne <= 100 && mTwo <= 100 && mThree > 100 && mThree < 105) || (mOne <= 100 && mTwo > 100 && mTwo <= 105 && mThree > 100 && mThree <= 105)){	
								momText = "<span class='overbought'>Strong buy</span> (" + momentum.join(", ") +")";
								pointsBuy += 2;
							}else if((mOne > 100 && mTwo > 100 && mThree < 100 && mThree >= 95) || (mOne > 100 && mTwo < 100 && mTwo >= 95 && mThree < 100 && mThree >= 95)){
								momText = "<span class='oversold'>Strong sell</span> (" + momentum.join(", ") +")"
								pointsSell += 2;
                            }else if(mThree >= 100 && mThree < 110){
								momText = "<span class='oversold'>Sell sign</span> (" + momentum.join(", ") +")";
								pointsSell += 0.75; 
                            }else if(mThree < 100 && mThree >= 90){
								momText = "<span class='overbought'>Buy sign</span> (" + momentum.join(", ") +")";
								pointsBuy += 0.75; 
                            }else{
								momText = "<span class='neutral'>Neutral</span> (" + momentum.join(", ") +")"; 
							}

							// Create output for EMA3
                            if((ema3One < close && ema3Two < close && ema3Three >= close) || (ema3One < close && ema3Two >= close && ema3Three >= close)){
								ema3Text = "<span class='overbought'>Strong Buy </span> (" + ema3Last + ")";
								pointsBuy += 2;
                            }else if((ema3One > close && ema3Two <= close && ema3Three <= close) || (ema3One > close && ema3Two > close && ema3Three <= close)){
								ema3Text = "<span class='oversold'>Strong Sell</span> (" + ema3Last + ")"; 
								pointsSell += 2;
                            }else{ 
								ema3Text = "<span class='neutral'>Neutral</span> (" + ema3Last + ")"; 
							}

							// Create output for EMA9
                            if((ema9One < close && ema9Two < close && ema9Three >= close) || (ema9One < close && ema9Two >= close && ema9Three >= close)){
								 ema9Text = "<span class='overbought'>Strong Buy</span> (" + ema9Last + ")";								 pointsBuy += 2;
                            }else if((ema9One > close && ema9Two <= close && ema9Three <= close) || (ema9One > close && ema9Two > close && ema9Three <= close)){
								 ema9Text = "<span class='oversold'>Strong sell</span> (" + ema9Last + ")";
								pointsSell += 2;
                            }else{
								 ema9Text = "<span class='neutral'>Neutral</span> (" + ema9Last + ")"; 
							}

							// Create output for EMA14
                            if((ema14One < close && ema14Two < close && ema14Three >= close) || (ema14One < close && ema14Two >= close && ema14Three >= close)){
								ema14Text = "<span class='overbought'>Strong buy</span> (" + ema14Last + ")";
								pointsBuy += 2; 
                           	}else if((ema14One > close && ema14Two <= close && ema14Three <= close) || (ema14One > close && ema14Two > close && ema14Three <= close)){
								ema14Text = "<span class='oversold'>Strong sell</span> (" + ema14Last + ")";
								pointsSell += 2;
                            }else{
								ema14Text = "<span class='neutral'>Neutral</span> (" + ema14Last + ")"; 
							}

							// Create output for Stochastic
                            if(perK >= 80 && perD >= 80 && perK >= perD){
								stochText = "<span class='oversold'>Sell</span> (" + perK + ", " + perD + ")";
								pointsSell += 1; 
                            }else if(perK >= 80 && perD >= 80 && perK < perD){
								stochText = "<span class='oversold'>Sell sign</span> (" + perK + ", " + perD + ")";
								pointsSell += 0.75;
                            }else if(perK < 80 && perD < 80 && perK > 20 && perD > 20){
								stochText = "<span class='neutral'>Neutral</span> (" + perK + ", " + perD + ")";
                            }else if(perK <= 20 && perD <= 20 && perK <= perD){
								stochText = "<span class='overbought'>Buy</span> (" + perK + ", " + perD + ")";
								pointsBuy += 1;
                            }else if(perK <= 20 && perD <= 20 && perK > perD){
								stochText = "<span class='overbought'>Buy sign</span> (" + perK + ", " + perD + ")";
								pointsBuy += 0.75; 
                            }else{ 
								stochText = "<span class='neutral'>Neutral</span> (" + perK + ", " + perD + ")";
							}
		
							let datay = Array.from(json.imgdata.data), bigPrice, summary; 
							if(datay[datay.length - 2].close > close){
								bigPrice = "<span class='oversold'>" + close + "</span>";
							}else if(datay[datay.length - 2].close < close){
								bigPrice = "<span class='overbought'>" + close + "</span>"
							}else{
								bigPrice = "<span class='neutral'>" + close + "</span>"
							}

							let isPos = pointsBuy - pointsSell;
							let isNeg = pointsSell - pointsBuy;
	
							if(isPos > isNeg && (isPos - isNeg >= 11)){
								summary = "<span class='overbought'>Strong buy</span>";
							}else if(isPos - isNeg >= 9){
								summary = "<span class='overbought'>Buy</span>";
							}else if(isPos - isNeg >= 7){
								summary = "<span class='overbought'>Buy sign</span>";
							}else if(isNeg - isPos >= 11){
								summary = "<span class='oversold'>Strong sell</span>";
							}else if(isNeg - isPos >= 9){
								summary = "<span class='oversold'>Sell</span>";
							}else if(isNeg - isPos >= 7){
								summary = "<span class='oversold'>Sell sign</span>";
							}else{
								summary = "<span class='neutral'>Neutral</span>";
							}

                            // Output the result in the HTML page
							_("hStocks").innerHTML += `
								<div class="stockCont">
									<div class="title">${ticker} ${dividend} ${bigPrice}</div>
									<div class="flexCont">
										<div class="min">Min price: ${min}</div>
										<div class="max">Max price: ${max}</div>
										<div class="change">Change: ${change}</div>
										<div class="open">Open price: ${open}</div>
										<div class="close">Close/current price: ${close}</div>
										<div class="trades">Trades: ${kotesek}</div>
										<div class="volume">Volume: ${prettyPrint(forgalom)}</div>
									</div>
									<div class="flexCont">
										<div class="rsi">RSI: ${rsiText1}</div>
										<div class="rsiLow">RSI low: ${rsiText2}</div>
										<div class="rsiLow">Momentum: ${momText}</div>
										<div class="rsiLow">Stochastic: ${stochText}</div>
										<div class="rsiLow">EMA3: ${ema3Text}</div>
										<div class="rsiLow">EMA9: ${ema9Text}</div>
										<div class="rsiLow">EMA14: ${ema14Text}</div>
									</div>
									<div class="summary">Summary: ${summary}</div>
								</div>
							`;
                        }
                    }
                }
                xml.send("refresh=now");
            }

            // Analyze stocks with technical analysis
            function analyzeStock(data){
                // Returns an array holding all the information about the analysis
                return [RSI(data, 14), momentum(data, 14), stochastic(data, 6), movingAvg(data, 3, "ema"),  movingAvg(data, 9, "ema"), movingAvg(data, 14, "ema")];
            }

            function momentum(data, n){
                // Explicit type coercion: object to array => thus we got a two-dimensional array
                let datax = Array.from(data);
				let result = [];	
				for(let i = 0; i < 3; i++){
					let h = (datax.length) - (n + i + 1);
					let todayClose = datax[datax.length - (i + 1)].close;
					let nBeforeClose = datax[h].close;
					let e = ((todayClose - nBeforeClose) / (nBeforeClose) * 100 + 100).toFixed(2);
 					result.push(e);
				}
	
                // Return the value of Momentum with the precision of 4 decimals
                return result;             
			}

            function stochastic(data, n){
                /* 
                    Formula: %K = 100 * ((Z - Ln) / (Hn - Ln))
                    where Z is the last closing price, Ln is the lowest price during the n period and Hn is the highest price during the n period
                */

                let stochData = [];
                let datax = Array.from(data);
				let dataxBackup = datax;
				datax.slice(Math.max(datax.length - n, 1));

                for(let i = 1; i <= n; i++){
                    // Dynamically count the last n elements of the array and constantly stepping i elements back from the end
                    
                    // Get the closing price for today
                    let Z = datax[datax.length - 1].close;

                    // Collect lowest prices during the n period
                    let lowestPrices = Array.from(datax.map(c => c.low));

                    // Collect highest prices during the n period
                    let highestPrices = Array.from(datax.map(c => c.high));
         
                    // Select the lowest (Ln) price from the lowest prices and the highest price (Hn) from the highest prices during the n period
                    let Ln = Math.min(...lowestPrices);
                    let Hn = Math.max(...highestPrices);
                    // Use the formula to calculate Stochastic
                    let perK = 100 * ((Z - Ln) / (Hn - Ln));
                    stochData.push(perK);

					datax.pop();
					datax.unshift(dataxBackup[n - i]);
                }

                // Get %D with the 3 day moving average
                let perD = movingAvg(stochData, 3, "sma");
                stochData.push(...perD);

                // Return an array of 4 elements where the first three elements are for the %K and the last is for the %D
                return stochData;
            }

            function RSI(data, n){
                /*
                    Formula: 100 - [100 / (1 + U/D)]
                    where U indicates the prices occurred during increasing
                    and D indicates those that occurred during descreasing
                */

                let datax = Array.from(data);
                let increasedArr = 0, descreasedArr = 0, incCount = 0, decCount = 0;
                for(let i = 0; i < n; i++){
                    // Check if the datax[0][i + 1] element is undefied or not
                    if(datax.length != i + 1){
                        // If not check/collect the prices occurred during decreasing over the n period
                        if(datax[i].close > datax[i + 1].close){
                            descreasedArr += datax[i + 1].close;
                            decCount++;
                        }else if(datax[i].close < datax[i + 1].close){
                            // Otherwise, push it to the increased ones
                            increasedArr += datax[i + 1].close;
                            incCount++;
                        }
                    }
                }

                // Count averages from prices
                let avgInc = increasedArr / incCount;
                let avgDec = descreasedArr / decCount;
                // Return the value of RSI with the precision of 4 decimals
                return (100 - (100 / (1 + avgInc / avgDec))).toFixed(2);
            }

            function movingAvg(optional, k, type){
                let i, avgArr = [], sum = 0;
                // Check if simple moving average is requested
                /*
                    Formula: Sum(k) / Count(k) where k indicates the elements passed in
                */
                if(type == "sma"){
                    // Loop from zero to array.lenght - moving average period
                    // For every element we count the SMA from the surronding prices
                    for(i = 0; i <= k; i++){
                        for(let n = i; n < i + k; n++){
                            sum += optional[n].close;
                        }
                        avgArr.push(sum / k);
                        sum = 0;
                    }
                // Check if exponential moving average is requested
                }else if(type == "ema"){
                    /*
                        Formula: (last price * x%) + (last EMA * (100 - x%))
                        where x% = 2 / (1 + N) where N = period
                    */
                    let nPeriod = 2 / (1 + k);
                    let lastPrice;

					// Choose time interval for the 1st element of SMA
					let tInt;
					if(nPeriod >= 0.4) tInt = 5;
					else if(nPeriod >= 0.2) tInt = 7;
					else tInt = 9;	

                    for(let i = 0; i < k; i++){
                        // Decide whether the last EMA exists or not
                        // If not, default to the first closing price over the n period, otherwise lastPrice is already set to lastEMA
                        lastPrice = lastPrice || Number(movingAvg(optional, tInt, "sma")[0]);

                        // Count EMA with the formula
                        lastEMA = (optional[i].close * nPeriod) + (lastPrice * (1 - nPeriod));
                        lastPrice = lastEMA;
                        avgArr.push(lastEMA.toFixed(2));
                    }
                }
                return avgArr;
            }

			function prettyPrint(num){
				let result = [];
				let formatNum = String(num).split("").reverse();
				for(let i = 0; i < formatNum.length; i++){
					if(i != 0 && i != formatNum.length - 1 && (i + 1) % 3 == 0){
						result[i] = "," + formatNum[i];
					}else{
						result[i] = formatNum[i];
					}
				}
				return result.reverse().join("");
			}	
        </script>
    </body>
</html>
