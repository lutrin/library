<?php
class typeValidator {
  public static function isAlphaNumeric( $str ) {
    return preg_match( '/[a-zA-Z0-9]*/', $str ) ? $str : false; 
  }

  /****************************************************************************/
  public static function isNumeric( $str ) {
    return preg_match( '/[0-9]*/', $str ) ? $str : false; 
  }

  /****************************************************************************/
  public static function isNumericList( $lst ) {
    if( !$lst ) {
      return false;
    }
    if( !is_array( $lst ) ) {
      $lst = array( $lst );
    }
    foreach( $lst as $str ) {
      if( !self::isNumeric( $str ) ) {
        return false;
      }
    }
    return $lst;
  }

  /****************************************************************************/
  public static function isId( $str ) {
    return preg_match( '/[a-z0-9_]*/', $str ) ? $str : false; 
  }

  /****************************************************************************/
  public static function isSingleId( $str ) {
    return preg_match( '/[a-z0-9]*/', $str ) ? $str : false; 
  }

  /****************************************************************************/
  public static function isSingleIdCommaList( $str ) {
    return preg_match( '/[a-z0-9\,]*/', $str ) ? $str : false; 
  }

  /****************************************************************************/
  public static function isBoolean( $str ) {
    return $str === 1 ||
           $str === "1" ||
           $str === true ||
           $str === "true" ||
           $str === 0 ||
           $str === "0" ||
           $str === "" ||
           $str === false ||
           $str === "false";
  }

  /****************************************************************************/
  public static function isEmpty( $str ) {
    return $str === "";
  }

  /****************************************************************************/
  public static function isYear( $str ) {
    return preg_match( '/^(19[0-9]{2}|2[0-9]{3})$/', $str ) ? $str : false;
  }

  /****************************************************************************/
  public static function isValidFormat( $format, $str ) {
    return preg_match( "'$format'", $str );
  }
}
