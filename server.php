<?php

  class Server{

    public function heroku_conn() {
      extract(parse_url($_ENV["DATABASE_URL"]));
      $string = "user=$user password=$pass host=$host dbname=" . substr($path, 1);
      $string . " sslmode=require";
      return $string;
    }

    public function serve(){
      $method = $_GET['action'];
      if($method != NULL){
        $pg_conn = pg_connect($this->heroku_conn());
      }
      switch($method){
        case "getFortunesSubmitted":
          $fortune = json_decode($_POST['json'], true);
          $insert = array($fortune["user"]);
          $result = pg_prepare($pg_conn, "getFortunesSubmitted",
          'SELECT fortuneid, text, upvote, downvote, views, uploaddate 
          FROM fortunes WHERE uploader = $1');


          $result = pg_execute($pg_conn, "getFortunesSubmitted", $insert);
          while($row[] = pg_fetch_assoc($result)){
          }
            echo(json_encode($row));
          
          break;
        case "getFortune":
          // need to change name of user var
          $fortune = json_decode($_POST['json'], true);
          $insert = array($fortune["fortuneid"]);
          $result = pg_prepare($pg_conn, "getFortune",
          'SELECT fortuneid, text, upvote, downvote, views, uploaddate 
          FROM fortunes WHERE fortuneid = $1');


          $result = pg_execute($pg_conn, "getFortune", $insert);
          while($row[] = pg_fetch_assoc($result)){
          }
            echo(json_encode($row));
          
          break;
        case "submitVote":
          // need to change name of user var
          $fortune = json_decode($_POST['json'], true);
          /*
          $insert = array($fortune["user"]);
          $result = pg_prepare($pg_conn, "getFortune",
          'SELECT fortuneid, text, upvote, downvote, views, uploaddate 
          FROM fortunes WHERE fortuneid = $1');


          $result = pg_execute($pg_conn, "getFortune", $insert);
          while($row[] = pg_fetch_assoc($result)){
          }
            echo(json_encode($row));
            */
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
