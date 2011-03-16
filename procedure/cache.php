<?php
cache::cleanOldFile();

class cache {
  protected $filename;
  protected $content;
  protected $simulate;
  public    $ext;

  /****************************************************************************/
  public static function cleanOldFile() {
    global $CACHEPATH, $CACHE_PERIOD;
    if( $handle = opendir( $CACHEPATH ) ) {
      $endPeriod = mktime( 0, 0, 0, date("m"), date("d") + $CACHE_PERIOD, date("Y") );
      while( false !== ( $file = readdir( $handle ) ) ) {
        if( $file != "." && $file != ".." ) {
          $filePath = $CACHEPATH . $file;
          $stat = stat( $filePath );
          if( $stat["atime"] > $endPeriod ) {
             unlink( $filePath );
          }
        }
      }
    }
  }

  /****************************************************************************/
  public function __construct( $filename, $extList = false ) {
    global $CACHEPATH;
    $this->filename = $CACHEPATH . $filename;
    $this->content = "";
    $this->simulate = false;
    if( $extList && is_array( $extList ) ) {
      foreach( $extList as $extItem ) {
        $tmpFilename = $this->filename . "." . $extItem;
        if( file_exists( $tmpFilename ) ) {
          $this->filename = $tmpFilename;
          $this->ext = $extItem;
          $this->content = file_get_contents( $this->filename );
          break;
        }
      }
    } else {
      if( file_exists( $this->filename ) ) {
        $this->content = file_get_contents( $this->filename );
      }
    }
  }

  /****************************************************************************/
  public function loaded( $nocache = false ) {
    $this->simulate = $nocache;
    return !$nocache && $this->content != "";
  }

  /****************************************************************************/
  public function save( $content, $ext = "" ) {
    $this->setContent( $content );
    if( !$this->simulate ) {
      $this->filename = $this->filename . ( $ext? ".$ext": "" );
      $fp = fopen( $this->filename, "w+" ); 
      fwrite( $fp, $this->content );
      fclose( $fp );
    }
  }

  /****************************************************************************/
  public function setContent( $content ) {
    $this->content = $content;
  }

  /****************************************************************************/
  public function getContent() {
    return $this->content;
  }
}
