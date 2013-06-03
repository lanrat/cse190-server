 <head>


         <meta charset="utf-8">
   
         <link rel="stylesheet" href="bootstrap\css\bootstrap.css">
         <link rel="stylesheet" href="bootstrap/css/bootstrap.css">
      <script src="boostrap/js/bootstrap.min.js"></script>
      <script src="swfobject/swfobject.js"></script>
     	<script src="swfobject/swfobject.js"></script>
   
  <title>Top 25 Fortunes</title>
 </head>

 <body>
 	
  <div class = "page-header"><h1> TOP FORTUNES </h1></div>
  <div class="container">
         
         <div class="hero-unit" style="text-align: center;">	
  <table class=class="table table-bordered table-striped">
   <thead>
    <tr>
     <th>Fortune</th>
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

$result = pg_query($pg_conn, "SELECT * FROM fortunes ORDER BY upvote DESC LIMIT 25");

while ($row = pg_fetch_row($result)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row[1]) . "</td>";
    echo "<td>" . htmlspecialchars($row[2]) . "</td>";
    echo "<td>" . $row[3] . "</td>";
    echo "<td>" . date('d-m-Y',$row[8]) . "</td>";  
    echo "</tr>";
}
$result->closeCursor();
?>
   </tbody>
  </table>
</div>
</div>
 </body>
</html>