<?php
function cleanAll() {
  return ( clearCache() && /*compressFile() && */mergeFile() )? "Ménage réussi": "Ménage échoué";
}

/******************************************************************************/
function clearCache() {
  global $CACHEPATH;

  # get list
  $html = "Suppression des fichiers caches<br />";
  if( $handle = opendir( $CACHEPATH ) ) {
    while( false !== ( $file = readdir( $handle ) ) ) {
      if( $file != "." && $file != ".." && $file != ".svn" ) {
        if( unlink( $CACHEPATH . $file ) ) {
          $html .= $file . " : effacé<br />";
        } else {
          $html .= $file . " : echoué<br />";
        }
      }
    }
  } else {
    print "Erreur, aucun fichier effacé.";
    return false;
  }
  return $html;
}

/******************************************************************************/
function compressFile() {
  global $COMPRESS_LIST;

  #html
  foreach( $COMPRESS_LIST["html"] as $folder ) {
    if( $handle = opendir( $folder ) ) {
      print "Compresser des fichiers du dossier $folder<br />";
      while( false !== ( $file = readdir( $handle ) ) ) {
        if( $file != "." && $file != ".." ) {
          $filePath = $folder . $file;
          $print = $file . ": " . filesize( $filePath );
          file_put_contents( $filePath, removeBOM( Minify_HTML::minify( file_get_contents( $filePath ) ) ) );
          $print .= " => " . filesize( $filePath );
          print $print . "<br />";
        }
      }
    }
  }

  #css
  foreach( $COMPRESS_LIST["css"] as $folder ) {
    if( $handle = opendir( $folder ) ) {
      print "Compresser des fichiers du dossier $folder<br />";
      while( false !== ( $file = readdir( $handle ) ) ) {
        if( $file != "." && $file != ".." ) {
          $filePath = $folder . $file;
          $print = $file . ": " . filesize( $filePath );
          file_put_contents( $filePath, removeBOM( cssmin::minify( file_get_contents( $filePath ) ) ) );
          $print .= " => " . filesize( $filePath );
          print $print . "<br />";
        }
      }
    }
  }

  #js
  foreach( $COMPRESS_LIST["js"] as $folder ) {
    if( $handle = opendir( $folder ) ) {
      print "Compresser des fichiers du dossier $folder<br />";
      while( false !== ( $file = readdir( $handle ) ) ) {
        if( $file != "." && $file != ".." ) {
          $filePath = $folder . $file;
          $print = $file . ": " . filesize( $filePath );
          file_put_contents( $filePath, removeBOM( JSMin::minify( file_get_contents( $filePath ) ) ) );
          $print .= " => " . filesize( $filePath );
          print $print . "<br />";
        }
      }
    }
  }
  return true;
}

/******************************************************************************/
function mergeFile() {
  global $MERGE_LIST, $MERGEPATH;

  foreach( $MERGE_LIST as $page => $typeList ) {
    foreach( $typeList as $type => $fileList ) {
      $fileBuffer = array();
      foreach( $fileList as $file ) {
        array_push( $fileBuffer, file_get_contents( $file ) );
      }
      print "Fusionnement de $page.$type<br />";
      $bufferContent = join( "", $fileBuffer );
      if( $type == "css" ) {
        $bufferContent = cssmin::minify( $bufferContent );
      } elseif( $type == "js" ) {
        $bufferContent = JSMin::minify( $bufferContent );
      }
      file_put_contents( $MERGEPATH . $page . "." . $type, removeBOM( $bufferContent ) );
    }
  }
  return true;
}

/******************************************************************************/
function removeBOM ($str = "" ) {
  if( substr( $str, 0, 3 ) == pack( "CCC" , 0xef , 0xbb , 0xbf ) ) {
    $str = substr( $str, 3 );
  }
  return $str;
}
