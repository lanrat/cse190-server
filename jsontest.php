<?php

// Create map with request parameters
//$params = array ('json' => json_encode(array ("user" => "1", "fortune_id" => "1", "text" => "You will get straight F's")));
 
//$params = array ('json' => json_encode(array ("user" => "00000000-2bb5-161b-ffff-fffff4f2d3b2")));
$params = array ('json' => json_encode(array ("user" => "100", "vote" => false, "fortuneid" => 10000)));
//$params = array ('json' => json_encode(array ("fortuneid" => "1")));


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
                  'http://cse-190-fortune.herokuapp.com/server.php?action=getFortuneByID',
                  false,
                  $context);
 
// Server response is now stored in $result variable so you can process it

echo $result;


//var_dump($strings);
