<?php

  class Server{

    public function serve(){
      $method = $_GET['action'];
      echo  $method;
      switch($method){
        case "getFortunesSubmitted":
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
          break;
      }
    }



  }

  $server = new Server;
  $server->serve();
?>
