<?php


class Console {
  public $argv;
  public $tokenCommand;
  public $tokenOptions;
  public $commands;
  public $allowedCommands = [];

  public function __construct($argv){
    $this->argv = $argv;
    array_shift($argv);
    $this->tokenCommand = array_shift($argv);
    $this->tokenOptions = $argv;
  }
  
  public function setCommands($commands){
      $this->commands = $commands;
      foreach ($commands->getAllowedCommands() as $name => $command) {
        $this->allowedCommands[$name] = $command;
      }
  }

  public function getCommand(){
    if($this->checkCommand()){
      return $this->commands->get($this->tokenCommand);
    }
    return null;
  }
  
  public function getTokenOptions($selection = null){
    $tokenOptions = [];
    foreach ($this->tokenOptions as $option) {
      $this->checkOption($option);
      list($key, $value) = $this->getOptionParts($option);
      if($selection == 'key'){
        $tokenOptions[$key] = $key;
      }elseif ($selection == 'value') {
        $tokenOptions[$key] = $value;
      }else{
        $tokenOptions[$key] = $option;
      }
    }
    
    return $tokenOptions;
  }
  
  public function checkCommand(){
    return array_key_exists($this->tokenCommand, $this->allowedCommands);
  }
  
  public function checkOptions(){
    foreach ($this->tokenOptions as $option) {
      $this->checkOption($option);
    }
    return true;
  }
  
  public function checkOption($option){
    list($key, $value) = $this->getOptionParts($option);
    $allowedOptions = $this->getAllowedOptions();
    if(array_key_exists($key, $allowedOptions)){
      return true;
    }
    throw new \Exception("This option `$option` does not exist.", 1);
  }
  
  public function getAllowedOptions(){
    if(isset($this->allowedCommands[$this->tokenCommand]['options'])){
      return $this->allowedCommands[$this->tokenCommand]['options'];
    }
    return [];
  }
  
  private function getOptionParts($option){
    $parts = explode('=', $option);
    if(count($parts) < 2){
      return [$option,null];
    }
    return $parts;  
  }
  
  public function run(){
    if($this->checkCommand() && $this->checkOptions()){
      $this->getCommand()->execute($this->getTokenOptions('value'));
    }
  }
}