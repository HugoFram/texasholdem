<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * texasholdem implementation : © <Hugo Frammery> <hugo@frammery.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * texasholdem.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in texasholdem_texasholdem.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_texasholdem_texasholdem extends game_view
  {
    function getGameName() {
        return "texasholdem";
    }    
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        /*********** Place your code below:  ************/


        /*
        
        // Examples: set the value of some element defined in your tpl file like this: {MY_VARIABLE_ELEMENT}

        // Display a specific number / string
        $this->tpl['MY_VARIABLE_ELEMENT'] = $number_to_display;

        // Display a string to be translated in all languages: 
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::_("A string to be translated");

        // Display some HTML content of your own:
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::raw( $some_html_code );
        
        */
        
        /*
        
        // Example: display a specific HTML block for each player in this game.
        // (note: the block is defined in your .tpl file like this:
        //      <!-- BEGIN myblock --> 
        //          ... my HTML code ...
        //      <!-- END myblock --> 
        

        $this->page->begin_block( "texasholdem_texasholdem", "myblock" );
        foreach( $players as $player )
        {
            $this->page->insert_block( "myblock", array( 
                                                    "PLAYER_NAME" => $player['player_name'],
                                                    "SOME_VARIABLE" => $some_value
                                                    ...
                                                     ) );
        }
        
        */

        $this->tpl['CHANGE_TOKEN_VALUE_WHITE'] = " = " . $this->game->token_values["white"];
        $this->tpl['CHANGE_TOKEN_VALUE_BLUE'] = " = " . $this->game->token_values["blue"];
        $this->tpl['CHANGE_TOKEN_VALUE_RED'] = " = " . $this->game->token_values["red"];
        $this->tpl['CHANGE_TOKEN_VALUE_GREEN'] = " = " . $this->game->token_values["green"];
        $this->tpl['CHANGE_TOKEN_VALUE_BLACK'] = " = " . $this->game->token_values["black"];

        $players_dir = $this->game->getPlayerDirections();

        $this->page->begin_block("texasholdem_texasholdem", "player");
        foreach($players as $player) {
            $this->page->insert_block("player", array(
                "DIR" => $players_dir[$player['player_id']],
                "PLAYER_COLOR" => $player['player_color'],
                "PLAYER_NAME" => $player['player_name'],
                "PLAYER_ID" => $player['player_id'],
                "PLAYER_TOKEN_WHITE" => 15,
                "PLAYER_TOKEN_BLUE" => 4,
                "PLAYER_TOKEN_RED" => 3,
                "PLAYER_TOKEN_GREEN" => 22,
                "PLAYER_TOKEN_BLACK" => 31,
                "PLAYER_STOCK_TOTAL" => 100,
                "PLAYER_BET_TOKEN_WHITE" => 1,
                "PLAYER_BET_TOKEN_BLUE" => 7,
                "PLAYER_BET_TOKEN_RED" => 0,
                "PLAYER_BET_TOKEN_GREEN" => 1,
                "PLAYER_BET_TOKEN_BLACK" => 3,
                "PLAYER_BET_TOTAL" => 0
            ));
        }

        $this->tpl['AUTOBLINDS_DESCRIPTION'] = self::_("Place blinds automatically");
        $this->tpl['BETMODE_DESCRIPTION'] = self::_("Choose raise amount by clicking on chips");
        $this->tpl['DOSHOWHAND_DESCRIPTION'] = self::_("Ask to reveal hand if all other players are folded");
        $this->tpl['BLIND_LEVEL_TEXT'] = self::_("Current blind level: 1/2");
        $this->tpl['HAND_NUMBER_TEXT'] = self::_("Number of hands played: 0");

        /*********** Do not change anything below this line  ************/
  	}
  }
  

