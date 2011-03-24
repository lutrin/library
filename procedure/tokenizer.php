<?php

class Tokenizer {
  /****************************************************************************/
  public static function create( $type ) {
    return $_SESSION["token_$type"] = md5( uniqid( mt_rand(), true ) );
  }

  /****************************************************************************/
  public static function exists( $type, $token ) {
    $tokenType = "token_$type";
    return isset( $_SESSION[$tokenType] ) && ( $_SESSION[$tokenType] == $token );
  }

  /****************************************************************************/
  public static function validate( $type, $token ) {
    if( !self::exists( $type, $token ) ) {
      print "Accès dénié";
      exit;
    }
  }

  /****************************************************************************/
  public static function delete( $type ) {
    unset( $_SESSION["token_$type"] );
  }
}
