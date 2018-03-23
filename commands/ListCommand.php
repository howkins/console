<?php
include_once(dirname(__DIR__).'/AbstractCommand.php');

class ListCommand extends AbstractCommand {
  public $name = 'list';
  public $options = [
    'list'=>['helper'=>['title'=>'list', 'content'=>'Show this list message']],
    'migrate:install'=>['helper'=>['title'=>'migrate:install', 'content'=>'Create the migration repository']],
    'migrate'=>['helper'=>['title'=>'migrate', 'content'=>'Migrate the new repositories']],
  ];
  
  public function execute($options){
    
    $this->availableCommandsHelper();
    exit;
  }
  
  public function getName(){
    return $this->name;
  }
  public function getOptions(){
    return $this->options;
  }
  
  private function availableCommandsHelper(){
    $this->cmdLine("");
    $this->cmdLine("Usage", 'yellow');
    $this->cmdLine("\tCommand [options]");
    $this->cmdLine("");
    $this->cmdLine("Available commands:", 'yellow');
    foreach ($this->options as $optionName => $option) {
      if(isset($option['helper']) && isset($option['helper']['title']) && isset($option['helper']['content']) ) {
        $this->cmdSpan("\t".str_pad($option['helper']['title'], self::TITLE_PAD), 'green');
          $this->cmdSpan("   ".$option['helper']['content']."");
          $this->cmdLine("");
      }
    }
    $this->cmdLine("");
  }

}