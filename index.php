 <head>


         <meta charset="utf-8">
   
         <link rel="stylesheet" href="bootstrap\css\bootstrap.css">
         <link rel="stylesheet" href="bootstrap/css/bootstrap.css">
   
  <title>Top 25 Fortunes of the Past Week</title>
 </head>

 <body>
 	
  <div class="jumbotron subhead" style="background-color:#191970; padding: 20px;">
  <div class="container" >
    <h1 style="text-align:center; color:#fff; font-size: 100px; line-height: 1; letter-spacing: -2px;">Top Fortunes of the Past Week</h1>

  </div>
	</div>
  <div class="container">
         
  <table class="table table-bordered table-striped">
   <thead>
    <tr>
     <th> Rank </th>
     <th>Fortune</th>
     <th> Views </th>
     <th>Upvotes</th>
     <th>Downvotes</th>
     <th> Uploaded</th> 
    </tr>
   </thead>
   <tbody>
<?php
function pg_connection_string_from_database_url() {
  extract(parse_url($_ENV["DATABASE_URL"]));
  $string = "user=$user password=$pass host=$host dbname=" . substr($path, 1);
  $string . " sslmode=require";
  return $string;
}

$pg_conn = pg_connect(pg_connection_string_from_database_url());

$result = pg_query($pg_conn, "SELECT * FROM fortunes WHERE uploaddate < date_part('epoch'::text, now()) - 604800 ORDER BY (upvote - downvote) DESC, upvote DESC LIMIT 25");
$i = 1;
while ($row = pg_fetch_row($result)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($i) . "</td>";
    echo "<td>" . htmlspecialchars($row[1]) . "</td>";
    echo "<td>" . htmlspecialchars($row[4]) . "</td>";    
    echo "<td>" . htmlspecialchars($row[2]) . "</td>";
    echo "<td>" . $row[3] . "</td>";
    echo "<td>" . date('m-d-Y',$row[8]) . "</td>";  
    echo "</tr>";
    $i += 1;
}
$result->closeCursor();
?>
   </tbody>
  </table>
</div>

 </body>
</html>
