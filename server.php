<?php

  class Server{

    public function pg_connection_string_from_database_url() {
      extract(parse_url($_ENV["DATABASE_URL"]));
      $string = "user=$user password=$pass host=$host dbname=" . substr($path, 1);
      $string . " sslmode=require";
      return $string;
    }

    public function serve(){
      $method = $_GET['action'];
      if($method != NULL){
        $pg_conn = pg_connect(pg_connection_string_from_database_url());
      }
      switch($method){
        case "getFortunesSubmitted":
          echo "YEAH";
          $fortune = json_decode($_POST['json']);
          $insert = array($fortune["user"]);
          $result = pg_prepare($pg_conn, "getFortunesSubmitted", 'SELECT * FROM fortunes WHERE uploader = $1');


          $result = pg_execute($pg_conn, "getFortunesSubmitted", $insert);
          while($row = pg_fetch_row($result)){
            echo $row[0];
          }
          
          break;
        case "getFortune":
          $fortune = json_decode($_POST['json']);
          var_dump($fortune);
          break;
        case "submitVote":
          break;
        case "submitFortune":
          break;
        case "submitFlag":
          break;
        case "submitView":
          break;
        default:
          echo "Default";
          $fortune = json_decode($_POST['json']);
          var_dump($fortune);
          echo "<br><br> json = " . ($_POST['json']);
          break;
      }
    }



  }

  $server = new Server;
  $server->serve();
?>
