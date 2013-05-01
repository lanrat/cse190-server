<?php
echo "bla bla bla";


function pg_connection_string_from_database_url() {
  extract(parse_url($_ENV["DATABASE_URL"]));
  return "user=$user password=$pass host=$host dbname=" . substr($path, 1)"
       . " sslmode=require";


$pg_conn = pg_connect(pg_connection_string_from_database_url());

$result = $db->query($query);

while($row = $result->fetch(PDO::FETCH_ASSOC)){
  echo $row["employee_id"] . $row["last_name"] . $row["first_name"];
}

echo "IS DONE YO";

?>
