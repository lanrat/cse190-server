<?php

// Create map with request parameters
$params = array ('json' => json_encode(array ("user" => "1", "fortuneid" => "1", "text" => "You will get straight F's")));
 
// Build Http query using params
$query = http_build_query ($params);
 
// Create Http context details
$contextData = array (
                'method' => 'POST',
                'header' => "Connection: close\r\n".
                            "Content-Length: ".strlen($query)."\r\n",
                'content'=> $query );
 
// Create context resource for our request
$context = stream_context_create (array ( 'http' => $contextData ));
 
// Read page rendered as result of your POST request
$result =  file_get_contents (
                  'http://cse-190-fortune.herokuapp.com/server.php?action=submitFortune',
                  false,
                  $context);
 
// Server response is now stored in $result variable so you can process it

echo $result;
?>
