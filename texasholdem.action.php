<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * texasholdem implementation : © <Your name here> <Your email address here>
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

    public function placeBet() {
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

        $this->game->placeBet($tokens);

        self::ajaxResponse();
    }

  }
  

