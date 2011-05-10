var _c = {
  selectList: [],
  ajaxList: [],

  /****************************************************************************/
  select: function( s, f ) {
    if( f || !this.selectList[s] ) {
      var e = $( s );
      this.selectList[s] = e;
      return e;
    }
    return this.selectList[s];
  },

  /****************************************************************************/
  modalWindow: {
    parent:"body",
    windowId:"modal",
    content:null,
    width:480,
    height:480,
    close:function() {  
      $( ".modal-window" ).remove();
      $( ".modal-overlay" ).remove();
    },  
    open:function() {
      var modal = "";
      modal += "<div class=\"modal-overlay\"></div>";
      modal += "<div id=\"" + this.windowId + "\" class=\"modal-window\" style=\"width:" + this.width + "px; height:" + this.height + "px; margin-top:-" + ( this.height / 2 ) + "px; margin-left:-" + ( this.width / 2 ) + "px;\">";
      modal += this.content;
      modal += "</div>";

      $( this.parent ).append( modal );

      $( ".modal-window" ).append( "<a class=\"close-window\"></a>" );
      $( ".close-window" ).click( function() {
        _c.modalWindow.close();
      } );
      $(".modal-overlay").click( function() {
        _c.modalWindow.close();
      } );
    }
  },

  /****************************************************************************/
  replaceAccents: function( s ) {
    var n = "";
    n = s.replace( /[àáâãäå]/g , "a" );
    n = n.replace( /[æ]/g, "ae" );
    n = n.replace( /[ç]/g, "c" );
    n = n.replace( /[èéêë]/g, "e" );
    n = n.replace( /[ìíîï]/g, "i" );
    n = n.replace( /[ñ]/g, "n" );
    n = n.replace( /[òóôõöø]/g, "o" );
    n = n.replace( /[ùúûü]/g, "u" );
    n = n.replace( /[ýÿ]/g, "y" );
    n = n.replace( /[ÀÁÂÃÄÅ]/g, "A" );
    n = n.replace( /[Æ]/g, "AE" );
    n = n.replace( /[Ç]/g, "C" );
    n = n.replace( /[Ð]/g, "D" );
    n = n.replace( /[ÈÉÊË]/g, "E" );
    n = n.replace( /[ÌÍÎÏ]/g, "I" );
    n = n.replace( /[Ñ]/g, "N" );
    n = n.replace( /[ÒÓÔÕÖØ]/g, "O" );
    n = n.replace( /[ÙÚÛÜ]/g, "U" );
    n = n.replace( /[Ý]/g, "Y" );
    return ( n );
  },

  /****************************************************************************/
  format: function( s, l ) {
    l = this.makeArray( l );
    return s.replace( /\{(\d+)\}/g, function( c,d ){ return l[d] } );
  },

  /****************************************************************************/
  findItem: function( idx, v, lst ) {
    lst = this.makeArray( lst );
    for( var i = 0, len = lst.length; i < len; i++ ) {
      if( lst[i][idx] == v ) {
        return lst[i];
      }
    }
    return false;
  },

  /****************************************************************************/
  eachItem: function( lst, callback ) {
    lst = this.makeArray( lst );
    var i = 0, l = lst.length, stop = false; while( i < l && !stop ) {
      stop = callback( lst[i++] );
    }
    return stop;
  },

  /****************************************************************************/
  inList: function( val, lst ) {
    return this.eachItem( lst, function( elm ) {
      return val == elm;
    } );
  },

  /****************************************************************************/
  mergeList: function( lst ) {
    var l = lst.length,
        m = [],
        i;
    if( !l ) {
      return m;
    }
    m = lst[--l];
    while( l-- ) {
      for( var i in lst[l] ) {
        if( !m[i] ) {
          m[i] = lst[l][i];
        }
      }
    }
    return m;
  },

  /****************************************************************************/
  makeArray: function( o ) {
    return $.isArray( o )? o: [ o ];
  },

  /****************************************************************************/
  isNumeric: function( t ) {
    return t - 0 == t && t.toString().length > 0;
  },

  /****************************************************************************/
  trim: function( str ) {
    str = str.replace( /^\s+/, '' );
    for( var i = str.length - 1; i >= 0; i-- ) {
      if( /\S/.test( str.charAt( i ) ) ) {
        str = str.substring( 0, i + 1 );
        break;
      }
    }
    return str;
  },

  /****************************************************************************/
  typeOf: function( v ) {
    var s = typeof v;
    if (s === 'object') {
      if (value) {
        if (typeof value.length === 'number' &&
          !(value.propertyIsEnumerable('length')) &&
          typeof value.splice === 'function') {
          s = 'array';
        }
      } else {
        s = 'null';
      }
    }
    return s;
  },

  /****************************************************************************/
  filtrate: function( str ) {
    return _c.trim( _c.replaceAccents( str ) )
           .toLowerCase()
           .replace( /\s/ig, "-" )
           .replace( /[^\-_\.a-z0-9]+/ig, "" );
  },

  /****************************************************************************/
  callGet: function( go ) {
    var fi = go.u + go.fo + "/" + go.n,
        options = { success: go.c, type: go.m },
        k, list;
    if( go.ps ) {
      options["data"] = go.ps;
    }
    if( go.fo == "template" ) {
      options["url"] = fi + ".html";
      options["contentType"] = "text/html";
      options["dataType"] = "html";
    } else if( go.fo == "transformation" ) {
      options["url"] = fi + ".xsl";
      options["contentType"] = "text/xsl";
      options["dataType"] = "text";
    } else if( go.fo == "procedure" ) {
      options["url"] = go.u + go.fo + "/controller.php";
      options["data"] = options["data"] || {};
      options["data"]["action"] = go.n;
      options["contentType"] = "application/json";
      options["dataType"] = "json";
      if( go.t ) {
        if( go.t == "text" ) {
          options["contentType"] = "text/plain";
          options["dataType"] = "text";
        } else if( go.t == "html" ) {
          options["contentType"] = "text/html";
          options["dataType"] = "html";
        }
      }
    } else if( go.fo == "text" ) {
      fi = "procedure/" + go.n;
      options["url"] = fi + ".php";
      options["contentType"] = "text/plain";
      options["dataType"] = "text";
    } else if( go.fo == "script" ) {
      fi = go.u + "interaction/" + go.n;
      options["url"] = fi + ".js";
      options["contentType"] = "application/javascript";
      options["dataType"] = "script";
    } else {
      options["url"] = fi + ".js";
      options["contentType"] = "application/json";
      if( go.fo == "data" ) {
        options["dataType"] = "json";
      } else {
        options["dataType"] = "text";
        options["success"] = function( result ) {
          var json = eval( "(" + result + ")" );
          go.c( json );
        }
      }
    }

    $.ajax( options );

    //return false;
  },
  
  /****************************************************************************/
  showAjaxError: function( XMLHttpRequest, textStatus, errorThrown ) {
    console.log( errorThrown );
    alert( "Erreur" );
  },

  /****************************************************************************/
  callAjax: function( getList, c ) {
    var gets = _c.makeArray( getList ),
        last = gets.length - 1,
        i = 0;
    if( !gets.length ) {
      c( false );
      return false;
    }
    return this.eachItem( gets, function( getItem ) {
      var ps = ( getItem.params ) || null,
          u = getItem.url || "",
          f = getItem.folder,
          n = getItem.name,
          t = getItem.dataType,
          m = getItem.method || "GET",
          lastGet;

      if( ( f == "template" || f == "data" || f == "interaction" || f == "transformation" ) &&
            _c.ajaxList[f] &&
            _c.ajaxList[f][n] ) {
        if( ( ++i ) > last ) {
          lastGet = gets[last];
          c( _c.ajaxList[lastGet.folder][lastGet.name] );
        }
        return true;
      } else {
        _c.callGet( {
          fo: f, n: n, ps: ps, u: u, t: t, m: m,
          c: function( aItem ) {
/*console.log( aItem );*/
            if( !_c.ajaxList[f] ) {
              _c.ajaxList[f] = {};
            }
            _c.ajaxList[f][n] = aItem;
            if( ( ++i ) > last ) {
              lastGet = gets[last];
if( !c ) {
  console.log( aItem );
}
              if( c ) {
                c( _c.ajaxList[lastGet.folder][lastGet.name] );
              }
            }
          }
        } );
      }
    } );
  },

  /****************************************************************************/
  setCookie: function( n, v, d ) {
    var exdate = new Date();
    exdate.setDate( exdate.getDate() + d ) ;
    document.cookie = n + "=" + escape( v ) + ( ( d == null )? "": ";expires=" + exdate.toUTCString() );
  },

  /****************************************************************************/
  getCookie: function( n ) {
    var start, end;
    if( document.cookie.length > 0 ) {
      start = document.cookie.indexOf( n + "=" );
      if( start != -1 ) {
        start += n.length + 1;
        end = document.cookie.indexOf( ";", start );
        if( end == -1 ) {
          end = document.cookie.length;
        }
        return unescape( document.cookie.substring( start, end ) );
      }
    }
    return "";
  },

  /****************************************************************************/
  setLocalStorage: function( n, v ) {
    var ov = this.getLocalStorage( n );
    this.overwriteLocalStorage( n, ( ov? this.mergeList( [ov, v] ): v ) );
  },

  /****************************************************************************/
  overwriteLocalStorage: function( n, v ) {
    window.localStorage[n] = JSON.stringify( v );
  },

  /****************************************************************************/
  getLocalStorage: function( n ) {
    var v = window.localStorage[n];
    return v? JSON.parse( v ): false;
  },

  /****************************************************************************/
  removeLocalStorage: function( n ) {
    window.localStorage.removeItem( n );
  },

  /****************************************************************************/
  clearLocalStorage: function() {
    window.localStorage.clear();
  },

  /****************************************************************************/
  setAccountStorage: function( n, v ) {
    _c.callAjax( [ { folder: "procedure", name: "setAccountStorage", params: { "name": n, "value": v } } ], false );
  }
};

//ajax
$.ajaxSetup( {
  type: "GET",
  scriptCharset: "UTF-8",
  async: true,
  cache: true,
  global: true,
  error: _c.showAjaxError,
  complete: function( a ) {
    /*console.log( a );*/
  }
} );

