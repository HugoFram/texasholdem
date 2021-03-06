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
 * states.inc.php
 *
 * texasholdem game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

 
$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => 20 )
    ),

    // New hand
    20 => array(
        "name" => "newHand",
        "description" => "",
        "type" => "game",
        "action" => "stNewHand",
        "updateGameProgression" => true,   
        "transitions" => array("manualSmallBlind" => 21, "placeSmallBlind" => 22)
    ),

    // Small blind player's turn
    21 => array(
        "name" => "smallBlind",
        "description" => clienttranslate('${actplayer} must bet ${smallblind} for the small blind.'),
        "descriptionmyturn" => clienttranslate('${you} must bet ${smallblind} for the small blind.'),
        "type" => "activeplayer",
        "args" => "argSmallBlind",
        "possibleactions" => array("placeSmallBlind", "makeChange"),
        "transitions" => array("placeSmallBlind" => 22, "zombiePass" => 98)
    ),

    // Transition to the big blind player
    22 => array(
        "name" => "toBigBlind",
        "description" => "",
        "type" => "game",
        "action" => "stToBigBlind",
        "transitions" => array("manualBigBlind" => 23, "placeBigBlind" => 24)
    ), 

    // Big blind player's turn
    23 => array(
        "name" => "bigBlind",
        "description" => clienttranslate('${actplayer} must bet ${bigblind} for the big blind.'),
        "descriptionmyturn" => clienttranslate('${you} must bet ${bigblind} for the big blind.'),
        "type" => "activeplayer",
        "args" => "argBigBlind",
        "possibleactions" => array("placeBigBlind", "makeChange"),
        "transitions" => array("placeBigBlind" => 24, "zombiePass" => 98)
    ),

    // Transition to start of the betting round
    24 => array(
        "name" => "toBetRound",
        "description" => "",
        "type" => "game",
        "action" => "stToBetRound",
        "transitions" => array("startRound" => 30)
    ), 

    // New betting round
    30 => array(
        "name" => "newBetRound",
        "description" => "",
        "type" => "game",
        "action" => "stNewBet",
        "transitions" => array("startRound" => 40, "placeBet" => 41, "allAllIn" => 31)
    ),  

    // Player's turn
    40 => array(
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} must call, raise, or fold'),
        "descriptionmyturn" => clienttranslate('${you} must call, raise, or fold'),
        "type" => "activeplayer",
        "args" => "argPlayerTurn",
        "possibleactions" => array( "placeBet", "fold", "makeChange" ),
        "transitions" => array("placeBet" => 41, "fold" => 41, "zombiePass" => 98)
    ),

    // Transition to next player or end of betting round
    41 => array(
        "name" => "nextPlayer",
        "description" => "",
        "type" => "game",
        "action" => "stNextPlayer",
        "transitions" => array("nextPlayer" => 40, "placeBet" => 41, "endBetRound" => 31)
    ),

    31 => array(
        "name" => "endBetRound",
        "description" => "",
        "type" => "game",
        "action" => "stEndBet",
        "transitions" => array("nextBetRound" => 30, "chooseShowHand" => 32, "endHand" => 25)
    ),

    32 => array(
        "name" => "chooseShowHand",
        "description" => clienttranslate('${actplayer} decides if she/he wants to reveal her/his hand'),
        "descriptionmyturn" => clienttranslate('Do you want to reveal your hand ${you}?'),
        "type" => "activeplayer",
        "possibleactions" => array("endHand"),
        "transitions" => array("endHand" => 25, "zombiePass" => 98)
    ), 

    25 => array(
        "name" => "endHand",
        "description" => "",
        "type" => "game",
        "action" => "stEndHand",
        "transitions" => array( "nextHand" => 20, "endGame" => 99 )
    ),

    98 => array(
        "name" => "zombiePass",
        "description" => "",
        "type" => "game",
        "action" => "stZombiePass",
        "transitions" => array("nextPlayer" => 40)
    ),
    
/*
    Examples:
    
    2 => array(
        "name" => "nextPlayer",
        "description" => '',
        "type" => "game",
        "action" => "stNextPlayer",
        "updateGameProgression" => true,   
        "transitions" => array( "endGame" => 99, "nextPlayer" => 10 )
    ),
    
    10 => array(
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} must play a card or pass'),
        "descriptionmyturn" => clienttranslate('${you} must play a card or pass'),
        "type" => "activeplayer",
        "possibleactions" => array( "playCard", "pass" ),
        "transitions" => array( "playCard" => 2, "pass" => 2 )
    ), 

*/    
   
    // Final state.
    // Please do not modify (and do not overload action/args methods).
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);



