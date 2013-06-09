<?php

  class Server{

    private $error = "false";
    private $debug = false;

    public function setError($result, $e){
      if($this->debug == true){
        echo $result;
        if($result == false){
          echo var_dump($e);
          echo "\n";
          echo var_dump($this->error);
          if($this->error === "false"){
            echo " inside";
            $this->error = $e;
          }
        }
      }
    }

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
        $return = array('accepted' => false, 'error' => $this->error);
      }

      echo(json_encode($return));
    }

    public function createUser($userid){
      $insert = array($userid);
      $pg_conn = pg_connect($this->heroku_conn());
      $result = pg_prepare($pg_conn, "createUser",
      "INSERT INTO users 
      SELECT CAST($1 as varchar(40)) 
      WHERE NOT EXISTS(
        SELECT userid FROM users WHERE userid = $1)");
      $result = pg_execute($pg_conn, "createUser", $insert);
    }

    public function serve(){
      //$error = "Default Error";
      $method = htmlspecialchars($_GET['action']);
      if($method != NULL){
        $pg_conn = pg_connect($this->heroku_conn());
      }

      // Grab the fortune.
      $fortune = json_decode($_POST['json'], true);

      // Store the user id if passed.
      if($fortune["user"] != NULL){
        $this->createUser($fortune["user"]);
      } 

      if($fortune["debug"] != NULL){
        $this->debug = true;
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
          $this->setError($result, "getFortunesSubmitted");
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
          'SELECT fortuneid, text, upvote, downvote, views, uploaddate, 
          upvote - downvote as totalvote 
          FROM fortunes WHERE fortuneid NOT IN 
          (SELECT fortuneid FROM viewed WHERE userid = $1)
          ORDER BY totalvote DESC');

          $result = pg_execute($pg_conn, "getFortune", $insert);
          $this->setError($result, "getFortune: getting fortunes");
          $rows = pg_fetch_all($result);


          // Grab lowest weight to prevent negative weights.
          $totalWeight = 0;
          $lastRow = end($rows);
          $bottomWeight = $lastRow["totalvote"];
          

          // Generate total weighting.
          $newFortunes = array();
          foreach($rows as $num => $row){
            $totalWeight += $row["totalvote"] + $bottomWeight;
            if(($row["upvote"] + $row["downvote"]) < 10){
              $newFortunes[] = $num;
            }
          }
          

          // 50% chance of new fortune, 50% chance of algorithm
          $fortuneType = rand(0, 1);

          if($fortuneType == 0){
            $randomFortune = $newFortunes[rand(0, count($newFortunes))];
          }
          else{
            $randomWeight = rand(0, $totalWeight);
            foreach($rows as $num => $row){
              $randomWeight -= $row["totalvote"] + $bottomWeight;
              if($randomWeight <= 0){
                $randomFortune = $num;
              }
            }
          }

          $chosen = $rows[$randomFortune];
          $this->processResult($chosen);


          // Update viewed
          $result = pg_prepare($pg_conn, "insertView",
          "INSERT INTO viewed
          VALUES ($1, $2, 0, 'false')");

          $insert = array($fortune["user"], $chosen["fortuneid"]);
          $result = pg_execute($pg_conn, "insertView", $insert);
          $this->setError($result, "getFortune: inserting view");

          // Update fortune
          $result = pg_prepare($pg_conn, "updateViews",
           'UPDATE fortunes SET views = views + 1 WHERE fortuneid = $1');
          $result = pg_execute($pg_conn, "updateViews", array($chosen["fortuneid"]));
          $this->setError($result, "getFortune: updating views");

          break;


        case "getFortuneByID":

          $insert = array($fortune["fortuneid"]);
          $result = pg_prepare($pg_conn, "getFortuneByID",
          'SELECT fortuneid, text, upvote, downvote, views, uploaddate 
          FROM fortunes WHERE fortuneid = $1');


          $result = pg_execute($pg_conn, "getFortuneByID", $insert);
          $this->setError($result, "getFortuneByID");
          $row = pg_fetch_assoc($result);
          $this->processResult($row);
          
          break;

        /* Method name: submitFortune
         * Parameters: Fortune text(actual fortune), Uploader ID 
         * Returns: void
         * Note: the time parameter is generated in php. 
         */
        case "submitFortune":
          $insert = array(trim($fortune["text"]),  $fortune["user"], time() );
         
          $result = pg_prepare($pg_conn, "submitFortune",
          'INSERT INTO fortunes ( text, uploader, uploaddate)
           VALUES ($1, $2, $3) 
           RETURNING fortuneid, text, upvote, downvote, views, uploaddate');

          $result = pg_execute($pg_conn, "submitFortune", $insert);
          $this->setError($result, "submitFortune: inserting fortune");
          $inserted = pg_fetch_assoc($result);

          $this->processResult($inserted);

          
          $result = pg_prepare($pg_conn, "insertView",
          "INSERT INTO viewed
          VALUES ($1, $2, 1, 'false')");

          $insert = array($fortune["user"], $inserted["fortuneid"]);
          $result = pg_execute($pg_conn, "insertView", $insert);
          $this->setError($result, "submitFortune: inserting into viewed");

          break;

        case "submitVote":
          if($fortune["vote"] === true)
          {
              $fortune["vote"] = 1;
          }
          else
          {
              $fortune["vote"] = -1;
          }

          $insert = array($fortune["fortuneid"],  $fortune["user"], $fortune["vote"]);    
          $result = pg_prepare($pg_conn, "oldVote",
           'SELECT vote from viewed WHERE fortuneid = $1 AND userid = $2');
          $result = pg_execute($pg_conn, "oldVote", array($fortune["fortuneid"],  $fortune["user"]));
          $this->setError($result, "submitVote: selecting original vote");

          $row = pg_fetch_row($result);
          $oldvote = $row[0];

          $result = pg_prepare($pg_conn, "submitVote",
           'UPDATE viewed SET vote = $3 WHERE fortuneid = $1 AND userid = $2 RETURNING vote');
          $result = pg_execute($pg_conn, "submitVote", $insert);
          $this->setError($result, "submitVote: updating views");


          if(pg_num_rows($result) != false)
          {
            if($oldvote == 1 )
            {
                $result = pg_prepare($pg_conn, "upVoteDown",
                'UPDATE fortunes SET upvote =  (upvote - 1) WHERE fortuneid = $1 RETURNING upvote');
                $result = pg_execute($pg_conn, "upVoteDown", array($fortune["fortuneid"]));
            }
            else if($oldvote == -1)
            {
                $result = pg_prepare($pg_conn, "downVoteDown",
                'UPDATE fortunes SET downvote = (downvote - 1) WHERE fortuneid = $1 RETURNING downvote');
                $result = pg_execute($pg_conn, "downVoteDown", array($fortune["fortuneid"])); 
            }
            
            if($fortune["vote"] == 1)
            {
              $result = pg_prepare($pg_conn, "upVote",
              'UPDATE fortunes SET upvote =  1 + upvote WHERE fortuneid = $1 RETURNING upvote');
              $result = pg_execute($pg_conn, "upVote", array($fortune["fortuneid"]));
            }
            else 
            {
              $result = pg_prepare($pg_conn, "downVote",
              'UPDATE fortunes SET downvote = 1 + downvote WHERE fortuneid = $1 RETURNING downvote');
              $result = pg_execute($pg_conn, "downVote", array($fortune["fortuneid"]));             
            }
          }
          $this->setError($result, "submitVote: updating fortunes");
          $this->processResult(pg_fetch_assoc($result));
          break;

        case "submitFlag":

          $insert = array($fortune["fortuneid"],  $fortune["user"], time());    

          $result = pg_prepare($pg_conn, "submitFlag",
           'UPDATE viewed SET flagged = true, atime = $3 WHERE fortuneid = $1 AND userid = $2 RETURNING flagged');
          $result = pg_execute($pg_conn, "submitFlag", $insert);
          $this->setError($result, "submitFlag: updating viewed");

          $this->processResult(pg_fetch_assoc($result));

          $result = pg_prepare($pg_conn, "flagUp",
          'UPDATE fortunes SET flags =  (1 + flags) WHERE fortuneid = $1 RETURNING flags');
          $result = pg_execute($pg_conn, "flagUp", array($fortune["fortuneid"]));
          $this->setError($result, "submitFlag: updating fortunes");

          //$result = pg_prepare($pg_conn, "countflags


          break;

        default:
          $this->processResult(false);
          break;
      }

// Logs -------------------------------------------------------------

      date_default_timezone_set('America/Los_Angeles');
      $current = file_get_contents('serverlogs.log');
      $current .= "\n\n";
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
