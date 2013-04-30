<?php
# This function reads your DATABASE_URL configuration automatically set by Heroku
# the return value is a string that will work with pg_connect
function pg_connection_string() {
  return "dbname=d9f15upnpf30jn host=ec2-54-225-106-211.compute-1.amazonaws.com user=whuakxgtjhychs password=6PV1qeLaRHp7PLGcQcfGaZj4mN port=5432 sslmode=require";
}
 
echo "bla bla bla";
$dsn = "pgsql:"
    . "dbname=d9f15upnpf30jn host=ec2-54-225-106-211.compute-1.amazonaws.com user=whuakxgtjhychs password=6PV1qeLaRHp7PLGcQcfGaZj4mN port=5432 sslmode=require";
$db = new PDO($dsn);

$query = "SELECT employee_id, last_name, first_name" .
         "FROM employees";

$result = $db->query($quer);

while($row = $result->fetch(PDO::FETCH_ASSOC)){
  echo "$row["employee_id"] $row["last_name"] $row["first_name"]\n";
}

echo "IS DONE YO";

?>
