<?php
class DB {
  protected static $instance;
  protected static $dbLink;

  /****************************************************************************/
  public static function instance() {
    if( !isset( self::$instance ) ) {

      # Create a new instance, connected
      self::$instance = new DB();
      self::$dbLink = self::$instance->connect();
    }
    return self::$instance;
  }
  
  /****************************************************************************/
  public static function connect( $exitOnError = false ) {
    global $DBDATA;
    $dbLink = @mysql_connect( $DBDATA['host'], $DBDATA['user'], $DBDATA['password'] );
    if( !$dbLink ) {
      print "No connection has been established to the database";
      if( $exitOnError ) {
        exit;
      }
      return null;
    }

    if( !@mysql_select_db( $DBDATA['database'], $dbLink ) ) {
      print "No connection has been established to the database";
      if( $exitOnError ) {
        exit;
      }
      return null;
    }

    mysql_query( "SET CHARACTER SET 'utf8'", $dbLink );
    if( $exitOnError ) {
      if( !$dbLink ) {
        print "Connection Database Error";
        exit;
      }
    }
    return $dbLink;
  }

  /****************************************************************************/
  public static function mysql_prep( $value ) {
    $magic_quotes_active = get_magic_quotes_gpc();
    if( $magic_quotes_active ) {
      $value = stripslashes( $value );
    }
#    $value = mysql_real_escape_string( $value );
    if( !$magic_quotes_active ) {
      if( strpos( str_replace( "\'", "", " $value" ), "'") != false ) {
        $value = addslashes( $value );
      }
    }
    return trim( $value );
  }

  /****************************************************************************/
  public static function prepareValue( $paramValue, $field ) {
    $value = $paramValue;

    # set default if empty
    if( $value === "" ) {
      return $field["default"];
    }

    # integer
    if( in_array( $field["type"], array( "int", "mediumint", "tinyint", "bigint" ) ) ) {
      return intval( $value );
    }

    # decimal
    if( in_array( $field["type"], array( "decimal", "float", "double", "real" ) ) ) {
      return preg_replace( "/\,/", ".", preg_replace( "/^[^0-9\.\,]/", "", $value ) );
    }

    # date and datetime
    if( in_array( $field["type"], array( "date", "datetime" ) ) ) {
      $time = strtotime( $value );
      if( $field["type"] == "date" ) {
        return date( "Y-m-d", $time );
      }
      return date( "Y-m-d h:i:s", $time );
    }
    return self::mysql_prep( $value );
  }

  /****************************************************************************/
  public static function runSql( $sql ) {
    self::instance();
    if( !$result = mysql_query( $sql, self::$dbLink ) ) {
      print "Problème de connexion à la base de données.$sql";
      exit;
    }
    return $result;
  }

  /****************************************************************************/
  public static function getInfo( $table ) {
    return self::fetch( "SHOW COLUMNS FROM $table;" );
  }

  /****************************************************************************/
  public static function select( $params ) {
    return self::fetch( self::prepareSelectSql( $params ) );
  }

  /****************************************************************************/
  public static function count( $params ) {
    return mysql_num_rows( self::runSql( self::prepareSelectSql( $params ) ) );
  }

