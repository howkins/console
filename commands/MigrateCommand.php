<?php
include_once(dirname(__DIR__).'/AbstractCommand.php');
include_once(dirname(dirname(__DIR__)).'/collection.php');

class MigrateCommand extends AbstractCommand {
  public $name = 'migrate';
  public $options = [
    '-h'=>['helper'=>['title'=>'-h', 'content'=>'Display this help message']],
    '--force'=>['helper'=>['title'=>'--force', 'content'=>'Continue even if an SQL error occurs.']],
    '--path'=>['helper'=>['title'=>'--path=[=PATH]', 'content'=>'Set path to migration files.']],
  ];
  public $migrationDir;
  const FILE_EXTENSION = 'sql';
  public function __construct(){
    $this->init();
  }
  
  public function execute($options){
    // Run options
    if(array_key_exists('-h', $options)){
      $this->helper(); exit;
    }
    if(array_key_exists('--path', $options)){
      $this->setMigrationDir($options['--path']);
    }

    // Run command
    $files = $this->fetchMigrationFiles();
    $filesToExecute = [];
    $existedFiles = [];
    
    $this->fetchMigrations(100,0,function($migrations) use($files, &$existedFiles){
      foreach ($files->pluck('migration') as $file) {
        if(in_array($file, $migrations->pluck('migration'))){
          $existedFiles[] = $file;
        }
      }
    });
    $filesToExecute = array_values(array_diff($files->pluck('migration'), $existedFiles));

    if($filesToExecute){
      // get next batch id 
      $nextBatchId = (int)$this->getLastBatchId()->last();
      // Find from fetchMigrationFiles the path of the file
      pg_query($this->conn, "END; BEGIN;");
      $errors = [];
      foreach ($filesToExecute as $fileToExecute) {
        $this->cmdSpan(" -Execute [$fileToExecute.".self::FILE_EXTENSION."]: ");
        
        $result = $this->insertMigration($fileToExecute, $nextBatchId);
        if($result !== true){
          $errors[] = $result;
        }
        $files_ = $files->filter(function($file) use ($fileToExecute){
          return $file['migration'] == $fileToExecute;
        });
        $filePaths = (new Collection($files_))->pluck('path');
        foreach ($filePaths as $filePath) {
          $result = $this->executeFileMigration($filePath);
          if($result !== true){
            $errors[] = $result;
            $this->cmdLine("error ($result)", 'red');
            if($errors && !array_key_exists('--force', $options)){
              break;
            }
          }else{
            $this->cmdLine("ok", 'green');
          }
        }
        if($errors && !array_key_exists('--force', $options)){
          break;  
        }
      }
      
      if($errors){
        pg_query($this->conn, "ROLLBACK;");
      }else{
        pg_query($this->conn, "COMMIT;");
      }
    }else{
      $this->cmdLine("Doesnt have migration files for executing!", 'green');
    }
    
    if(false){
      throw new \Exception("Not install table", 1);
    } 
    exit;
  }
  
  public function getName(){
    return $this->name;
  }
  public function getOptions(){
    return $this->options;
  }
  public function setMigrationDir($dir){
    $this->migrationDir = realpath($dir).DIRECTORY_SEPARATOR;
  }
  private function init(){
    $this->conn = pg_connect ( 'host='.PGDB_SRV.' port='.PGDB_PORT.' dbname='.PGDB_DB.' user='.PGDB_USR.' password='.PGDB_PASS.'' );
    if(!$this->conn){
      exit('Error connection');
    }
    $this->setMigrationDir('/var/www/main/zhelezov/migrations');
  }
  private function fetchMigrations($pChunk, $pOffset=0, $cb){
    $offset = $pOffset;
    $chunkResults = pg_query($this->conn, 'SELECT id, migration, batch FROM public.ecms_migrations ORDER BY batch ASC OFFSET '.(int)$offset. ' LIMIT '.(int)$pChunk);
    if(pg_affected_rows($chunkResults)){
      $cb( new Collection(pg_fetch_all($chunkResults)) );
      $newOffset = $pChunk + $pOffset;
      $this->fetchMigrations($pChunk, $newOffset, $cb);
    }
    // $exec = pg_query($this->conn, 'SELECT id, migration, batch FROM public.ecms_migrations ORDER BY batch ASC');
    // return new Collection(pg_fetch_all($exec) ?: []);
    return true;
  }
  private function getLastBatchId(){
    $result = pg_query($this->conn, 'SELECT batch FROM public.ecms_migrations ORDER BY id DESC LIMIT 1');
    return new Collection(pg_fetch_all_columns($result, 0));
  }
  private function fetchMigrationFiles(){
    $scandir = scandir($this->migrationDir);
    $files = [];
    if($scandir){
      foreach ($scandir as $key => $file) {
        $pathfile = $this->migrationDir . $file;
        if ( !in_array( $file ,array( ".",".." ) ) && !is_dir($pathfile) && pathinfo($pathfile, PATHINFO_EXTENSION) == self::FILE_EXTENSION ){
          $files[] = [
            'migration' => basename($file, '.'.self::FILE_EXTENSION),
            'path' => $pathfile,
            'extension' => self::FILE_EXTENSION,
          ];
          
        }
      }
    }
    return new Collection($files);
  }
  private function insertMigration($migration, $batch){    
    @pg_query($this->conn, "INSERT INTO public.ecms_migrations (migration, batch) VALUES ('".$migration."', '".($batch)."');");
    
    if(pg_last_error ( $this->conn )){
      return pg_last_error ( $this->conn );
    }
    return true;
  }
  private function executeFileMigration($file){
    $resource = fopen($file, "r");
    if(filesize($file) && $contents = fread($resource, filesize($file))){
        @pg_query($this->conn, "$contents");
        
        if(pg_last_error ( $this->conn )){
          return pg_last_error ( $this->conn );
        }
    }else{
      return 'The file is empty.';
    }
    fclose($resource);
    return true;
    
  }
}








