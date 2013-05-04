<?php

  class Server{

    public function serve(){
      $method = $_GET['action'];
      echo  $method;
      switch($method){
        case "getFortunesSubmitted":
          break;
        case: "getFortune":
          break;
        case: "submitVote":
          break;
        case: "submitFortune":
          break;
        case: "submitFlag":
          break;
        case: "submitView":
          break;
        default:
          echo "Default";
        break;
      }
    }



  }

  $server = new Server;
  $server->serve();
?>
