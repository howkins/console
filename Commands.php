<?php

class Commands {
  public $commands = [];
  
  public function setCommand(AbstractCommand $command){
    $this->commands[$command->getName()] = $command;
  }
  
  public function get($command){
    return $this->commands[$command];
  }
  
  public function getAllowedCommands(){
    $allowedCommands = [];
    foreach ($this->commands as $command) {
      $allowedCommands[$command->getName()] = ["options"=>$command->getOptions()];
    }
    return $allowedCommands;
  }
  
}