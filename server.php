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
        case "submitUpdate":



          break;
        case "submitFortune":
          $fortune = json_decode($_POST['json'], true);
          $insert = array($fortune["text"], $fortune["date"], $fortune["user"]);
         
          //NEED to fix this
          $result = pg_prepare($pg_conn, "submitFortune",
          'INSERT INTO fortunes ( text, uploader, uploaddate)
           VALUES ($1, $2 , $3)');

          $result = pg_execute($pg_conn, "submitFortune", $insert);
            
          break;
        
        case "submitView":
          $fortune = json_decode($_POST['json'], true);
          $insert = array($fortune["userid"], $fortune["user"], $fortune["vote"], $fortune["flagged"]);
          $result = pg_prepare($pg_conn, "submitView",
          'INSERT INTO viewed (userid, fortuneid, vote, flagged)
           SELECT $1, $2, $3, $4
           WHERE NOT EXISTS (SELECT userid FROM viewed WHERE
           userid = $1
           AND fortuneid = $2');

          $result = pg_execute($pg_conn, "viewMaintenance", $insert);

          $result = pg_prepare($pg_conn, "updateView", 'UPDATE fortunes WHERE fortuneID = $2 
            SET views = views + $3 
            SET flagged = flagged + $4');
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
