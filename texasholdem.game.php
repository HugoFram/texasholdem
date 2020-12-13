<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * texasholdem implementation : © <Your name here> <Your email address here>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * texasholdem.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class texasholdem extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        
        self::initGameStateLabels( array( 
            //    "my_first_global_variable" => 10,
            //    "my_second_global_variable" => 11,
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
            "roundNumber" => 10,
            "roundStage" => 11, // 1 = pre-flop, 2 = flop, 3 = turn, 4 = river
        ) );

        $this->cards = self::getNew("module.common.deck");
        $this->cards->init("card");
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "texasholdem";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
 
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_score,
            player_stock_token_white, player_stock_token_blue, player_stock_token_red, player_stock_token_green,
            player_stock_token_black, player_bet_token_white, player_bet_token_blue,
            player_bet_token_red, player_bet_token_green, player_bet_token_black) VALUES ";
        $values = array();
        $initial_score = 100;
        $initial_white_tokens = 10;
        $initial_blue_tokens = 5;
        $initial_red_tokens = 4;
        $initial_green_tokens = 2;
        $initial_black_tokens = 2;
        $initial_bet_tokens = 0;
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."',".
                $initial_score.",".$initial_white_tokens.",".$initial_blue_tokens.",".$initial_red_tokens.",".$initial_green_tokens.",".$initial_black_tokens.",".
                $initial_bet_tokens.",".$initial_bet_tokens.",".$initial_bet_tokens.",".$initial_bet_tokens.",".$initial_bet_tokens.")";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        // Init global values with their initial values
        //self::setGameStateInitialValue( 'my_first_global_variable', 0 );
        
        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        // TODO: setup the initial game situation here

        // Initialize tokens bet in previous round stages to 0
        $sql = "INSERT INTO token (token_color, token_number) VALUES ('white', 0), ('blue', 0), ('red', 0), ('green', 0), ('black', 0)";
        self::DbQuery( $sql );

        // Create cards
        $cards = array();
        foreach ($this->suits as $suit_id => $suit) {
            // spade, heart, clubs, diamonds
            for ($value = 2; $value <= 14; $value++) {
                $cards[] = array('type' => $suit_id, 'type_arg' => $value, 'nbr' => 1);
            }
        }
        $this->cards->createCards($cards, 'deck');

        // Shuffle deck
        $this->cards->shuffle('deck');

        // Deal two cards to each player
        $players = self::loadPlayersBasicInfos();
        foreach ($players as $player_id => $player) {
            $cards = $this->cards->pickCards(2, 'deck', $player_id);
        }       

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score, is_fold, player_stock_token_white stock_white, 
            player_stock_token_blue stock_blue, player_stock_token_red stock_red, player_stock_token_green stock_green,
            player_stock_token_black stock_black, player_bet_token_white bet_white, player_bet_token_blue bet_blue,
            player_bet_token_red bet_red, player_bet_token_green bet_green, player_bet_token_black bet_black FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
  
        // TODO: Gather all information about current game situation (visible by player $current_player_id).
  
        // Cards in player hand
        $result['hands'] = $this->cards->getCardsInLocation('hand');

        // Cards played on the table
        $result['cardsflop'] = $this->cards->getCardsInLocation('flop');
        $result['cardturn'] = $this->cards->getCardsInLocation('turn');
        $result['cardriver'] = $this->cards->getCardsInLocation('river');

        // Tokens bet at previous betting stages
        $sql = "SELECT token_color, token_number FROM token";
        $result['tokensontable']= self::getCollectionFromDb( $sql );

        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */

    function getPlayerDirections() {
        $result = array();
    
        $players = self::loadPlayersBasicInfos();
        $nextPlayer = self::createNextPlayerTable( array_keys( $players ) );

        $current_player = self::getCurrentPlayerId();

        $numPlayers = count($players);


        // Place players around the table depending on the number of players
        switch($numPlayers) {
          case 2:
            $directions = array("S", "N");
            break;
          case 3:
            $directions = array("S", "W", "N");
            break;
          case 4:
            $directions = array("S", "W", "N", "E");
            break;
          case 5:
            $directions = array("SW", "W", "N", "E", "SE");
            break;
          case 6:
            $directions = array("SW", "W", "NW", "NE", "E", "SE");
            break;
          case 7:
            $directions = array("SW", "W", "NW", "N", "NE", "E", "SE");
            break;
          case 8:
            $directions = array("S", "SW", "W", "NW", "N", "NE", "E", "SE");
            break;
        }
        
        if(!isset($nextPlayer[$current_player]))
        {
            // Spectator mode: take any player for south
            $player_id = $nextPlayer[0];
            $result[$player_id] = array_shift($directions);
        }
        else
        {
            // Normal mode: current player is on south
            $player_id = $current_player;
            $result[$player_id] = array_shift($directions);
        }
        
        while(count($directions) > 0)
        {
            $player_id = $nextPlayer[$player_id];
            $result[$player_id] = array_shift($directions);
        }

        return $result;
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in texasholdem.action.php)
    */

    /*
    
    Example:

    function playCard( $card_id )
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'playCard' ); 
        
        $player_id = self::getActivePlayerId();
        
        // Add your game logic to play a card there 
        ...
        
        // Notify all players about the card played
        self::notifyAllPlayers( "cardPlayed", clienttranslate( '${player_name} plays ${card_name}' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id
        ) );
          
    }
    
    */

    function placeBet($tokens) {
        self::checkAction("placeBet");
        $player_id = self::getActivePlayerId();

        // Query current player's tokens stock
        $sql = "SELECT player_stock_token_white, player_stock_token_blue, player_stock_token_red, 
            player_stock_token_green, player_stock_token_black FROM player WHERE player_id = '" . $player_id . "'";
        $current_stock = self::getObjectFromDB($sql);
        $diff_stock = array();

        // Update player table with new number of tokens in stock and betting area
        $keys = array(
            "player_stock_token_white", "player_bet_token_white",
            "player_stock_token_blue", "player_bet_token_blue",
            "player_stock_token_red", "player_bet_token_red",
            "player_stock_token_green", "player_bet_token_green",
            "player_stock_token_black", "player_bet_token_black"
        );

        $sql = "UPDATE player SET ";
        foreach($tokens as $token_id => $token_number) {
            $sql .= $keys[$token_id] . " = " . $token_number;
            // Don't add a comma for the last item
            if ($token_id < (count($tokens) - 1)) {
                $sql .= ", ";
            }

            // Compute stock difference
            if (strpos($keys[$token_id], "stock")) {
                $color = str_replace("player_stock_token_", "", $keys[$token_id]);
                $diff_stock[$color] = $token_number - $current_stock[$keys[$token_id]];
            }
        }
        $sql .= " WHERE player_id = '" . $player_id. "'";
        self::DbQuery($sql);

        // Calculate additional bet
        $additional_bet = 0;
        foreach($diff_stock as $color => $token_diff) {
            $additional_bet -= $this->token_values[$color] * $token_diff;
        }

        // Notify other player of the bet placed
        self::notifyAllPlayers("betPlaced", clienttranslate( '${player_name} raises by ${additional_bet}' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'additional_bet' => $additional_bet,
            'diff_stock' => $diff_stock
        ) );

        $this->gamestate->nextState('placeBet');
    }

    function fold($player_id) {
        self::checkAction("fold");
        $player_id = self::getActivePlayerId();

        $sql = "UPDATE player SET is_fold = true WHERE player_id = '" . $player_id . "'";
        self::DbQuery($sql);

        $this->cards->moveAllCardsInLocation("hand", "discarded", $player_id, $player_id);

        self::notifyAllPlayers("fold", clienttranslate('${player_name} folds'), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName()
        ));

        $this->gamestate->nextState('fold');
    }

    function makeChange($tokens) {
        self::checkAction("makeChange");
        $player_id = self::getActivePlayerId();
    }

    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    /*
    
    Example for game state "MyGameState":
    
    function argMyGameState()
    {
        // Get some values from the current game situation in database...
    
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }    
    */

    function argPlaceBet() {
        return array(
            "white" =>  0,
            "blue" => 0,
            "red" => 0,
            "green" => 0,
            "black" => 0
        );
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    
    /*
    
    Example for game state "MyGameState":

    function stMyGameState()
    {
        // Do some stuff ...
        
        // (very often) go to another gamestate
        $this->gamestate->nextState( 'some_gamestate_transition' );
    }    
    */

    function stNewHand() {
        $this->gamestate->nextState();
    }

    function stNewBet() {
        $this->gamestate->nextState();
    }

    function stNextPlayer() {
        $player_id = self::activeNextPlayer();

        // Check which players are folded
        $sql = "SELECT player_id, is_fold FROM player";
        $folded_players = self::getCollectionFromDb($sql, true);
        //throw new BgaUserException(implode(" - ", array_keys($folded_players)) . implode(" - ", $folded_players));
        $num_folded_players = 0;

        // Skip player if he has folded 
        while ($folded_players[$player_id] && $num_folded_players < (count($folded_players) - 1)) {
            $num_folded_players++;
            $player_id = self::activeNextPlayer();
        }

        // Check if all players except one have folded
        if ($num_folded_players == (count($folded_players) - 1)) {
            $this->gamestate->nextState("endBetRound");
        } else {
            self::giveExtraTime($player_id);
            $this->gamestate->nextState("nextPlayer");
        }
    }

    function stEndBet() {
        $this->gamestate->nextState("nextBetRound");
    }

    function stEndHand() {
        $this->gamestate->nextState("nextHand");
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );
            
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
