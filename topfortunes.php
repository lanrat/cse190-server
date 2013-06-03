<?php
echo "bla bla bla";


function pg_connection_string_from_database_url() {
  extract(parse_url($_ENV["DATABASE_URL"]));
  $string = "user=$user password=$pass host=$host dbname=" . substr($path, 1);
  $string . " sslmode=require";
  return $string;
}

$pg_conn = pg_connect(pg_connection_string_from_database_url());

$result = pg_query($pg_conn, "SELECT * FROM fortunes");

while($row = pg_fetch_row($result)){
  echo $row[0] . " " . $row[1] . " " . $row[2];
}



?>