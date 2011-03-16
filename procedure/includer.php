<?php

class Includer {
  protected static $instance;
  protected static $includeList;

  /****************************************************************************/
  public static function instance() {
    global $INCLUDE_LIST;
    if( !isset( self::$instance ) ) {
      self::$instance = new Includer();
      self::$includeList = $INCLUDE_LIST;
    }
    return self::$instance;
  }

  /****************************************************************************/
  public static function add( $newInclude ) {
    self::instance();
    $includeList = self::addToList( $newInclude, array() );
    self::append( $includeList );
  }

  /****************************************************************************/
  protected static function addToList( $newInclude, $includeList ) {
    $dependList = false;
    foreach( self::makeArray( $newInclude ) as $includeItem ) {
      if( in_array( $includeItem, array_keys( self::$includeList ) ) ) {
        array_push( $includeList, self::$includeList[$includeItem]["path"] );
        $dependList = isset( self::$includeList[$includeItem]["depend"] )? self::$includeList[$includeItem]["depend"]: array();;

        unset( self::$includeList[$includeItem] );
        if( $dependList ) {
          $includeList = self::addToList( $dependList, $includeList );
        } else {
          $includeList = self::append( $includeList );
        }
      }
    }
    return $includeList;
  }

  /****************************************************************************/
  protected static function append( $includeList ) {
    while( $includeList ) {
      $path = array_pop( $includeList );
      include_once $path;
    }
    return $includeList;
  }

  /****************************************************************************/
  protected static function makeArray( $newInclude ) {
    return array_reverse( ( !is_array( $newInclude )? array( $newInclude ): $newInclude ) );
  }
}
