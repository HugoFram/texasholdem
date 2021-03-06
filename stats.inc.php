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
 * stats.inc.php
 *
 * texasholdem game statistics description
 *
 */

/*
    In this file, you are describing game statistics, that will be displayed at the end of the
    game.
    
    !! After modifying this file, you must use "Reload  statistics configuration" in BGA Studio backoffice
    ("Control Panel" / "Manage Game" / "Your Game")
    
    There are 2 types of statistics:
    _ table statistics, that are not associated to a specific player (ie: 1 value for each game).
    _ player statistics, that are associated to each players (ie: 1 value for each player in the game).

    Statistics types can be "int" for integer, "float" for floating point values, and "bool" for boolean
    
    Once you defined your statistics there, you can start using "initStat", "setStat" and "incStat" method
    in your game logic, using statistics names defined below.
    
    !! It is not a good idea to modify this file when a game is running !!

    If your game is already public on BGA, please read the following before any change:
    http://en.doc.boardgamearena.com/Post-release_phase#Changes_that_breaks_the_games_in_progress
    
    Notes:
    * Statistic index is the reference used in setStat/incStat/initStat PHP method
    * Statistic index must contains alphanumerical characters and no space. Example: 'turn_played'
    * Statistics IDs must be >=10
    * Two table statistics can't share the same ID, two player statistics can't share the same ID
    * A table statistic can have the same ID than a player statistics
    * Statistics ID is the reference used by BGA website. If you change the ID, you lost all historical statistic data. Do NOT re-use an ID of a deleted statistic
    * Statistic name is the English description of the statistic as shown to players
    
*/

$stats_type = array(

    // Statistics global to table
    "table" => array(

        "turns_number" => array("id"=> 10,
                    "name" => totranslate("Number of turns"),
                    "type" => "int" ),

/*
        Examples:


        "table_teststat1" => array(   "id"=> 10,
                                "name" => totranslate("table test stat 1"), 
                                "type" => "int" ),
                                
        "table_teststat2" => array(   "id"=> 11,
                                "name" => totranslate("table test stat 2"), 
                                "type" => "float" )
*/  
    ),
    
    // Statistics existing for each player
    "player" => array(

        "turns_number" => array(
            "id"=> 10,
            "name" => totranslate("Number of turns"),
            "type" => "int"
        ),
        "hands_won" => array(
            "id"=> 11,
            "name" => totranslate("Number of hands won"),
            "type" => "int"
        ),
        "times_folded" => array(
            "id"=> 12,
            "name" => totranslate("Number of times folded"),
            "type" => "int"
        ),
        "checks" => array(
            "id"=> 13,
            "name" => totranslate("Number of checks"),
            "type" => "int"
        ),
        "times_called" => array(
            "id"=> 14,
            "name" => totranslate("Number of times called"),
            "type" => "int"
        ),
        "times_raised" => array(
            "id"=> 15,
            "name" => totranslate("Number of times raised"),
            "type" => "int"
        ),
        "times_all_in" => array(
            "id"=> 16,
            "name" => totranslate("Number of times all in"),
            "type" => "int"
        ),
        "high_cards" => array(
            "id"=> 17,
            "name" => totranslate("Number of high cards"),
            "type" => "int"
        ),
        "pairs" => array(
            "id"=> 18,
            "name" => totranslate("Number of pairs"),
            "type" => "int"
        ),
        "two_pairs" => array(
            "id"=> 19,
            "name" => totranslate("Number of two pairs"),
            "type" => "int"
        ),
        "three_of_a_kinds" => array(
            "id"=> 20,
            "name" => totranslate("Number of three of a kinds"),
            "type" => "int"
        ),
        "straights" => array(
            "id"=> 21,
            "name" => totranslate("Number of straights"),
            "type" => "int"
        ),
        "flushes" => array(
            "id"=> 22,
            "name" => totranslate("Number of flushes"),
            "type" => "int"
        ),
        "full_houses" => array(
            "id"=> 23,
            "name" => totranslate("Number of full houses"),
            "type" => "int"
        ),
        "four_of_a_kinds" => array(
            "id"=> 24,
            "name" => totranslate("Number of four of a kinds"),
            "type" => "int"
        ),
        "straight_flushes" => array(
            "id"=> 25,
            "name" => totranslate("Number of straight flushes"),
            "type" => "int"
        )
    
/*
        Examples:    
        
        
        "player_teststat1" => array(   "id"=> 10,
                                "name" => totranslate("player test stat 1"), 
                                "type" => "int" ),
                                
        "player_teststat2" => array(   "id"=> 11,
                                "name" => totranslate("player test stat 2"), 
                                "type" => "float" )

*/    
    )

);
