<?php

  class Server{

    public function heroku_conn() {
      extract(parse_url($_ENV["DATABASE_URL"]));
      $string = "user=$user password=$pass host=$host dbname=" . substr($path, 1);
      $string . " sslmode=require";
      return $string;
    }

    public function processResult($result){
      if($result != false){    
        $return = array('accepted' => true, 'result' => array($result));
      }
      else{
        $return = array('accepted' => false);
      }

      echo(json_encode($return));
    }

    public function createUser($userid){
      $insert = array($userid);
      //var_dump($insert);
      $pg_conn = pg_connect($this->heroku_conn());
      $result = pg_prepare($pg_conn, "createUser",
      "INSERT INTO users 
      SELECT $1 
      WHERE NOT EXISTS(
        SELECT userid FROM users WHERE userid = $1)");
      $result = pg_execute($pg_conn, "createUser", $insert);
    }

    public function serve(){
      $method = $_GET['action'];
      if($method != NULL){
        $pg_conn = pg_connect($this->heroku_conn());
      }
      $fortune = json_decode($_POST['json'], true);
      // Store the user id if passed.
      if($fortune["user"] != NULL){
        //echo "plz work: " . $fortune["user"] . "\n";
        $this->createUser($fortune["user"]);
      }
      switch($method){
        /* Method name: getFortunesSubmitted
         * Parameters: Uploader ID
         * Returns: All fortunes uploaded by the user.
         */
        case "getFortunesSubmitted":
          $insert = array($fortune["user"]);
          $result = pg_prepare($pg_conn, "getFortunesSubmitted",
          'SELECT fortuneid, text, upvote, downvote, views, uploaddate 
          FROM fortunes WHERE uploader = $1');

          $result = pg_execute($pg_conn, "getFortunesSubmitted", $insert);
          $rows = pg_fetch_all($result);
          $this->processResult($rows);
          break;


        /* Method name: getFortune
         * Parameters: Fortune ID
         * Returns: All data about a specific fortune.
         */
        case "getFortune":
          $insert = array($fortune["user"]);
          $result = pg_prepare($pg_conn, "getFortune",
          'SELECT fortuneid, text, upvote, downvote, views, uploaddate 
          FROM fortunes WHERE fortuneid NOT IN 
          (SELECT fortuneid FROM viewed WHERE userid = $1)');

          $result = pg_execute($pg_conn, "getFortune", $insert);
          $rows = pg_fetch_all($result);
          $randomFortune = rand(0, count($rows) - 1);
          $chosen = $rows[$randomFortune];
          $this->processResult($chosen);


          $result = pg_prepare($pg_conn, "insertView",
          "INSERT INTO viewed
          VALUES ($1, $2, 0, 'false')");

          $insert = array($fortune["user"], $chosen["fortuneid"]);
          $result = pg_execute($pg_conn, "insertView", $insert);

          break;

        case "getFortuneByID":

          $insert = array($fortune["fortuneid"]);
          $result = pg_prepare($pg_conn, "getFortuneByID",
          'SELECT fortuneid, text, upvote, downvote, views, uploaddate 
          FROM fortunes WHERE fortuneid = $1');


          $result = pg_execute($pg_conn, "getFortuneByID", $insert);
          $row = pg_fetch_assoc($result);
          $this->processResult($row);
          
          break;

        /* Method name: submitFortune
         * Parameters: Fortune text(actual fortune), Uploader ID 
         * Returns: void
         * Note: the time parameter is generated in php. 
         */
        case "submitFortune":
          $insert = array($fortune["text"],  $fortune["user"], time() );
         
          $result = pg_prepare($pg_conn, "submitFortune",
          'INSERT INTO fortunes ( text, uploader, uploaddate)
           VALUES ($1, $2, $3) 
           RETURNING fortuneid, text, upvote, downvote, views, uploaddate');

          $result = pg_execute($pg_conn, "submitFortune", $insert);
          $this->processResult(pg_fetch_assoc($result));

          break;

        case "submitVote":
          // We need to change this to take a boolean for vote
          $insert = array($fortune["fortuneid"],  $fortune["user"], $fortune["vote"]);                                
          $result = pg_prepare($pg_conn, "submitVote",
           'UPDATE viewed SET vote = $3 WHERE fortuneid = $1 AND userid = $2 AND vote = 0 ');
          $result = pg_execute($pg_conn, "submitVote", $insert);
   
          if($result == false)
          {
              
          }
          else
          {
              if($fortune["vote"] == true)
              {
                $result = pg_prepare($pg_conn, "upVote",
                'UPDATE fortunes SET upvote =  1 + upvote WHERE fortuneid = $1');
                $result = pg_execute($pg_conn, "upVote", array($fortune["fortuneid"]));
              }
              else 
              {
                $result = pg_prepare($pg_conn, "downVote",
                'UPDATE fortunes SET downvote = -1 + downvote WHERE fortuneid = $1');
                $result = pg_execute($pg_conn, "downVote", array($fortune["fortuneid"]));             
              }
          }
          break;


        /* Method name: submitView
         * Parameters: UploaderID, FortuneID, int Vote, int Flagged
         * Returns: void
         * Note: Database attributes: views, and flags increase by the parameters passed respectively.  
         */
        case "submitView":
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

// Logs -------------------------------------------------------------

      date_default_timezone_set('America/Los_Angeles');
      $current = file_get_contents('serverlogs.log');
      $current .= "\n\nNEW LOG: ";
      $current .= date('l jS \of F Y h:i:s A');
      $current .= "---------------------------\n";
      $current .= "action: " . $method . "\n";
      if($fortune != NULL){
        //logging output Date - JSON Object
        $current .= var_export($fortune, true);
      }
      else{
        $current .= "FAIL:\n";
        $current .= var_export($_POST['json'], true);
      }
      file_put_contents('serverlogs.log', $current);
    }
  }

// Main -------------------------------------------------------------

  $server = new Server;
  $server->serve();
?>
