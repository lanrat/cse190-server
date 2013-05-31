
<?php
$params = array ('json' => json_encode(array ("fortuneid" => 273, "user" => 100, "vote" => -1)));


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
                  'http://cse-190-fortune.herokuapp.com/server.php?action=submitVote',
                  false,
                  $context);
 
// Server response is now stored in $result variable so you can process it

echo $result;
?>