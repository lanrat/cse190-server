<?php
echo "bla bla bla";


function pg_connection_string_from_database_url() {
  extract(parse_url($_ENV["DATABASE_URL"]));
  $string = "user=$user password=$pass host=$host dbname=" . substr($path, 1)";
  $string . " sslmode=require";
  return $string;
}

$pg_conn = pg_connect(pg_connection_string_from_database_url());

$result = pg_query($pg_conn, "SELECT employee_id, last_name, first_name FROM employee");

while($row = $result->fetch(PDO::FETCH_ASSOC)){
  echo "cmon man";
  echo $row["employee_id"] . $row["last_name"] . $row["first_name"];
}

echo "IS DONE YO";

?>
