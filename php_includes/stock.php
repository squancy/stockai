<?php
  function getDataStock() {
    $aURLs = array(
      "https://data.portfolio.hu/all/json/4IG:interval=1M",
      "https://data.portfolio.hu/all/json/MOL:interval=1M",
      "https://data.portfolio.hu/all/json/ESTMEDIA:interval=1M",
      "https://data.portfolio.hu/all/json/FUTURAQUA:interval=1M",
      "https://data.portfolio.hu/all/json/WABERERS:interval=1M",
      "https://data.portfolio.hu/all/json/MTELEKOM:interval=1M"
    );
    
    // Initialize curl for async (multi) use
    $mh = curl_multi_init();
    
    // Create array for curl handlers
    $aCurlHandles = array();
    
    foreach ($aURLs as $id => $url) {
      // Initialize a new curl instance at every iteration
      $ch = curl_init();
      
      // Setup options
      curl_setopt($ch, CURLOPT_URL, $url);
      
      // Save output for further usage with curl_multi_getcontent
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      
      $aCurlHandles[$url] = $ch;
      curl_multi_add_handle($mh, $ch);
    }
    
    $active = null;
    
    // Execute curl requests
    do {
      $mrc = curl_multi_exec($mh, $active);
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    
    while ($active && $mrc == CURLM_OK) {
      if (curl_multi_select($mh) != -1) {
        do {
          $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
      }
    }
    $html = "";
    
    // Iterate through the handles and get content
    foreach ($aCurlHandles as $url => $ch) {
      $html .= curl_multi_getcontent($ch) . "|||";
      
      curl_multi_remove_handle($mh, $ch);
    }
    
    // Close curl connection
    curl_multi_close($mh);
    return $html;
  }
?>
