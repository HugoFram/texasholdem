<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * texasholdem implementation : © <Hugo Frammery> <hugo@frammery.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * texasholdem.action.php
 *
 * texasholdem main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/texasholdem/texasholdem/myAction.html", ...)
 *
 */
  
  
  class action_texasholdem extends APP_GameAction
  { 
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "texasholdem_texasholdem";
            self::trace( "Complete reinitialization of board game" );
      }
  	} 
  	
  	// TODO: defines your action entry points there


    /*
    
    Example:
  	
    public function myAction()
    {
        self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        $arg1 = self::getArg( "myArgument1", AT_posint, true );
        $arg2 = self::getArg( "myArgument2", AT_posint, true );

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->myAction( $arg1, $arg2 );

        self::ajaxResponse( );
    }
    
    */

    public function smallBlind() {
      self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        // Expects a number list with the following format
        // "<num tokens white in stock>;<num tokens white in betting area>;<num tokens blue in stock>;<num tokens blue in betting area>;..."
        $tokens_raw = self::getArg("tokens", AT_numberlist, true);

        // Removing last ';' if exists
        if (substr($tokens_raw, -1) == ';') {
          $tokens_raw = substr($tokens_raw, 0, -1 );
        }
        if ($tokens_raw == '') {
          $tokens = array();
        } else {
          $tokens = explode( ';', $tokens_raw);
        }

        $this->game->placeSmallBlind($tokens, false);

        self::ajaxResponse();
    }

    public function bigBlind() {
      self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        // Expects a number list with the following format
        // "<num tokens white in stock>;<num tokens white in betting area>;<num tokens blue in stock>;<num tokens blue in betting area>;..."
        $tokens_raw = self::getArg("tokens", AT_numberlist, true);

        // Removing last ';' if exists
        if (substr($tokens_raw, -1) == ';') {
          $tokens_raw = substr($tokens_raw, 0, -1 );
        }
        if ($tokens_raw == '') {
          $tokens = array();
        } else {
          $tokens = explode( ';', $tokens_raw);
        }

        $this->game->placeBigBlind($tokens, false);

        self::ajaxResponse();
    }

    public function check() {
      self::setAjaxMode();
      
      // Retrieve arguments
      // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
      // Expects a number list with the following format
      // "<num tokens white in stock>;<num tokens white in betting area>;<num tokens blue in stock>;<num tokens blue in betting area>;..."
      $tokens_raw = self::getArg("tokens", AT_numberlist, true);

      // Removing last ';' if exists
      if (substr($tokens_raw, -1) == ';') {
        $tokens_raw = substr($tokens_raw, 0, -1 );
      }
      if ($tokens_raw == '') {
        $tokens = array();
      } else {
        $tokens = explode( ';', $tokens_raw);
      }
      
      $this->game->check($tokens);

      self::ajaxResponse();
    }

    public function call() {
      self::setAjaxMode();
      
      // Retrieve arguments
      // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
      // Expects a number list with the following format
      // "<num tokens white in stock>;<num tokens white in betting area>;<num tokens blue in stock>;<num tokens blue in betting area>;..."
      $tokens_raw = self::getArg("tokens", AT_numberlist, true);

      // Removing last ';' if exists
      if (substr($tokens_raw, -1) == ';') {
        $tokens_raw = substr($tokens_raw, 0, -1 );
      }
      if ($tokens_raw == '') {
        $tokens = array();
      } else {
        $tokens = explode( ';', $tokens_raw);
      }

      $this->game->call($tokens);

      self::ajaxResponse();
    }

    public function raise() {
      self::setAjaxMode();     

      // Retrieve arguments
      // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
      // Expects a number list with the following format
      // "<num tokens white in stock>;<num tokens white in betting area>;<num tokens blue in stock>;<num tokens blue in betting area>;..."
      $tokens_raw = self::getArg("tokens", AT_numberlist, true);

      // Removing last ';' if exists
      if (substr($tokens_raw, -1) == ';') {
        $tokens_raw = substr($tokens_raw, 0, -1 );
      }
      if ($tokens_raw == '') {
        $tokens = array();
      } else {
        $tokens = explode( ';', $tokens_raw);
      }

      $this->game->raise($tokens);

      self::ajaxResponse();
    }

    public function raiseBy() {
      self::setAjaxMode();     

      // Retrieve arguments
      // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
      // Expects a number list with the following format
      // "<num tokens white in stock>;<num tokens white in betting area>;<num tokens blue in stock>;<num tokens blue in betting area>;..."
      $tokens_raw = self::getArg("tokens", AT_numberlist, true);

      // Removing last ';' if exists
      if (substr($tokens_raw, -1) == ';') {
        $tokens_raw = substr($tokens_raw, 0, -1 );
      }
      if ($tokens_raw == '') {
        $tokens = array();
      } else {
        $tokens = explode( ';', $tokens_raw);
      }

      $raise_value = self::getArg("raiseValue", AT_int, true);

      $this->game->raiseBy($tokens, $raise_value);

      self::ajaxResponse();
    }

    public function fold() {
      self::setAjaxMode();

      // Retrieve arguments
      // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
      // Expects a number list with the following format
      // "<num tokens white in stock>;<num tokens white in betting area>;<num tokens blue in stock>;<num tokens blue in betting area>;..."
      $tokens_raw = self::getArg("tokens", AT_numberlist, true);
      $player_id = self::getArg( "player_id", AT_posint, true );

      // Removing last ';' if exists
      if (substr($tokens_raw, -1) == ';') {
        $tokens_raw = substr($tokens_raw, 0, -1 );
      }
      if ($tokens_raw == '') {
        $tokens = array();
      } else {
        $tokens = explode( ';', $tokens_raw);
      }

      $this->game->fold($player_id, $tokens);
      self::ajaxResponse();
    }

    public function showHand() {
      self::setAjaxMode();

      // Retrieve arguments
      // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
      $show_hand = self::getArg("show_hand", AT_posint, true);

      $this->game->showHand($show_hand);
      self::ajaxResponse();
    }

    public function allIn() {
      self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        // Expects a number list with the following format
        // "<num tokens white in stock>;<num tokens white in betting area>;<num tokens blue in stock>;<num tokens blue in betting area>;..."
        $tokens_raw = self::getArg("tokens", AT_numberlist, true);

        // Removing last ';' if exists
        if (substr($tokens_raw, -1) == ';') {
          $tokens_raw = substr($tokens_raw, 0, -1 );
        }
        if ($tokens_raw == '') {
          $tokens = array();
        } else {
          $tokens = explode( ';', $tokens_raw);
        }

        $this->game->allIn($tokens);

        self::ajaxResponse();
    }

    public function makeChange() {
      self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        // Expects a number list with the following format
        // "<num tokens white in stock>;<num tokens white in betting area>;<num tokens blue in stock>;<num tokens blue in betting area>;..."
        $tokens_raw = self::getArg("tokens", AT_numberlist, true);

        // Removing last ';' if exists
        if (substr($tokens_raw, -1) == ';') {
          $tokens_raw = substr($tokens_raw, 0, -1 );
        }
        if ($tokens_raw == '') {
          $tokens = array();
        } else if ($tokens_raw == '0;0;0;0;0;0;0;0;0;0') {
          throw new BgaUserException(_("You must select one of the tokens proposition to get in exchange of what you give."));
        } else {
          $tokens = explode( ';', $tokens_raw);
        }

        $this->game->makeChange($tokens);

        self::ajaxResponse();
    }

    public function autoblinds() {
      self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        // playerId: integer for the id of the player who clicked the slider
        // checked: integer, 0 = disable autoblinds, 1 = enable autoblinds
        $player_id = self::getArg("playerId", AT_int, true);
        $is_checked = self::getArg("isAutoblinds", AT_int, true);

        $this->game->changeAutoblinds($player_id, $is_checked);

        self::ajaxResponse();
    }

    public function betmode() {
      self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        // playerId: integer for the id of the player who clicked the slider
        // checked: integer, 0 = disable autoblinds, 1 = enable autoblinds
        $player_id = self::getArg("playerId", AT_int, true);
        $is_checked = self::getArg("isBetManual", AT_int, true);

        // Expects a number list with the following format
        // "<num tokens white in stock>;<num tokens white in betting area>;<num tokens blue in stock>;<num tokens blue in betting area>;..."
        $tokens_raw = self::getArg("tokens", AT_numberlist, true);

        // Removing last ';' if exists
        if (substr($tokens_raw, -1) == ';') {
          $tokens_raw = substr($tokens_raw, 0, -1 );
        }
        if ($tokens_raw == '') {
          $tokens = array();
        } else if ($tokens_raw == '0;0;0;0;0;0;0;0;0;0') {
          throw new BgaUserException(_("You must select one of the tokens proposition to get in exchange of what you give."));
        } else {
          $tokens = explode( ';', $tokens_raw);
        }

        $this->game->changeBetmode($player_id, $is_checked, $tokens);

        self::ajaxResponse();
    }

    public function doshowhand() {
      self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        // playerId: integer for the id of the player who clicked the slider
        // checked: integer, 0 = disable autoblinds, 1 = enable autoblinds
        $player_id = self::getArg("playerId", AT_int, true);
        $is_checked = self::getArg("doShowHand", AT_int, true);

        $this->game->changeDoShowHand($player_id, $is_checked);

        self::ajaxResponse();
    }

  }
  

