<?php

abstract class AbstractCommand {
  // REQUIRED FOR COMMAND CLASSES
  abstract function getName();
  abstract function getOptions();
  abstract function execute($options);
  
  // HELP FUNCTIONS
  const TITLE_PAD = 35;
  
  protected function helper(){
    $this->cmdLine("");
    $this->cmdLine("Usage", 'yellow');
    $this->cmdLine("\t".$this->name." [options]");
    $this->cmdLine("");
    $this->cmdLine("Options:", 'yellow');
    foreach ($this->options as $optionName => $option) {
      if(isset($option['helper']) && isset($option['helper']['title']) && isset($option['helper']['content']) ) {
        $this->cmdSpan("\t".str_pad($option['helper']['title'], self::TITLE_PAD), 'green');
          $this->cmdSpan("   ".$option['helper']['content']."");
          $this->cmdLine("");
      }
    }
    $this->cmdLine("");
  }
  
  protected function cmdLine($message, $color=null){
    echo $this->cmdLineColor($color).$message.$this->cmdLineColor(null)."\n";
  }
  protected function cmdSpan($message, $color=null){
    echo $this->cmdLineColor($color).$message.$this->cmdLineColor(null);
  }
  
  protected function cmdLineColor($color){
    switch ($color) {
      case 'black': return `tput setaf 0`; break;
      case 'red': return `tput setaf 1`; break;
      case 'green': return `tput setaf 2`; break;
      case 'yellow': return `tput setaf 3`; break;
      case 'blue': return `tput setaf 4`; break;
      case 'magenta': return `tput setaf 5`; break;
      case 'cyan': return `tput setaf 6`; break;
      case 'white': return `tput setaf 7`; break;
      default: return `tput sgr0`; break;
    }
  }
  
}
