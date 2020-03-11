<?php
    // Get the required stock data from a 3rd-party provider via cURL
		$aURLs = array(
      "https://data.portfolio.hu/all/json/4IG:interval=1M",
      "https://data.portfolio.hu/all/json/MOL:interval=1M",
      "https://data.portfolio.hu/all/json/WABERERS:interval=1M",
      "https://data.portfolio.hu/all/json/FUTURAQUA:interval=1M",
      "https://data.portfolio.hu/all/json/MTELEKOM:interval=1M",
      "https://data.portfolio.hu/all/json/ESTMEDIA:interval=1M"); 
    $mh = curl_multi_init(); 

    $aCurlHandles = array(); 

    foreach ($aURLs as $id=>$url) { 
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
        curl_setopt($ch, CURLOPT_HEADER, 0); 

        $aCurlHandles[$url] = $ch;
        curl_multi_add_handle($mh,$ch);
    }

    $active = null;
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
    foreach ($aCurlHandles as $url=>$ch) {
        $html .= curl_multi_getcontent($ch)."|||"; 
        curl_multi_remove_handle($mh, $ch); 
    }

  curl_multi_close($mh); 
	echo $html;
?>
