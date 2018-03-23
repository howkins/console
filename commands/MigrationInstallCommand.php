<?php
include_once(dirname(__DIR__).'/AbstractCommand.php');

class MigrationInstallCommand extends AbstractCommand {
  public $name = 'migrate:install';
  public $options = [
    '-h'=>['helper'=>['title'=>'-h', 'content'=>'Display this help message']],
    '--database'=>['helper'=>['title'=>'--database[=DATABASE]', 'content'=>'The database connection to use.']],
    '--path'=>['helper'=>['title'=>'--path[=PATH]', 'content'=>'The path of migrations files to be executed.']],
  ];
  public function __construct(){
    $this->init();
  }
  
  public function execute($options){
    // Run options
    if(array_key_exists('-h', $options)){
      $this->helper(); exit;
    }
    // Run command
    $this->cmdSpan("\n- Installing: ");
    if(!$this->install()){
      throw new \Exception("Not install table", 1);
    } 
    $this->cmdLine("done", 'green');
    exit;
  }
  
  public function getName(){
    return $this->name;
  }
  public function getOptions(){
    return $this->options;
  }
  
  
  private function init(){
    $this->conn = pg_connect ( 'host='.PGDB_SRV.' port='.PGDB_PORT.' dbname='.PGDB_DB.' user='.PGDB_USR.' password='.PGDB_PASS.'' );
    if(!$this->conn){
      exit('Error connection');
    }
  }
  private function install(){
    return pg_query($this->conn, 'create table public.ecms_migrations (
            	id serial primary key,
              migration varchar,
              batch varchar
            );
            GRANT ALL ON SEQUENCE public.ecms_migrations_id_seq TO iusrpmt;
            GRANT ALL ON SEQUENCE public.ecms_migrations_id_seq TO postgres;
            GRANT ALL ON TABLE public.ecms_migrations TO iusrpmt;
            GRANT ALL ON TABLE public.ecms_migrations TO postgres;');
  }


  
}