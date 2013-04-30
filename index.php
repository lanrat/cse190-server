<?php
# This function reads your DATABASE_URL configuration automatically set by Heroku
# the return value is a string that will work with pg_connect
function pg_connection_string() {
  return "dbname=d9f15upnpf30jn host=ec2-54-225-106-211.compute-1.amazonaws.com user=whuakxgtjhychs password=6PV1qeLaRHp7PLGcQcfGaZj4mN port=5432 sslmode=require";
}
 
# Establish db connection
/*$db = pg_connect(pg_connection_string());
if (!$db) {
   echo "Database connection error."
   exit;
}*/
 
$result = pg_query($db, "SELECT * FROM employees");
echo "bla bla bla";

while($row = pg_fetch_row($result)){
  echo "$row[0] $row[1] $row[2] $row[3] \n";
}

?>
