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
      $fortune;
      switch($method){
          /* Method name: getFortunesSubmitted
           * Parameters: Uploader ID
           * Returns: All fortunes uploaded by the user.
           */
        case "getFortunesSubmitted":
          $fortune = json_decode($_POST['json'], true);
          $insert = array($fortune["user"]);
          $result = pg_prepare($pg_conn, "getFortunesSubmitted",
          'SELECT fortuneid, text, upvote, downvote, views, uploaddate 
          FROM fortunes WHERE uploader = $1');


          $result = pg_execute($pg_conn, "getFortunesSubmitted", $insert);
          $rows = pg_fetch_all($result);
          /*while($row[] = pg_fetch_assoc($result)){
          }*/
          echo(json_encode($rows));
          
          break;

          /* Method name: getFortune
           * Parameters: Fortune ID
           * Returns: All data about a specific fortune.
           */
        case "getFortune":
          $fortune = json_decode($_POST['json'], true);
          $insert = array($fortune["user"]);
          $result = pg_prepare($pg_conn, "getFortune",
          'SELECT fortuneid, text, upvote, downvote, views, uploaddate 
          FROM fortunes WHERE fortuneid NOT IN 
          (SELECT fortuneid FROM viewed WHERE userid = $1)');

          $result = pg_execute($pg_conn, "getFortune", $insert);
          $rows = pg_fetch_all($result);
          $randomFortune = rand(0, count($rows) - 1);
          $chosen = $rows[$randomFortune];
          echo(json_encode($chosen));


          $result = pg_prepare($pg_conn, "insertView",
          "INSERT INTO viewed
          VALUES ($1, $2, 0, 'false')");

          $insert = array($fortune["user"], $chosen["fortuneid"]);
          $result = pg_execute($pg_conn, "insertView", $insert);

          break;
        case "getFortuneByID":

          $fortune = json_decode($_POST['json'], true);
          $insert = array($fortune["fortuneid"]);
          $result = pg_prepare($pg_conn, "getFortuneByID",
          'SELECT fortuneid, text, upvote, downvote, views, uploaddate 
          FROM fortunes WHERE fortuneid = $1');


          $result = pg_execute($pg_conn, "getFortuneByID", $insert);
          while($row[] = pg_fetch_assoc($result)){
          }
          echo(json_encode($row));
          
          break;
        case "submitUpdate":



          break;

          /* Method name: submitFortune
           * Parameters: Fortune text(actual fortune), Uploader ID 
           * Returns: void
           * Note: the time parameter is generated in php. 
           */
        case "submitFortune":
          $fortune = json_decode($_POST['json'], true);
          $insert = array($fortune["text"],  $fortune["user"], time() );
         
          $result = pg_prepare($pg_conn, "submitFortune",
          'INSERT INTO fortunes ( text, uploader, uploaddate)
           VALUES ($1, $2, $3)');

          $result = pg_execute($pg_conn, "submitFortune", $insert);
          if($result == false){
            $return = array("accepted" => false);
          }else{
            $return = array("accepted" => true);
          }

          echo(json_encode($return));
            
          break;
          /* Method name: submitView
           * Parameters: UploaderID, FortuneID, int Vote, int Flagged
           * Returns: void
           * Note: Database attributes: views, and flags increase by the parameters passed respectively.  
           */
        case "submitView":
          $fortune = json_decode($_POST['json'], true);
          $insert = array($fortune["userid"], $fortune["fortuneid"], $fortune["vote"], $fortune["flagged"]);
          $result = pg_prepare($pg_conn, "submitView",
          'INSERT INTO viewed (userid, fortuneid, vote, flagged)
           SELECT $1, $2, $3, $4
           WHERE NOT EXISTS (SELECT userid FROM viewed WHERE
           userid = $1
           AND fortuneid = $2');
           /*** Needs to be fixed****/
          $result = pg_execute($pg_conn, "viewMaintenance", $insert);

          $result = pg_prepare($pg_conn, "updateView", 'UPDATE fortunes WHERE fortuneID = $2 
            SET views = views + $3 
            SET flagged = flagged + $4');
          break;

        default:
          /*echo "Default";
          $fortune = json_decode($_POST['json']);
          var_dump($fortune);
          echo "<br><br> json = " . ($_POST['json']);*/
          break;
      }
      if($fortune != NULL){
        //logging output Date - JSON Object
        $current = file_get_contents('serverlogs.log');
        $current .= "\n\nNEW LOG: ";
        $current .= date('l jS \of F Y h:i:s A');
        $current .= "---------------------------\n";
        $current .= var_dump($fortune);
        file_put_contents('serverlogs.log', $current);
      }
    }
  }

  $server = new Server;
  $server->serve();
?>