  /****************************************************************************/
  public static function insert( $params ) {
    # table
    if( !isset( $params["table"] ) ) {
      print "Impossible to execute insert without table name.";
      exit;
    }
    $table = $params["table"];

    # field
    $field = isset( $params["field"] )? ( " (" . join( ",", self::ensureArray( $params["field"] ) ) . ")" ): "";

    $sql = "INSERT INTO $table$field ";

    # values part
    if( isset( $params["values"] ) || isset( $params["valuesList"] ) ) {
      $valuesList = array();

      # no quote
      if( isset( $params["noquote"] ) && $params["noquote"] ) {
        if( isset( $params["values"] ) ) {
          $valuesList = array( join( ",", $params["values"] ) );
        } else {
          $valuesList = array_map( function( $row ) {
            return join( ",", $row );
          }, $params["valuesList"] );
        }

      # with quote
      } else {
        if( isset( $params["values"] ) ) {
          $valuesList = array(
            "'" . join( "','", array_map( self::mysql_prep, $params["values"] ) ) . "'"
          );
        } else {
          $valuesList = array_map( function( $row ) {
            return "'" . join( "','", $row ) . "'";
          }, $params["valuesList"] );
        }
      }
      if( !$valuesList ) {
        print "Impossible to execute insert without values.";
        exit;
      }

      # get return mode
      $sql .= "VALUES ";
      if( isset( $params["return"] ) && $params["return"] == "id" ) {

        # get id list
        return array_map( function( $value ) use( $sql ) {

          # execute query
          DB::runSql( "$sql($value);" );
          if( mysql_affected_rows() ) {
            return mysql_insert_id();
          }
          return false;
        }, $valuesList );
      } else {
        $values = "(" . join( "),(", $valuesList ) . ")";

        # execute query
        self::runSql( "$sql$values;" );
      }
      return mysql_affected_rows();
    } elseif( isset( $params["select"] ) ) {
      self::runSql( $sql . "(" . self::prepareSelectSql( $params["select"] ) . ")" );
      if( mysql_affected_rows() ) {
        return mysql_insert_id();
      }
    }
    return false;
  }

  /****************************************************************************/
  public static function update( $params ) {
    # table
    if( !isset( $params["table"] ) ) {
      print "Impossible to execute update without table name.";
      exit;
    }
    $table = $params["table"];

    # set
    if( !isset( $params["set"] ) ) {
      print "Impossible to execute update without set list.";
      exit;
    }
    $setList = array();
    if( isset( $params["noquote"] ) && $params["noquote"] ) {
      foreach( $params["set"] as $field => $value ) {
        $setList[] = "$field=$value";
      }
    } else {
      foreach( $params["set"] as $field => $value ) {
        $setList[] = "$field='" . self::mysql_prep( $value ) . "'";
      }
    }

    # condition
    $condition = "";
    if( isset( $params["where"] ) ) {
      $condition = " WHERE (" . join( ") AND (", self::ensureArray( $params["where"] ) ) . ")";
    }

    # execute
    self::runSql( "UPDATE $table SET " . join( ",", $setList ) . $condition . ";" );
    return mysql_affected_rows();
  }

  /****************************************************************************/
  public static function delete( $params ) {
    # table
    if( !isset( $params["table"] ) ) {
      print "Impossible to execute delete without table name.";
      exit;
    }
    $table = $params["table"];

    # condition
    $condition = "";
    if( isset( $params["where"] ) ) {
      $condition = " WHERE (" . join( ") AND (", self::ensureArray( $params["where"] ) ) . ")";
    }

    # execute
    self::runSql( "DELETE FROM $table$condition" );
    return mysql_affected_rows();
  }

  /****************************************************************************/
  public static function prepareSelectSql( $params ) {
if( !$params ) {
var_dump(debug_backtrace(false));
}
    # field list
    if( !isset( $params["field"] ) ) {
      $fieldList = "*";
    } else {
      $fieldList = join( ",", self::ensureArray( $params["field"] ) );
    }

    # table list
    if( !isset( $params["table"] ) ) {
      print "Impossible to execute query without table list.";
      exit;
    }
    $tableList = join( ",", self::ensureArray( $params["table"] ) );

    # condition
    $condition = "";
    if( isset( $params["where"] ) ) {
      $condition = " WHERE (" . join( ") AND (", self::ensureArray( $params["where"] ) ) . ")";
    }

    # order
    $order = "";
    if( isset( $params["order"] ) ) {
      $order = " ORDER BY " . join( ",", self::ensureArray( $params["order"] ) );
    }

    # execute query
    return "SELECT {$fieldList} FROM {$tableList}{$condition}{$order}";    
  }

  /****************************************************************************/
  protected static function fetch( $sql ) {
    $result = db::runSql( $sql );
    $fieldList = array();
    while( $item = mysql_fetch_assoc( $result ) ) {
      $fieldList[] = $item;
    }
    return $fieldList;
  }

  /****************************************************************************/
  public static function ensureArray( $array ) {
    if( !is_array( $array ) ) {
      return array( $array );
    }
    return $array;
  }
}
