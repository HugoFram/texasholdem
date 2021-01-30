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
            "roundStage" => 11, // 0 = blinds, 1 = pre-flop, 2 = flop, 3 = turn, 4 = river
            "numFoldedPlayers" => 12,
            "numAllInPlayers" => 13,
            "numEliminatedPlayers" => 14,
            "currentBetLevel" => 15,
            "smallBlindPlayer" => 16,
            "smallBlindValue" => 17,
            "numBettingPlayers" => 18, // Number of players who have bet, checked or folded during this betting round
            "minimumRaise" => 19,
            "dealerId" => 20,
            "areHandsRevealed" => 21,

            "gameEndVariant" => 100,
            "handsNumberLimit" => 101,
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
            player_bet_token_red, player_bet_token_green, player_bet_token_black, is_fold, is_all_in, wants_autoblinds, wants_manualbet) VALUES ";
        $values = array();
        $initial_score = 100;
        $initial_white_tokens = 10;
        $initial_blue_tokens = 5;
        $initial_red_tokens = 4;
        $initial_green_tokens = 2;
        $initial_black_tokens = 2;
        $initial_bet_tokens = 0;
        $initial_is_fold = 0;
        $initial_is_all_in = 0;
        $initial_wants_autoblinds = 1;
        $initial_wants_manualbet = 0;
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."',".
                $initial_score.",".$initial_white_tokens.",".$initial_blue_tokens.",".$initial_red_tokens.",".$initial_green_tokens.",".$initial_black_tokens.",".
                $initial_bet_tokens.",".$initial_bet_tokens.",".$initial_bet_tokens.",".$initial_bet_tokens.",".$initial_bet_tokens.",".$initial_is_fold.",".$initial_is_all_in.
                ",".$initial_wants_autoblinds.",".$initial_wants_manualbet.")";
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

        self::initStat('player', 'turns_number', 0);
        self::initStat('player', 'hands_won', 0);
        self::initStat('player', 'times_folded', 0);
        self::initStat('player', 'checks', 0);
        self::initStat('player', 'times_called', 0);
        self::initStat('player', 'times_raised', 0);
        self::initStat('player', 'times_all_in', 0);
        self::initStat('player', 'high_cards', 0);
        self::initStat('player', 'pairs', 0);
        self::initStat('player', 'two_pairs', 0);
        self::initStat('player', 'three_of_a_kinds', 0);
        self::initStat('player', 'straights', 0);
        self::initStat('player', 'flushes', 0);
        self::initStat('player', 'full_houses', 0);
        self::initStat('player', 'four_of_a_kinds', 0);
        self::initStat('player', 'straight_flushes', 0);

        // TODO: setup the initial game situation here

        // Set values of state variables
        self::setGameStateInitialValue("roundNumber", 0);
        self::setGameStateInitialValue("roundStage", 0);
        self::setGameStateInitialValue("numFoldedPlayers", 0);
        self::setGameStateInitialValue("numAllInPlayers", 0);
        self::setGameStateInitialValue("numEliminatedPlayers", 0);
        self::setGameStateInitialValue("currentBetLevel", 0);
        self::setGameStateInitialValue("smallBlindValue", 1);
        self::setGameStateInitialValue("numBettingPlayers", 0);
        self::setGameStateInitialValue("minimumRaise", 2);
        self::setGameStateInitialValue("areHandsRevealed", 0);

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

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();
        self::setGameStateInitialValue("smallBlindPlayer", self::getActivePlayerId());
        if (count($players) <= 2) {
            // Heads up case (two players left) => dealer is small blind
            self::setGameStateInitialValue("dealerId", self::getActivePlayerId());
        } else {
            self::setGameStateInitialValue("dealerId", self::getPlayerBefore(self::getActivePlayerId()));
        }
        
        self::notifyAllPlayers("changeActivePlayer", clienttranslate('${player_name} is the active player'), array(
            'player_name' => self::getActivePlayerName(),
            'player_id' => self::getActivePlayerId()
        ));

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
        $sql = "SELECT player_id id, player_score score, is_fold, wants_autoblinds, wants_manualbet, player_stock_token_white stock_white, 
            player_stock_token_blue stock_blue, player_stock_token_red stock_red, player_stock_token_green stock_green,
            player_stock_token_black stock_black, player_bet_token_white bet_white, player_bet_token_blue bet_blue,
            player_bet_token_red bet_red, player_bet_token_green bet_green, player_bet_token_black bet_black FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
  
        // TODO: Gather all information about current game situation (visible by player $current_player_id).
  
        // Cards in player hand
        $hands_cards = $this->cards->getCardsInLocation('hand');
        foreach ($hands_cards as $card_id => $card) {
            if ($card["location_arg"] != $current_player_id) {
                // Hide the actual value of other player cards to avoid JS interception cheat
                $hands_cards[$card_id]["type"] = 0;
                $hands_cards[$card_id]["type_arg"] = 0;
                $hands_cards[$card_id]["id"] = 0;
            }
        }
        $result['hands'] = array_values($hands_cards);

        // Cards played on the table
        $result['cardsflop'] = $this->cards->getCardsInLocation('flop');
        $result['cardturn'] = $this->cards->getCardsInLocation('turn');
        $result['cardriver'] = $this->cards->getCardsInLocation('river');

        // Tokens bet at previous betting stages
        $sql = "SELECT token_color, token_number FROM token";
        $result['tokensontable']= self::getCollectionFromDb( $sql );

        // Token values
        $result['tokenvalues'] = $this->token_values;

        // Current active player
        $result['activeplayerid'] = self::getActivePlayerId();

        // Dealer id
        $result['dealer'] = self::getGameStateValue("dealerId");

        // Blinds level
        $small_blind = self::getGameStateValue("smallBlindValue");
        $result['smallblind'] = $small_blind;
        $result['bigblind'] = 2 * $small_blind;

        // Hand number
        $result['handnumber'] = self::getGameStateValue("roundNumber");

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
        $num_players = self::getUniqueValueFromDB("SELECT COUNT(player_id) FROM player");

        return (int)(self::getGameStateValue("numEliminatedPlayers") / ($num_players - 1) * 100);
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

    function findComboIn7CardsHand($hand) {
        // Flatten the hand in to get an array of strings of the format "<value><suit>" (e.g. "9C")
        $cards_flat = array_map(function($card) {return ($card["type_arg"] - 2) * 4 + ($card["type"] - 1);}, $hand);

        self::dump( "Cards flat: ", $cards_flat );
        rsort($cards_flat);
        self::dump( "Sorted Cards flat: ", $cards_flat );

        $values_occurrences = array();
        $suits_occurrences = array();
        $is_straight = FALSE;
        $has_ace = FALSE;

        foreach($cards_flat as $card_id => $card) {
            $card_value = (int)(floor($card / 4) + 2);
            $card_suit = $card % 4 + 1;

            // Check if card is ace as it has a special behaviour for straights
            if ($card_value == 14) {
                $has_ace = TRUE;
            }

            // Increase the number of occurrences of this value
            if (!array_key_exists($card_value, $values_occurrences)) {
                $values_occurrences[$card_value] = 1;
            } else {
                $values_occurrences[$card_value] = $values_occurrences[$card_value] + 1;
            }

            // Increase the number of occurrences of this suit
            if (!array_key_exists($card_suit, $suits_occurrences)) {
                $suits_occurrences[$card_suit] = 1;
            } else {
                $suits_occurrences[$card_suit] = $suits_occurrences[$card_suit] + 1;
            }

            if (!$card_id) {
                // First card of hand
                $num_consecutive = 1;
            } else {
                // Not first card of hand
                $diff_value = $card_value - (int)(floor($cards_flat[$card_id - 1] / 4) + 2);
                if ($diff_value == -1) {
                    $num_consecutive++;
                    if (($num_consecutive >= 5 || ($num_consecutive >= 4 && $has_ace && $card_value == 2)) && !$is_straight) {
                        $is_straight = TRUE;
                        if ($num_consecutive == 4 && $has_ace && $card_value == 2) {
                            // Case of Ace-2-3-4-5 straight
                            $straight_lowest_value = 1;
                        } else {
                            $straight_lowest_value = $card_value;
                        }
                    }
                } else if ($diff_value < -1) {
                    $num_consecutive = 1;
                }
            }
        }

        self::dump( "Suits occurrences: ", $suits_occurrences );
        self::dump( "Values occurrences: ", $values_occurrences );

        $is_flush = count(array_filter($suits_occurrences, function($suit) {return $suit >= 5;})) > 0;
        $num_diff_values = count($values_occurrences);
        $max_num_occurrences = max($values_occurrences);
        $best_combo_hand = array();

        // Check combos in increasing order (decreasing probabilities)
        if ($max_num_occurrences == 2 && $num_diff_values == 6 && !$is_straight && !$is_flush) {
            // One pair (probability: 43.8%) --> comboId = 1
            self::trace("One pair");
            $combo_id = 1;

            $pair_value = array_keys($values_occurrences, 2)[0];
            $kickers = array_slice(array_keys($values_occurrences, 1), 0, 3);

            $best_combo_hand = array_filter($hand, function($card) use($pair_value, $kickers) {
                return $card["type_arg"] == $pair_value || in_array($card["type_arg"], $kickers);
            });

            $combo_value = ($pair_value - 2);
            $kicker_value = ($kickers[0] - 2) * 10000 + ($kickers[1] - 2) * 100 + ($kickers[2] - 2);

        } else if ($max_num_occurrences == 2 && $num_diff_values <= 5 && !$is_straight && !$is_flush) {
            // Two pairs (probability: 23.5%) --> comboId = 2
            self::trace("Two pairs");
            $combo_id = 2;

            $pair_values = array_slice(array_keys($values_occurrences, 2), 0, 2);
            $kicker = array_keys($values_occurrences, 1)[0];

            $best_combo_hand = array_filter($hand, function($card) use($pair_values, $kicker) {
                return in_array($card["type_arg"], $pair_values) || $card["type_arg"] == $kicker;
            });

            $combo_value = ($pair_values[0] - 2) * 100 + ($pair_values[1] - 2);
            $kicker_value = $kicker - 2;

        } else if ($max_num_occurrences == 1 && !$is_straight && !$is_flush) {
            // High card (probability: 17.4%) --> comboId = 0
            self::trace("High card");
            $combo_id = 0;

            $kickers = array_slice(array_keys($values_occurrences, 1), 0, 5);

            $best_combo_hand = array_filter($hand, function($card) use($kickers) {
                return in_array($card["type_arg"], $kickers);
            });

            $combo_value = $kickers[0] - 2;
            $kicker_value = ($kickers[1] - 2) * 1000000 + ($kickers[2] - 2) * 10000 + ($kickers[3] - 2) * 100 + ($kickers[4] - 2);

        } else if ($max_num_occurrences == 3 && $num_diff_values == 5 && !$is_straight && !$is_flush) {
            // Three of a kind (probability: 4.83%) --> comboId = 3
            self::trace("Three of a kind");
            $combo_id = 3;

            $three_of_a_kind_value = array_keys($values_occurrences, 3)[0];
            $kickers = array_slice(array_keys($values_occurrences, 1), 0, 2);

            $best_combo_hand = array_filter($hand, function($card) use($three_of_a_kind_value, $kickers) {
                return $card["type_arg"] == $three_of_a_kind_value || in_array($card["type_arg"], $kickers);
            });

            $combo_value = ($three_of_a_kind_value - 2);
            $kicker_value = ($kickers[0] - 2) * 100 + ($kickers[1] - 2);

        } else if ($is_straight && !$is_flush) {
            // Straight (probability: 4.62%) --> comboId = 4
            self::trace("Straight");
            $combo_id = 4;

            foreach ($hand as $card) {
                // Check if card is between lowest straight value and (4 + lowest straigh value)
                if (($card["type_arg"] <= ($straight_lowest_value + 4) && $card["type_arg"] >= $straight_lowest_value) || ($straight_lowest_value == 1 && $card["type_arg"] == 14)) {
                    // Check if a card with the same value is already in the selected cards
                    if (!in_array($card["type_arg"], array_map(function($card) {return $card["type_arg"];}, $best_combo_hand))) {
                        $best_combo_hand[] = $card;
                    }
                }
            }

            $combo_value = ($straight_lowest_value + 4) - 2;
            $kicker_value = 0;

        } else if (!$is_straight && $is_flush) {
            // Flush (probability: 3.03%) --> comboId = 5
            self::trace("Flush");
            $combo_id = 5;

            // Identify suit of the flush
            $flush_suit = array_keys(array_filter($suits_occurrences, function($suit) {return $suit >= 5;}))[0];

            // Iterate over values in decreasing order and add to the best combo hand the card of this value if it has the flush suit
            array_walk($values_occurrences, function($occurrences, $value) use(&$best_combo_hand, $flush_suit, $hand) {
                // Check if best combo hand is already full
                if (count($best_combo_hand) < 5) {
                    $new_card = array_filter($hand, function($card) use($value, $flush_suit) {
                        return $card["type_arg"] == $value && $card["type"] == $flush_suit;
                    });
                    if (count($new_card) > 0) {
                        $best_combo_hand[] = array_values($new_card)[0];
                    }
                }
            });

            $flush_values = array_map(function($card) {return $card["type_arg"];}, $best_combo_hand);
            rsort($flush_values);

            $combo_value = $flush_values[0] - 2;
            $kicker_value = ($flush_values[1] - 2) * 1000000 + ($flush_values[2] - 2) * 10000 + ($flush_values[3] - 2) * 100 + ($flush_values[4] - 2);

        } else if ($max_num_occurrences == 3 && $num_diff_values <= 4 && !$is_straight && !$is_flush) {
            // Full house (probability: 2.60%) --> comboId = 6
            self::trace("Full house");
            $combo_id = 6;

            $three_of_a_kind_value = array_keys($values_occurrences, 3)[0];

            // Add best three of a kind to the best combo hand
            $best_combo_hand = array_filter($hand, function($card) use($three_of_a_kind_value) {
                return $card["type_arg"] == $three_of_a_kind_value;
            });

            // Add best pair to the best combo hand
            array_walk($values_occurrences, function($occurrences, $value) use(&$best_combo_hand, $hand, $three_of_a_kind_value) {
                if ($value != $three_of_a_kind_value && $occurrences >= 2 && count($best_combo_hand) < 5) {
                    $pair = array_values(array_filter($hand, function($card) use($value) {return $card["type_arg"] == $value;}));
                    $best_combo_hand[] = $pair[0];
                    $best_combo_hand[] = $pair[1];
                }
            });

            $pair_value = array_values($best_combo_hand)[3]["type_arg"];
            $combo_value = ($three_of_a_kind_value - 2) * 100 + ($pair_value - 2);
            $kicker_value = 0;

        } else if ($max_num_occurrences == 4) {
            // Four of a kind (probability: 0.168%) --> comboId = 7
            self::trace("Four of a kind");
            $combo_id = 7;

            $four_of_a_kind_value = array_keys($values_occurrences, 4)[0];

            $best_combo_hand = array_filter($hand, function($card) use($four_of_a_kind_value) {
                return $card["type_arg"] == $four_of_a_kind_value;
            });

            array_walk($values_occurrences, function($occurrences, $value) use(&$best_combo_hand, $hand, $four_of_a_kind_value) {
                if ($value != $four_of_a_kind_value && count($best_combo_hand) < 5) {
                    $best_combo_hand[] = array_values(array_filter($hand, function($card) use($value) {return $card["type_arg"] == $value;}))[0];
                }
            });

            $combo_value = $four_of_a_kind_value - 2;
            $kicker_value = array_values($best_combo_hand)[4]["type_arg"] - 2; // Kicker value
        } else if ($is_straight && $is_flush) {
            // Straight flush OR Flush

            // Check if the straight cards and the flush cards are the same
            $flush_suit = array_keys(array_filter($suits_occurrences, function($suit) {return $suit >= 5;}))[0];
            $best_combo_hand = array_filter($hand, function($card) use($flush_suit) {
                return $card["type"] == $flush_suit;
            });

            usort($best_combo_hand, function($card_a, $card_b) {
                return $card_a["type_arg"] < $card_b["type_arg"];
            });

            // Check each possible sub-hand to identify the best one with a straigh
            $num_cards_of_flush_suit = count($best_combo_hand);
            $is_straight_flush = FALSE;
            for ($start_idx = 0; $start_idx <= ($num_cards_of_flush_suit - 5); $start_idx++) {
                // Extract values of cards of the sub-hand
                $values = array_map(function($card) {return $card["type_arg"];}, array_slice($best_combo_hand, $start_idx, 5));
                $is_straight_sub_hand = TRUE;
                foreach ($values as $value_id => $value) {
                    if ($value_id < 4) {
                        // Check if the value between the card value and the next card value is 1. If not, it is not a straight
                        if (($values[$value_id + 1] - $value) != -1) {
                            $is_straight_sub_hand = FALSE;
                            break;
                        }
                    }
                }
                // If it is a straight, it is the best possible one because cards were sorted in descending order
                // => This sub hand is the best combo
                if ($is_straight_sub_hand) {
                    // Straight flush (probability: 0.0279%) --> comboId = 8
                    self::trace("Straight flush");
                    $combo_id = 8;
                    $is_straight_flush = TRUE;
                    $best_combo_hand = array_slice($best_combo_hand, $start_idx, 5);

                    $combo_value = array_values($best_combo_hand)[0]["type_arg"] - 2;
                    $kicker_value = 0;
                    break;
                }
            }
            if (!$is_straight_flush) {
                // Flush (probability: 3.03%) --> comboId = 5
                self::trace("Flush");
                $combo_id = 5;
                $best_combo_hand = array_slice($best_combo_hand, 0, 5);

                $flush_values = array_map(function($card) {return $card["type_arg"];}, $best_combo_hand);
                rsort($flush_values);

                $combo_value = $flush_values[0] - 2;
                $kicker_value = ($flush_values[1] - 2) * 1000000 + ($flush_values[2] - 2) * 10000 + ($flush_values[3] - 2) * 100 + ($flush_values[4] - 2);
            }
        } else {
            // Wtf? it shouldn't reach this code chunk
            throw new BgaUserException(_("Unrecognized combo"));
        }

        usort($best_combo_hand, function($card_a, $card_b) {
            return $card_a["type_arg"] < $card_b["type_arg"];
        });

        self::dump("Best combo hand: ", array_map(function($card) {
            return $this->values_label[$card["type_arg"]] . " of " . $this->suits[$card["type"]]["name"];
        }, $best_combo_hand));
        self::dump("Combo Id: ", $combo_id);
        self::dump("Combo Value: ", $combo_value);
        self::dump("Kicker Value: ", $kicker_value);
        self::dump("Best combo hand: ", array_map(function($card) {
            return $this->values_label[$card["type_arg"]] . " of " . $this->suits[$card["type"]]["name"];
        }, $best_combo_hand));

        return array("hand" => $best_combo_hand, "comboId" => $combo_id, "comboValue" => $combo_value, "kickerValue" => $kicker_value);
    }

    // Display the content of a variable as a string (For debugging purposes)
    function varDumpToString($var) {
        ob_start();
        var_dump($var);
        $result = ob_get_clean();
        throw new BgaUserException($result);
     }

    function compareHands($combo_a, $combo_b) {
        // Compare combo ranks
        if ($combo_a["comboId"] != $combo_b["comboId"]) {
            return ($combo_a["comboId"] > $combo_b["comboId"]) ? -1 : 1;
        } else {
            // Compare combo values
            if ($combo_a["comboValue"] != $combo_b["comboValue"]) {
                return ($combo_a["comboValue"] > $combo_b["comboValue"]) ? -1 : 1;
            } else {
                // Compare kicker values
                if ($combo_a["kickerValue"] != $combo_b["kickerValue"]) {
                    return ($combo_a["kickerValue"] > $combo_b["kickerValue"]) ? -1 : 1;
                } else {
                    return 0;
                }
            }
        }
    }
    
    // Calculate the value of tokens in each player's stock and betting area
    function getPlayersTokens() {
        $sql = "SELECT player_id, player_stock_token_white, player_stock_token_blue, player_stock_token_red, 
            player_stock_token_green, player_stock_token_black, player_bet_token_white, player_bet_token_blue, 
            player_bet_token_red, player_bet_token_green, player_bet_token_black FROM player";

        $players_tokens = self::getCollectionFromDb($sql);
        $players_tokens_value = array();

        $colors = ["white", "blue", "red", "green", "black"];

        foreach ($players_tokens as $player_id => $player) {
            $players_tokens_value[$player_id]["bet"] = 0;
            $players_tokens_value[$player_id]["stock"] = 0;
            foreach($colors as $color) {
                $players_tokens_value[$player_id]["stock"] += $this->token_values[$color] * $player["player_stock_token_".$color];
                $players_tokens_value[$player_id]["bet"] += $this->token_values[$color] * $player["player_bet_token_".$color];
            }
        }

        return $players_tokens_value;
    }

    function computeBet($tokens, $player_id) {
        // Query current player's tokens stock
        $sql = "SELECT player_stock_token_white, player_stock_token_blue, player_stock_token_red, 
            player_stock_token_green, player_stock_token_black, player_bet_token_white, player_bet_token_blue, 
            player_bet_token_red, player_bet_token_green, player_bet_token_black, player_score FROM player WHERE player_id = '" . $player_id . "'";
        $current_tokens = self::getObjectFromDB($sql);
        $diff_stock = array();

        // Update player table with new number of tokens in stock and betting area
        $keys = array(
            "player_stock_token_white", "player_bet_token_white",
            "player_stock_token_blue", "player_bet_token_blue",
            "player_stock_token_red", "player_bet_token_red",
            "player_stock_token_green", "player_bet_token_green",
            "player_stock_token_black", "player_bet_token_black"
        );

        $is_all_in = TRUE;
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
                $diff_stock[$color] = $token_number - $current_tokens[$keys[$token_id]];

                // If for any color there is more than 1 token in stock, the player is not all in
                if ($token_number > 0) {
                    $is_all_in = FALSE;
                }
            }
        }
        $sql .= " WHERE player_id = '" . $player_id. "'";

        // Calculate additional bet
        $additional_bet = 0; // Value of tokens added to the betting area in the current place bet action
        $current_player_bet = 0; // Value of the tokens already present in the betting area before the current place bet action
        foreach($diff_stock as $color => $token_diff) {
            $additional_bet -= $this->token_values[$color] * $token_diff;
            $current_player_bet += $this->token_values[$color] * $current_tokens["player_bet_token_".$color];
        }
        $total_player_bet = $current_player_bet + $additional_bet; // Value of tokens that will be in the betting area after the current place bet action


        return array(
            'additional_bet' => $additional_bet,
            'current_player_bet' => $current_player_bet,
            'total_player_bet' => $total_player_bet,
            'diff_stock' => $diff_stock,
            'is_all_in' => $is_all_in,
            'player_score' => $current_tokens["player_score"],
            'token_update_sql' => $sql
        );
    }

    function moveTokens($from, $to, $value) {
        // Get current state of pot and players token
        $sql = "SELECT player_id, player_name, player_stock_token_white, player_stock_token_blue, player_stock_token_red, 
            player_stock_token_green, player_stock_token_black, player_bet_token_white, player_bet_token_blue, 
            player_bet_token_red, player_bet_token_green, player_bet_token_black FROM player";
        $players_tokens = self::getCollectionFromDb($sql);
        $players_tokens_value = self::getPlayersTokens();

        $colors = ["white", "blue", "red", "green", "black"];

        // Gather information on the source ("from")
        $from_tokens = array();
        if ($from == "pot") {
            $from_player_id = NULL;
            $sql = "SELECT token_color, token_number FROM token";
            $pot_tokens = self::getCollectionFromDb($sql);
            foreach ($colors as $color) {
                $from_tokens[$color] = $pot_tokens[$color]["token_number"];
            }
            $source_name = "the pot";
        } else if (strpos($from, "stock") !== false) {
            $from_player_id = str_replace("stock_", "", $from);
            foreach ($colors as $color) {
                $from_tokens[$color] = $players_tokens[$from_player_id]["player_stock_token_${color}"];
            }
            $source_name = $players_tokens[$from_player_id]["player_name"] . "'s stock";
        } else if (strpos($from, "bet") !== false) {
            $from_player_id = str_replace("bet_", "", $from);
            foreach ($colors as $color) {
                $from_tokens[$color] = $players_tokens[$from_player_id]["player_bet_token_${color}"];
            }
            $source_name = $players_tokens[$from_player_id]["player_name"] . "'s betting area";
        } else {
            throw new feException("Unrecognized source in moveTokens(\$from = ${from},...)");
        }

        // Gather information on the destination ("to")
        if ($to == "pot") {
            $to_player_id = NULL;
            $sql = "SELECT token_color, token_number FROM token";
            $to_tokens = self::getCollectionFromDb($sql);
            $destination_name = "the pot";
        } else if (strpos($to, "stock") !== false) {
            $to_player_id = str_replace("stock_", "", $to);
            $to_tokens = array();
            foreach ($colors as $color) {
                $to_tokens[$color] = $players_tokens[$to_player_id]["player_stock_token_${color}"];
            }
            $destination_name = $players_tokens[$to_player_id]["player_name"] . "'s stock";
        } else if (strpos($to, "bet") !== false) {
            $to_player_id = str_replace("bet_", "", $to);
            $to_tokens = array();
            foreach ($colors as $color) {
                $to_tokens[$color] = $players_tokens[$to_player_id]["player_bet_token_${color}"];
            }
            $destination_name = $players_tokens[$to_player_id]["player_name"] . "'s betting area";
        } else {
            throw new feException("Unrecognized destination in moveTokens(\$to = ${to},...)");
        }

        // Convert value to tokens number
        $token_diff = array(
            "white" => 0,
            "blue" => 0,
            "red" => 0,
            "green" => 0,
            "black" => 0
        );

        $remaining_value = $value;
        $token_added = TRUE;
        while ($remaining_value > 0 && $token_added) {
            $token_added = FALSE;
            foreach (array_reverse($colors) as $color) {
                if ($remaining_value >= $this->token_values[$color] && $token_diff[$color] < $from_tokens[$color]) {
                    self::trace("Remaining value: ${remaining_value}, " . ($from_tokens[$color] - $token_diff[$color]) . " ${color} chips remaining in source. Moving 1 from ${from} to ${to}");
                    $token_diff[$color]++;
                    $remaining_value -= $this->token_values[$color];
                    $token_added = TRUE;
                }
            }
        }

        if ($remaining_value > 0) {
            // Try to make change in the source tokens
            $change_done = FALSE;
            foreach ($colors as $color_id => $color) {
                if ($from_tokens[$color] - $token_diff[$color] > 0 && $remaining_value < $this->token_values[$color]) {
                    // Exchange a token from source with the corresponding value in tokens of the lowest value
                    $from_tokens[$color]--;
                    $num_small_tokens = (int)floor($this->token_values[$color] / $this->token_values[$colors[0]]);
                    self::notifyAllPlayers("changeRequired", clienttranslate('The current chips in ${source_name} cannot make ${value}. A ${color} chip is exchanged against ${num_small_tokens} ${small_color} chips.'), array(
                        'i18n' => array('source_name', 'color', 'small_color'),
                        'source_name' => $source_name,
                        'from' => $from,
                        'value' => $value,
                        'color' => $color,
                        'small_color' => $colors[0],
                        'num_small_tokens' => $num_small_tokens,
                    ));
                    $from_tokens[$colors[0]] += $num_small_tokens;
                    $token_diff[$colors[0]] += (int)floor($remaining_value / $this->token_values[$colors[0]]);
                    $value = (int)floor($remaining_value / $this->token_values[$colors[0]]) * $this->token_values[$colors[0]];
                    $remaining_value -= $value;
                    $change_done = TRUE;
                    break;
                }
            }
            if (!$change_done) {
                throw new feException(_("Remaining value (${remaining_value}) is larger than 0."));
            }
        }

        // Update source tokens
        if ($from == "pot") {
            foreach ($colors as $color) {
                $sql_from = "UPDATE token SET token_number = " . ($from_tokens[$color] - $token_diff[$color]) . " WHERE token_color = '${color}'";
                self::DbQuery($sql_from);
                $from_tokens[$color] -= $token_diff[$color];
            }
        } else if (strpos($from, "stock") !== false) {
            $sql_from = "UPDATE player SET";
            foreach ($colors as $id => $color) {
                $sql_from .= " player_stock_token_${color} = " . ($from_tokens[$color] - $token_diff[$color]);
                $from_tokens[$color] -= $token_diff[$color];
                if ($id < (count($colors) - 1)) {
                    $sql_from .= ",";
                }
            }
            $sql_from .= " WHERE player_id = ${from_player_id}";
            self::DbQuery($sql_from);
        } else if (strpos($from, "bet") !== false) {
            $sql_from = "UPDATE player SET";
            foreach ($colors as $id => $color) {
                $sql_from .= " player_bet_token_${color} = " . ($from_tokens[$color] - $token_diff[$color]);
                $from_tokens[$color] -= $token_diff[$color];
                if ($id < (count($colors) - 1)) {
                    $sql_from .= ",";
                }
            }
            $sql_from .= " WHERE player_id = ${from_player_id}";
            self::DbQuery($sql_from);
        }

        // Update destination tokens
        if ($to == "pot") {
            foreach ($colors as $color) {
                $sql_to = "UPDATE token SET token_number = " . ($to_tokens[$color] + $token_diff[$color]) . " WHERE token_color = '${color}'";
                self::DbQuery($sql_to);
                $to_tokens[$color] += $token_diff[$color];
            }
        } else if (strpos($to, "stock") !== false) {
            $sql_to = "UPDATE player SET";
            foreach ($colors as $id => $color) {
                $sql_to .= " player_stock_token_${color} = " . ($to_tokens[$color] + $token_diff[$color]);
                $to_tokens[$color] += $token_diff[$color];
                if ($id < (count($colors) - 1)) {
                    $sql_to .= ",";
                }
            }
            $sql_to .= " WHERE player_id = ${to_player_id}";
            self::DbQuery($sql_to);
        } else if (strpos($to, "bet") !== false) {
            $sql_to = "UPDATE player SET";
            foreach ($colors as $id => $color) {
                $sql_to .= " player_bet_token_${color} = " . ($to_tokens[$color] + $token_diff[$color]);
                $to_tokens[$color] += $token_diff[$color];
                if ($id < (count($colors) - 1)) {
                    $sql_to .= ",";
                }
            }
            $sql_to .= " WHERE player_id = ${to_player_id}";
            self::DbQuery($sql_to);
        }

        // Notify the client to move the tokens
        if ($value > 0) {
            self::notifyAllPlayers("moveTokens", clienttranslate('${value} is moved from ${source_name} to ${destination_name}'), array(
                'i18n' => array('source_name', 'destination_name'),
                'value' => $value,
                'source_name' => $source_name,
                'destination_name' => $destination_name,
                'from' => $from,
                'to' => $to,
                'from_tokens' => $from_tokens,
                'to_tokens' => $to_tokens,
                'token_diff' => $token_diff
            ));
        }
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

    function placeSmallBlind($tokens, $is_auto) {
        if (!$is_auto) {
            self::checkAction("placeSmallBlind");
        }
        $player_id = self::getActivePlayerId();
        $player_name = self::getActivePlayerName();
        $current_round_stage = self::getGameStateValue("roundStage");
        $blind_value = self::getGameStateValue("smallBlindValue");

        // Send notification for dialog bubble
        self::notifyAllPlayers("announceAction", '', array(
            'player_id' => $player_id,
            'message' => clienttranslate("I place the small blind")
        ));

        // Get current level of bet
        $current_bet_level = self::getGameStateValue("currentBetLevel");

        $bet_computation = self::computeBet($tokens, $player_id);
        $additional_bet = $bet_computation["additional_bet"];
        $diff_stock = $bet_computation["diff_stock"];
        $is_all_in = $bet_computation["is_all_in"];
        $player_score = $bet_computation["player_score"];
        $sql = $bet_computation["token_update_sql"];

        if ($additional_bet != $blind_value) {
            // The player has not placed the correct amount of tokens for the small blind
            if ($additional_bet < $blind_value && $is_all_in) {
                // The player has placed all his tokens but that's not sufficient for the small blind
                self::DbQuery($sql);
                self::notifyAllPlayers("betPlaced", clienttranslate('${player_name} does not have enough stock to place the small blind. She/he is all in with ${all_in_value} added to the bet.'), array(
                    'player_id' => $player_id,
                    'player_name' => $player_name,
                    'additional_bet' => $additional_bet,
                    'diff_stock' => $diff_stock,
                    'all_in_value' => $additional_bet,
                    'show_all' => FALSE
                ));
            } else {
                if ($additional_bet != 0) {
                    self::notifyPlayer($player_id, "betPlaced", clienttranslate('Incorrect bet placed. Moving back ${additional_bet} to your stock.'), array(
                        'player_id' => $player_id,
                        'additional_bet' => $additional_bet,
                        'diff_stock' => array_map(function($token_num) {return -$token_num;}, $diff_stock),
                        'show_all' => TRUE
                    ));
                }
                if ($player_score >= $blind_value) {
                    // The player has enough in stock for the small blind => place the tokens for him
                    self::moveTokens("stock_${player_id}", "bet_${player_id}", $blind_value);
                    self::notifyAllPlayers("smallBlindPlaced", clienttranslate('${player_name} places the small blind'), array(
                        'player_name' => $player_name
                    ));
                    $additional_bet = $blind_value;
                    $is_all_in = $player_score == $blind_value;
                } else {
                    // The player does not have enough in stock for the small blind => he goes all in
                    self::notifyAllPlayers("allInSmallBlind", clienttranslate('${player_name} does not have enough stock to place the small blind. She/he is all in with ${all_in_value} added to the bet.'), array(
                        'player_name' => $player_name,
                        'all_in_value' => $player_score
                    ));
                    self::moveTokens("stock_${player_id}", "bet_${player_id}", $player_score);
                    self::notifyAllPlayers("smallBlindPlaced", clienttranslate('${player_name} places the small blind'), array(
                        'player_name' => $player_name
                    ));
                    $additional_bet = $player_score;
                    $is_all_in = TRUE;
                }
            }
        } else {
            // Notify other player that the player placed the small blind
            self::DbQuery($sql);
            self::notifyAllPlayers("betPlaced", clienttranslate('${player_name} bets ${additional_bet} for the small blind'), array(
                'player_id' => $player_id,
                'player_name' => $player_name,
                'additional_bet' => $additional_bet,
                'diff_stock' => $diff_stock,
                'show_all' => FALSE
            ));
        }
        self::setGameStateValue("currentBetLevel", $additional_bet);

        // Increment the number of all in players if the player bet all his stock
        if ($additional_bet > 0 && $is_all_in) {
            $sql = "UPDATE player SET is_all_in = 1 WHERE player_id = " . $player_id;
            self::DbQuery($sql);

            self::incGameStateValue("numAllInPlayers", 1);
        } else {
            self::incGameStateValue("numBettingPlayers", 1); // An additional player has played for this betting round
        }

        $this->gamestate->nextState('placeSmallBlind');
    }

    function placeBigBlind($tokens, $is_auto) {
        if (!$is_auto) {
            self::checkAction("placeBigBlind");
        }
        $player_id = self::getActivePlayerId();
        $player_name = self::getActivePlayerName();
        $current_round_stage = self::getGameStateValue("roundStage");
        $blind_value = self::getGameStateValue("smallBlindValue");

        // Send notification for dialog bubble
        self::notifyAllPlayers("announceAction", '', array(
            'player_id' => $player_id,
            'message' => clienttranslate("I place the big blind")
        ));

        // Get current level of bet
        $current_bet_level = self::getGameStateValue("currentBetLevel");

        $bet_computation = self::computeBet($tokens, $player_id);
        $additional_bet = $bet_computation["additional_bet"];
        $diff_stock = $bet_computation["diff_stock"];
        $is_all_in = $bet_computation["is_all_in"];
        $player_score = $bet_computation["player_score"];
        $sql = $bet_computation["token_update_sql"];

        $big_blind = 2 * $blind_value;
        if ($additional_bet != $big_blind) {
            if ($additional_bet < $big_blind && $is_all_in) {
                // The player has placed all his tokens but that's not sufficient for the small blind
                self::DbQuery($sql);
                self::notifyAllPlayers("betPlaced", clienttranslate('${player_name} does not have enough stock to place the big blind. She/he is all in with ${all_in_value} added to the bet.'), array(
                    'player_id' => $player_id,
                    'player_name' => $player_name,
                    'additional_bet' => $additional_bet,
                    'diff_stock' => $diff_stock,
                    'all_in_value' => $additional_bet,
                    'show_all' => FALSE
                ));
            } else {
                if ($additional_bet != 0) {
                    self::notifyPlayer($player_id, "betPlaced", clienttranslate('Incorrect bet placed. Moving back ${additional_bet} to your stock.'), array(
                        'player_id' => $player_id,
                        'additional_bet' => $additional_bet,
                        'diff_stock' => array_map(function($token_num) {return -$token_num;}, $diff_stock),
                        'show_all' => TRUE
                    ));
                }

                if ($player_score >= $big_blind) {
                    // The player has enough in stock for the big blind => place the tokens for him
                    self::moveTokens("stock_${player_id}", "bet_${player_id}", $big_blind);
                    self::notifyAllPlayers("bigBlindPlaced", clienttranslate('${player_name} places the big blind'), array(
                        'player_name' => $player_name
                    ));
                    $additional_bet = $big_blind;
                    $is_all_in = $player_score == $big_blind;
                } else {
                    // The player does not have enough in stock for the big blind => he goes all in
                    self::notifyAllPlayers("allInBigBlind", clienttranslate('${player_name} does not have enough stock to place the big blind. She/he is all in with ${all_in_value} added to the bet.'), array(
                        'player_name' => $player_name,
                        'all_in_value' => $player_score
                    ));
                    self::moveTokens("stock_${player_id}", "bet_${player_id}", $player_score);
                    self::notifyAllPlayers("bigBlindPlaced", clienttranslate('${player_name} places the big blind'), array(
                        'player_name' => $player_name
                    ));
                    $additional_bet = $player_score;
                    $is_all_in = TRUE;
                }
            }
        } else {
            // Notify other player that the player placed the small blind
            self::DbQuery($sql);
            self::notifyAllPlayers("betPlaced", clienttranslate('${player_name} bets ${additional_bet} for the big blind'), array(
                'player_id' => $player_id,
                'player_name' => $player_name,
                'additional_bet' => $additional_bet,
                'diff_stock' => $diff_stock,
                'show_all' => FALSE
            ));
        }
        if ($additional_bet > self::getGameStateValue("currentBetLevel")) {
            self::setGameStateValue("currentBetLevel", $additional_bet);
        }

        // Increment the number of all in players if the player bet all his stock
        if ($additional_bet > 0 && $is_all_in) {
            $sql = "UPDATE player SET is_all_in = 1 WHERE player_id = " . $player_id;
            self::DbQuery($sql);

            self::incGameStateValue("numAllInPlayers", 1);
        } else {
            self::incGameStateValue("numBettingPlayers", 1); // An additional player has played for this betting round
        }

        $this->gamestate->nextState('placeBigBlind');
    }

    function check($tokens) {
        self::checkAction("placeBet");
        $player_id = self::getActivePlayerId();
        $player_name = self::getActivePlayerName();
        $small_blind_player = self::getGameStateValue("smallBlindPlayer");
        $current_bet_level = self::getGameStateValue("currentBetLevel");
        $current_round_stage = self::getGameStateValue("roundStage");
        $blind_value = self::getGameStateValue("smallBlindValue");

        // Send notification for dialog bubble
        self::notifyAllPlayers("announceAction", '', array(
            'player_id' => $player_id,
            'message' => clienttranslate("I check")
        ));

        $bet_computation = self::computeBet($tokens, $player_id);
        $additional_bet = $bet_computation["additional_bet"];
        $current_player_bet = $bet_computation["current_player_bet"];
        $diff_stock = $bet_computation["diff_stock"];

        if ($current_round_stage == 1) {
            if ($current_bet_level != 2 * $blind_value || $current_player_bet != 2 * $blind_value) {
                throw new BgaUserException(_("You cannot check at this stage"));
            }
        } else {
            if ($current_bet_level != 0) {
                throw new BgaUserException(_("You cannot check because the current bet is not 0"));
            }
        }

        if ($additional_bet > 0) {
            self::notifyPlayer($player_id, "betPlaced", clienttranslate('No chip should be bet for a check action. Moving back ${additional_bet} to your stock.'), array(
                'player_id' => $player_id,
                'additional_bet' => $additional_bet,
                'diff_stock' => array_map(function($token_num) {return -$token_num;}, $diff_stock),
                'show_all' => TRUE
            ));
        }

        // Notify other player that the player checked
        self::notifyAllPlayers("check", clienttranslate( '${player_name} checks' ), array(
            'player_name' => $player_name
        ));

        self::incGameStateValue("numBettingPlayers", 1); // An additional player has played for this betting round

        // Increment stats
        self::incStat(1, "checks", $player_id);

        $this->gamestate->nextState('placeBet');
    }

    function call($tokens) {
        self::checkAction("placeBet");
        $player_id = self::getActivePlayerId();
        $player_name = self::getActivePlayerName();   

        // Get current level of bet
        $current_bet_level = self::getGameStateValue("currentBetLevel");

        // Send notification for dialog bubble
        self::notifyAllPlayers("announceAction", '', array(
            'player_id' => $player_id,
            'message' => clienttranslate("I call")
        ));

        $bet_computation = self::computeBet($tokens, $player_id);
        $additional_bet = $bet_computation["additional_bet"];
        $diff_stock = $bet_computation["diff_stock"];
        $current_player_bet = $bet_computation["current_player_bet"];
        $total_player_bet = $bet_computation["total_player_bet"]; // Value of tokens that will be in the betting area after the current place bet action
        $is_all_in = $bet_computation["is_all_in"];
        $player_score = $bet_computation["player_score"];
        $sql = $bet_computation["token_update_sql"];

        $player_tokens = self::getPlayersTokens()[$player_id];

        if ($total_player_bet == $current_bet_level) {
            // The player has already put the correct amount of tokens in the betting area
            self::DbQuery($sql);
            self::notifyAllPlayers("betPlaced", clienttranslate('${player_name} calls by betting ${additional_bet}'), array(
                'player_id' => $player_id,
                'player_name' => $player_name,
                'additional_bet' => $additional_bet,
                'diff_stock' => $diff_stock,
                'show_all' => FALSE
            ));
        } else {
            if ($total_player_bet < $current_bet_level && $is_all_in) {
                // The player has put all his tokens in the betting area but that is not sufficient to call
                self::DbQuery($sql);
                self::notifyAllPlayers("betPlaced", clienttranslate('${player_name} does not have enough stock to call. She/he is all in with ${all_in_value} added to the bet.'), array(
                    'player_id' => $player_id,
                    'player_name' => $player_name,
                    'additional_bet' => $additional_bet,
                    'diff_stock' => $diff_stock,
                    'all_in_value' => $total_player_bet,
                    'show_all' => FALSE
                ));
            } else {
                // The player has not put the correct amount of tokens in the betting area and is not all in
                // => Move back his newly bet token and bet the correct amount for him
                if ($additional_bet != 0) {
                    self::notifyPlayer($player_id, "betPlaced", clienttranslate('Incorrect bet placed. Moving back ${additional_bet} to your stock.'), array(
                        'player_id' => $player_id,
                        'additional_bet' => $additional_bet,
                        'diff_stock' => array_map(function($token_num) {return -$token_num;}, $diff_stock),
                        'show_all' => TRUE
                    ));
                }
                if ($player_tokens["stock"] >= ($current_bet_level - $player_tokens["bet"])) {
                    // The player has enough stock to call
                    self::moveTokens("stock_${player_id}", "bet_${player_id}", $current_bet_level - $player_tokens["bet"]);
                    self::notifyAllPlayers("callPlaced", clienttranslate('${player_name} calls by betting ${additional_bet}'), array(
                        'player_name' => $player_name,
                        'additional_bet' => $current_bet_level - $player_tokens["bet"]
                    ));
                    $additional_bet = $current_bet_level - $player_tokens["bet"];
                    $is_all_in = $player_tokens["stock"] == ($current_bet_level - $player_tokens["bet"]);
                } else {
                    // The player does not have enough stock to call => he goes all in
                    self::notifyAllPlayers("allInCall", clienttranslate('${player_name} does not have enough stock to call. She/he is all in with ${all_in_value} added to the bet.'), array(
                        'player_name' => $player_name,
                        'all_in_value' => $player_tokens["stock"]
                    ));
                    self::moveTokens("stock_${player_id}", "bet_${player_id}", $player_tokens["stock"]);
                    self::notifyAllPlayers("callPlaced", clienttranslate('${player_name} calls'), array(
                        'player_name' => $player_name
                    ));
                    $additional_bet = $player_tokens["stock"];
                    $is_all_in = TRUE;
                }
            }
        }

        if ($additional_bet > 0 && $is_all_in) {
            $sql = "UPDATE player SET is_all_in = 1 WHERE player_id = " . $player_id;
            self::DbQuery($sql);

            self::incGameStateValue("numAllInPlayers", 1);
        } else {
            self::incGameStateValue("numBettingPlayers", 1); // An additional player has played for this betting round
        }

        // Increment stats
        self::incStat(1, "times_called", $player_id);

        $this->gamestate->nextState('placeBet');
    }

    function raise($tokens) {
        self::checkAction("placeBet");
        $player_id = self::getActivePlayerId();
        $player_name = self::getActivePlayerName();
        $current_round_stage = self::getGameStateValue("roundStage");
        $minimum_raise = self::getGameStateValue("minimumRaise");
        $current_bet_level = self::getGameStateValue("currentBetLevel");

        $bet_computation = self::computeBet($tokens, $player_id);
        $additional_bet = $bet_computation["additional_bet"];
        $diff_stock = $bet_computation["diff_stock"];
        $total_player_bet = $bet_computation["total_player_bet"]; // Value of tokens that will be in the betting area after the current place bet action
        $is_all_in = $bet_computation["is_all_in"];
        $sql = $bet_computation["token_update_sql"];
        self::DbQuery($sql);

        $raise_amount = $total_player_bet - $current_bet_level;

        // Send notification for dialog bubble
        if ($current_bet_level == 0) {
            self::notifyAllPlayers("announceAction", '', array(
                'player_id' => $player_id,
                'message' => _("I bet ${raise_amount}")
            ));
        } else {
            self::notifyAllPlayers("announceAction", '', array(
                'player_id' => $player_id,
                'message' => _("I raise by ${raise_amount}")
            ));
        }

        if ($current_round_stage == 0) {
            throw new feException(_("You are not supposed to raise during the blinds phase"));
        } else if ($total_player_bet > $current_bet_level) {

            // Check that the player raises by a sufficient amount
            if ($raise_amount >= $minimum_raise) {
                if ($current_bet_level == 0) {
                    self::notifyAllPlayers("betPlaced", clienttranslate('${player_name} bets ${raise_amount}'), array(
                        'player_id' => $player_id,
                        'player_name' => $player_name,
                        'additional_bet' => $additional_bet,
                        'diff_stock' => $diff_stock,
                        'raise_amount' => $raise_amount,
                        'show_all' => FALSE
                    ));
                } else {
                    self::notifyAllPlayers("betPlaced", clienttranslate('${player_name} raises by ${raise_amount}'), array(
                        'player_id' => $player_id,
                    'player_name' => $player_name,
                    'additional_bet' => $additional_bet,
                    'diff_stock' => $diff_stock,
                    'raise_amount' => $raise_amount,
                    'show_all' => FALSE
                    ));
                }
    
                // Update current bet level
                self::setGameStateValue("currentBetLevel", $total_player_bet);
                self::setGameStateValue("minimumRaise", $raise_amount);
            } else {
                throw new BgaUserException(_("You need to raise by at least ${minimum_raise}. You currently raised by ${raise_amount}."));
            }
        } else {
            throw new BgaUserException(_("To raise you need to bet at least " . ($current_bet_level + $minimum_raise) . ". You currently bet ${total_player_bet}."));
        }

        // Increment the number of all in players if the player bet all his stock
        if ($total_player_bet > 0 && $is_all_in) {
            $sql = "UPDATE player SET is_all_in = 1 WHERE player_id = " . $player_id;
            self::DbQuery($sql);

            self::incGameStateValue("numAllInPlayers", 1);
        } else {
            self::incGameStateValue("numBettingPlayers", 1); // An additional player has played for this betting round
        }

        // Increment stats
        self::incStat(1, "times_raised", $player_id);

        $this->gamestate->nextState('placeBet');
    }

    function raiseBy($tokens, $raise_value) {
        self::checkAction("placeBet");
        $player_id = self::getActivePlayerId();
        $player_name = self::getActivePlayerName();
        $current_round_stage = self::getGameStateValue("roundStage");
        $minimum_raise = self::getGameStateValue("minimumRaise");  
        $current_bet_level = self::getGameStateValue("currentBetLevel");

        $bet_computation = self::computeBet($tokens, $player_id);
        $additional_bet = $bet_computation["additional_bet"];
        $current_player_bet = $bet_computation["current_player_bet"];
        $total_player_bet = $bet_computation["total_player_bet"]; // Value of tokens that will be in the betting area after the current place bet action
        $diff_stock = $bet_computation["diff_stock"];
        $is_all_in = $bet_computation["is_all_in"];
        $sql = $bet_computation["token_update_sql"];

        $player_tokens = self::getPlayersTokens()[$player_id];

        $raise_amount = $raise_value;

        // Send notification for dialog bubble
        if ($current_bet_level == 0) {
            self::notifyAllPlayers("announceAction", '', array(
                'player_id' => $player_id,
                'message' => _("I bet ${raise_amount}")
            ));
        } else {
            self::notifyAllPlayers("announceAction", '', array(
                'player_id' => $player_id,
                'message' => _("I raise by ${raise_amount}")
            ));
        }

        if ($current_round_stage == 0) {
            throw new feException(_("You are not supposed to raise during the blinds phase"));
        } else {
            if ($raise_amount < $minimum_raise) {
                // The raise is lower than the minimum possible raise
                throw new BgaUserException(_("You need to raise by at least ${minimum_raise}. You currently raised by ${raise_amount}."));
            } else if ($raise_amount + ($current_bet_level - $current_player_bet) > $player_tokens["stock"]) {
                // The player does not have enough stock to raise by the specified amount
                throw new BgaUserException(_("You need don't have enough chips in stock to raise by ${raise_amount}"));
            }

            if ($total_player_bet == ($current_bet_level + $raise_amount)) {
                // The player has moved to the betting area an amount of tokens that corresponds to the raise he announced
                self::DbQuery($sql);
                if ($current_bet_level == 0) {
                    self::notifyAllPlayers("betPlaced", clienttranslate('${player_name} bets ${additional_bet}'), array(
                        'player_id' => $player_id,
                        'player_name' => $player_name,
                        'additional_bet' => $additional_bet,
                        'diff_stock' => $diff_stock,
                        'show_all' => FALSE
                    ));
                } else {
                    self::notifyAllPlayers("betPlaced", clienttranslate('${player_name} raises by ${additional_bet}'), array(
                        'player_id' => $player_id,
                        'player_name' => $player_name,
                        'additional_bet' => $additional_bet,
                        'diff_stock' => $diff_stock,
                        'show_all' => FALSE
                    ));
                }
            } else {
                // The player has not moved to the betting area an amount of tokens that corresponds to the raise he announced
                if ($additional_bet != 0) {
                    self::notifyPlayer($player_id, "betPlaced", clienttranslate('Incorrect bet placed. Moving back ${additional_bet} to your stock.'), array(
                        'player_id' => $player_id,
                        'additional_bet' => $additional_bet,
                        'diff_stock' => array_map(function($token_num) {return -$token_num;}, $diff_stock),
                        'show_all' => TRUE
                    ));
                }

                // The player has enough stock to call
                self::moveTokens("stock_${player_id}", "bet_${player_id}", $current_bet_level + $raise_amount - $current_player_bet);
                if ($current_bet_level == 0) {
                    self::notifyAllPlayers("raisePlaced", clienttranslate('${player_name} bets ${raise_amount}'), array(
                        'player_name' => $player_name,
                        'raise_amount' => $raise_amount
                    ));
                } else {
                    self::notifyAllPlayers("raisePlaced", clienttranslate('${player_name} raises by ${raise_amount}'), array(
                        'player_name' => $player_name,
                        'raise_amount' => $raise_amount
                    ));
                }
                $additional_bet = $raise_amount;
                $is_all_in = $player_tokens["stock"] == $raise_amount;
            }
            // Update current bet level
            self::incGameStateValue("currentBetLevel", $raise_amount);
            self::setGameStateValue("minimumRaise", $raise_amount);
        }

        // Increment the number of all in players if the player bet all his stock
        if ($is_all_in) {
            $sql = "UPDATE player SET is_all_in = 1 WHERE player_id = " . $player_id;
            self::DbQuery($sql);

            self::incGameStateValue("numAllInPlayers", 1);
        } else {
            self::incGameStateValue("numBettingPlayers", 1); // An additional player has played for this betting round
        }

        // Increment stats
        self::incStat(1, "times_raised", $player_id);

        $this->gamestate->nextState('placeBet');
    }

    function fold($player_id, $tokens) {
        self::checkAction("fold");
        $player_id = self::getActivePlayerId();

        // Send notification for dialog bubble
        self::notifyAllPlayers("announceAction", '', array(
            'player_id' => $player_id,
            'message' => clienttranslate('I fold')
        ));

        $bet_computation = self::computeBet($tokens, $player_id);
        $additional_bet = $bet_computation["additional_bet"];
        $diff_stock = $bet_computation["diff_stock"];

        if ($additional_bet != 0) {
            self::notifyPlayer($player_id, "betPlaced", clienttranslate('No chip should be bet or taken back for a fold action. Moving back ${additional_bet} to your stock.'), array(
                'player_id' => $player_id,
                'additional_bet' => $additional_bet,
                'diff_stock' => array_map(function($token_num) {return -$token_num;}, $diff_stock),
                'show_all' => TRUE
            ));
        }

        $sql = "UPDATE player SET is_fold = true WHERE player_id = '" . $player_id . "'";
        self::DbQuery($sql);

        $this->cards->moveAllCardsInLocation("hand", "discard", $player_id, $player_id);

        self::incGameStateValue("numFoldedPlayers", 1);

        self::notifyAllPlayers("fold", clienttranslate('${player_name} folds'), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName()
        ));

        // Increment stats
        self::incStat(1, "times_folded", $player_id);

        $this->gamestate->nextState('fold');
    }

    function allIn($tokens) {
        self::checkAction("placeBet");
        $player_id = self::getActivePlayerId();
        $player_name = self::getActivePlayerName();
        $small_blind_player = self::getGameStateValue("smallBlindPlayer");
        $current_round_stage = self::getGameStateValue("roundStage");
        $blind_value = self::getGameStateValue("smallBlindValue");
        $minimum_raise = self::getGameStateValue("minimumRaise");

        // Get current level of bet
        $current_bet_level = self::getGameStateValue("currentBetLevel");

        // Send notification for dialog bubble
        self::notifyAllPlayers("announceAction", '', array(
            'player_id' => $player_id,
            'message' => clienttranslate('I go all in')
        ));

        $bet_computation = self::computeBet($tokens, $player_id);
        $additional_bet = $bet_computation["additional_bet"];
        $current_player_bet = $bet_computation["current_player_bet"];
        $diff_stock = $bet_computation["diff_stock"];
        $total_player_bet = $bet_computation["total_player_bet"]; // Value of tokens that will be in the betting area after the current place bet action
        $sql = $bet_computation["token_update_sql"];

        $players_score = self::getCollectionFromDb("SELECT player_id, player_score FROM player");

        $player_tokens = self::getPlayersTokens()[$player_id];

        // Assess if the player is allowed to go all in and display relevant notifications for the different use cases
        if (($player_tokens["stock"] + $current_player_bet) <= $current_bet_level) {
            // Case of All in to call the bet
            if ($player_tokens["stock"] + $current_player_bet == $current_bet_level) {
                // The player has just enough chips to call the current bet level
                self::notifyAllPlayers("allIn", clienttranslate('${player_name} goes all in to call the current bet level by adding ${all_in_value} to her/his bet.'), array(
                    'player_name' => $player_name,
                    'all_in_value' => $player_tokens["stock"]
                ));
            } else {
                // The player does not have enough chips to call the current bet level
                self::notifyAllPlayers("allIn", clienttranslate('${player_name} does not have enough stock to place the big blind. She/he is all in with ${all_in_value} added to the bet.'), array(
                    'player_name' => $player_name,
                    'all_in_value' => $player_tokens["stock"]
                ));
            }
        } else {
            // Case of All in to raise the bet
            $raise_amount = ($player_tokens["stock"] + $current_player_bet) - $current_bet_level;
            
            if ($raise_amount < $minimum_raise) {
                // Not enough stock to raise => Forbidden action
                throw new BgaUserException(_("You cannot go all in because you need need to raise by at least ${minimum_raise}. You currently raised by ${raise_amount}."));
            } else {
                // Enough stock to raise

                self::notifyAllPlayers("allIn", clienttranslate('${player_name} goes all in to raise by ${raise_amount}'), array(
                    'player_name' => $player_name,
                    'raise_amount' => $raise_amount
                ));
                
                // Update current bet level
                self::setGameStateValue("currentBetLevel", $current_bet_level + $raise_amount);
                self::setGameStateValue("minimumRaise", $raise_amount);
            }
        }

        // Move player's chips if necessary
        if ($additional_bet == 0) {
            // The player has not moved any chip during this turn to the betting area before clicking the All in button
            // => Need to move all his chips to the betting area
            self::moveTokens("stock_${player_id}", "bet_${player_id}", $player_tokens["stock"]);
        } else {
            // The player has moved some chips during this turn to the betting area before clicking the All in button
            
            if ($additional_bet != $player_tokens["stock"]) {
                // The player has not moved ALL his chips to the betting area before clicking the All in button
                // => Need to move the rest

                // Move back the chips on the current player's client
                self::notifyPlayer($player_id, "betPlaced", '', array(
                    'player_id' => $player_id,
                    'diff_stock' => array_map(function($token_num) {return -$token_num;}, $diff_stock),
                    'show_all' => TRUE
                ));
                // Move all chips from the current player's stock to the betting area
                self::moveTokens("stock_${player_id}", "bet_${player_id}", $player_tokens["stock"]);
            } else {
                // The player has already moved ALL his chips to the betting area
                // => No additional chip move is required for the current player's client

                self::notifyAllPlayers("betPlaced", '', array(
                    'player_id' => $player_id,
                    'diff_stock' => $diff_stock,
                    'show_all' => FALSE
                ));
            }
        }

        // Increment the number of all in players
        $sql = "UPDATE player SET is_all_in = 1 WHERE player_id = " . $player_id;
        self::DbQuery($sql);

        // Increment stats
        self::incStat(1, "times_all_in", $player_id);

        self::incGameStateValue("numAllInPlayers", 1);

        $this->gamestate->nextState('placeBet');
    }

    function makeChange($tokens) {
        self::checkAction("makeChange");
        $player_id = self::getActivePlayerId();
        $player_name = self::getActivePlayerName();

        // Query current player's tokens stock
        $sql = "SELECT player_stock_token_white, player_stock_token_blue, player_stock_token_red, 
            player_stock_token_green, player_stock_token_black FROM player WHERE player_id = '" . $player_id . "'";
        $current_tokens = self::getObjectFromDB($sql);
        $diff_stock = array(
            "white" => 0,
            "blue" => 0,
            "red" => 0,
            "green" => 0,
            "black" => 0
        );

        // Update playe's stock with new number of tokens
        $colors = ["white", "blue", "red", "green", "black"];
        $valueGiven = 0;
        $valueReceived = 0;

        $sql = "UPDATE player SET ";
        foreach($tokens as $token_id => $token_number) {
            $color = $colors[(int)floor($token_id / 2)];
            $isGiven = $token_id % 2 == 0;
            if ($isGiven) {
                if ($token_number > 0) {
                    // Given token => decrement stock
                    if ($token_number > $current_tokens["player_stock_token_${color}"]) {
                        throw new feException(_("You cannot give ${token_number} ${color} chips because you only have " . $current_tokens["player_stock_token_${color}"]));
                    } else {
                        $sql .= "player_stock_token_${color} = " . ($current_tokens["player_stock_token_${color}"] - $token_number) . ", ";
                        $diff_stock[$color] = -1 * (int)$token_number;
                        $current_tokens["player_stock_token_${color}"] -= $token_number;
                        $valueGiven += $token_number * $this->token_values[$color];
                    }
                }
            } else {
                if ($token_number > 0) {
                    // Receive token => increment stock
                    $sql .= "player_stock_token_${color} = " . ($current_tokens["player_stock_token_${color}"] + $token_number) . ", ";
                    $diff_stock[$color] += (int)$token_number;
                    $current_tokens["player_stock_token_${color}"] += $token_number;
                    $valueReceived += $token_number * $this->token_values[$color];
                }
            }
        }
        // Removing last ';' if exists
        if (substr($sql, -2) == ', ') {
            $sql = substr($sql, 0, -2);
        }
        $sql .= " WHERE player_id = '" . $player_id. "'";
        if ($valueReceived != $valueGiven) {
            throw new feException(_("The value of chips given is different than the value of received tokens"));
        } else {
            self::DbQuery($sql);
            self::notifyAllPlayers("makeChange", clienttranslate('${player_name} makes some change'), array(
                'player_name' => $player_name,
                'player_id' => $player_id,
                'diff_stock' => $diff_stock
            ));
        }
    }

    function changeAutoblinds($player_id, $is_checked) {
        self::trace("Autoblinds for player ${player_id}: " . ($is_checked == 1 ? "enabled" : "disabled"));
        $sql = "UPDATE player SET wants_autoblinds = ${is_checked} WHERE player_id = ${player_id}";
        self::DbQuery($sql);
        self::notifyPlayer($player_id, "autoblindsChange", '', array());
    }

    function changeBetmode($player_id, $is_checked, $tokens) {
        self::trace("Manual bet for player ${player_id}: " . ($is_checked == 1 ? "enabled" : "disabled"));
        $sql = "UPDATE player SET wants_manualbet = ${is_checked} WHERE player_id = ${player_id}";
        self::DbQuery($sql);

        if (!$is_checked) {
            // Move back chips that were moved during the current player's turn
            $bet_computation = self::computeBet($tokens, $player_id);
            $additional_bet = $bet_computation["additional_bet"];
            $diff_stock = $bet_computation["diff_stock"];

            if ($additional_bet != 0) {
                self::notifyPlayer($player_id, "betPlaced", clienttranslate('You chose to not bet by clicking on chips. The chips you moved during the current turn are returned to their origin.'), array(
                    'player_id' => $player_id,
                    'additional_bet' => $additional_bet,
                    'diff_stock' => array_map(function($token_num) {return -$token_num;}, $diff_stock),
                    'show_all' => TRUE
                ));
            }
        }

        self::notifyPlayer($player_id, "betmodeChange", '', array());
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

    function argSmallBlind() {
        return array('smallblind' => self::getGameStateValue("smallBlindValue"));
    }

    function argBigBlind() {
        return array('bigblind' => 2 * self::getGameStateValue("smallBlindValue"));
    }

    function argPlayerTurn() {
        $round_stage = self::getGameStateValue("roundStage");
        $player_id = self::getActivePlayerId();
        $current_bet_level = self::getGameStateValue("currentBetLevel");
        $minimum_raise = self::getGameStateValue("minimumRaise");
        $small_blind = self::getGameStateValue("smallBlindValue");
        $big_blind = 2 * $small_blind;
        $player_tokens = self::getPlayersTokens()[$player_id];

        $possible_actions = array();

        // Check
        if (self::getGameStateValue("roundStage") == 1) {
            if ($current_bet_level == $big_blind && $player_tokens["bet"] == $big_blind) {
                $possible_actions["check"] = TRUE;
            } else {
                $possible_actions["check"] = FALSE;
            }
        } else {
            if ($current_bet_level == 0 && $player_tokens["bet"] == 0) {
                $possible_actions["check"] = TRUE;
            } else {
                $possible_actions["check"] = FALSE;
            }
        }

        // Call
        if ($player_tokens["bet"] < $current_bet_level) {
            $possible_actions["call"] = TRUE;
        } else {
            $possible_actions["call"] = FALSE;
        }

        // Raise
        if (0 < $current_bet_level && $player_tokens["bet"] <= $current_bet_level) {
            $possible_actions["raise"] = TRUE;
        } else {
            $possible_actions["raise"] = FALSE;
        }

        // Buttons to raise by a fixed amount
        if ($possible_actions["raise"]) {
            $first_raise_amount = $minimum_raise;
            $second_raise_amount = 2 * $minimum_raise;
            $third_raise_amount = 5 * $minimum_raise;
            // Raise by (first value)
            if (($player_tokens["stock"] - $current_bet_level) >= $first_raise_amount) {
                $possible_actions["raise_by_first"] = $first_raise_amount;
            }

            // Raise by (second value)
            if (($player_tokens["stock"] - $current_bet_level) >= $second_raise_amount) {
                $possible_actions["raise_by_second"] = $second_raise_amount;
            }

            // Raise by (third value)
            if (($player_tokens["stock"] - $current_bet_level) >= $third_raise_amount) {
                $possible_actions["raise_by_third"] = $third_raise_amount;
            }
        }

        // Bet
        if ($player_tokens["bet"] == 0 && $current_bet_level == 0) {
            $possible_actions["bet"] = TRUE;
        } else {
            $possible_actions["bet"] = FALSE;
        }

        // Buttons to bet by a fixed amount
        if ($possible_actions["bet"]) {
            $first_raise_amount = $minimum_raise;
            $second_raise_amount = 2 * $minimum_raise;
            $third_raise_amount = 5 * $minimum_raise;
            // Raise by (first value)
            if (($player_tokens["stock"] - $current_bet_level) >= $first_raise_amount) {
                $possible_actions["bet_first"] = $first_raise_amount;
            }

            // Raise by (second value)
            if (($player_tokens["stock"] - $current_bet_level) >= $second_raise_amount) {
                $possible_actions["bet_second"] = $second_raise_amount;
            }

            // Raise by (third value)
            if (($player_tokens["stock"] - $current_bet_level) >= $third_raise_amount) {
                $possible_actions["bet_third"] = $third_raise_amount;
            }
        }

        // All in
        if (($player_tokens["stock"] + $player_tokens["bet"]) <= $current_bet_level || ($player_tokens["stock"] + $player_tokens["bet"] - $current_bet_level) >= $minimum_raise) {
            $possible_actions["all_in"] = TRUE;
        } else {
            $possible_actions["all_in"] = FALSE;
        }

        // Fold
        $possible_actions["fold"] = TRUE;
        
        return $possible_actions;
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
        self::incGameStateValue("roundNumber", 1);
        self::setGameStateValue("roundStage", 0);
        self::setGameStateValue("numFoldedPlayers", 0);
        self::setGameStateValue("numAllInPlayers", 0);
        self::setGameStateValue("areHandsRevealed", 0);

        // Unfold all players and reset all in flag
        $sql = "UPDATE player SET is_fold = 0, is_all_in = 0";
        self::DbQuery($sql);

        // Deal two cards to each player and increase turn number player stat
        $players = self::getCollectionFromDb("SELECT player_id, player_eliminated, wants_autoblinds FROM player");
        foreach ($players as $player_id => $player) {
            if (!$player["player_eliminated"]) {
                $cards = $this->cards->pickCards(2, 'deck', $player_id);
                // Increment stats
                self::incStat(1, "turns_number", $player_id);
            }
        }

        // Update blinds if necessary
        // small blind = initial small blind * 2 ^ (max(#eliminated players, floor(#turns / max(10, 2*#players))))
        $num_turns = self::getGameStateValue("roundNumber");
        $num_eliminated_players = self::getGameStateValue("numEliminatedPlayers");
        $current_small_blind = self::getGameStateValue("smallBlindValue");
        $new_small_blind = 1 * pow(2, max($num_eliminated_players, floor($num_turns / max(10, 2*count($players)))));
        if ($new_small_blind != $current_small_blind) {
            self::notifyAllPlayers("increaseBlinds", clienttranslate('The blinds are increased (small blind: ${small_blind}, big blind: ${big_blind})'), array(
                'small_blind' => $new_small_blind,
                'big_blind' => 2*$new_small_blind
            ));

            self::setGameStateValue("smallBlindValue", $new_small_blind);
        }

        self::notifyAllPlayers("newHand", clienttranslate('Hand number ${num_turns} starts. Two cards are dealt to each player.'), array(
            'num_turns' => $num_turns
        ));

        // Send individual notification to each player hiding other players cards
        foreach ($players as $player_id => $player) {
            $hands_cards = $this->cards->getCardsInLocation('hand');
            $player_hand = $hands_cards;
            foreach ($player_hand as $card_id => $card) {
                if ($card["location_arg"] != $player_id) {
                    // Hide the actual value of other player cards to avoid JS interception cheat
                    $player_hand[$card_id]["type"] = 0;
                    $player_hand[$card_id]["type_arg"] = 0;
                    $player_hand[$card_id]["id"] = 0;
                }
            }
            self::notifyPlayer($player_id, "dealCardsPlayer", '', array(
                'players' => $players,
                'hands' => array_values($player_hand)
            ));
        }

        $player_id = self::getActivePlayerId();
        if ($players[$player_id]["wants_autoblinds"]) {
            // Build tokens array as if it was sent by the frontend
            $sql = "SELECT player_id, player_stock_token_white, player_stock_token_blue, player_stock_token_red, 
                player_stock_token_green, player_stock_token_black FROM player WHERE player_id = ${player_id}";
            $player_stock = self::getCollectionFromDb($sql)[$player_id];
            $tokens = array(
                $player_stock['player_stock_token_white'], 0, 
                $player_stock['player_stock_token_blue'], 0,
                $player_stock['player_stock_token_red'], 0,
                $player_stock['player_stock_token_green'], 0,
                $player_stock['player_stock_token_black'], 0,
            );
            self::placeSmallBlind($tokens, true);
        } else {
            $this->gamestate->nextState("manualSmallBlind");
        }
    }

    function stToBigBlind() {
        $players = self::getCollectionFromDb("SELECT player_id, is_fold, is_all_in, player_eliminated, wants_autoblinds FROM player");

        // Skip player if he has folded or is already all in
        $player_id = self::getPlayerAfter(self::getActivePlayerId());
        while (($players[$player_id]["is_fold"] || $players[$player_id]["is_all_in"] || $players[$player_id]["player_eliminated"])) {
            $player_id = self::getPlayerAfter($player_id);
        }
        $this->gamestate->changeActivePlayer($player_id);

        if ($players[$player_id]["wants_autoblinds"]) {
            // Build tokens array as if it was sent by the frontend
            $sql = "SELECT player_id, player_stock_token_white, player_stock_token_blue, player_stock_token_red, 
                player_stock_token_green, player_stock_token_black FROM player WHERE player_id = ${player_id}";
            $player_stock = self::getCollectionFromDb($sql)[$player_id];
            $tokens = array(
                $player_stock['player_stock_token_white'], 0, 
                $player_stock['player_stock_token_blue'], 0,
                $player_stock['player_stock_token_red'], 0,
                $player_stock['player_stock_token_green'], 0,
                $player_stock['player_stock_token_black'], 0,
            );
            self::placeBigBlind($tokens, true);
        } else {
            self::notifyAllPlayers("changeActivePlayer", clienttranslate('${player_name} is now the active player'), array(
                'player_name' => self::getActivePlayerName(),
                'player_id' => $player_id
            ));
            self::giveExtraTime($player_id);
            $this->gamestate->nextState("manualBigBlind");
        }
    }

    function stToBetRound() {
        $players = self::getCollectionFromDb("SELECT player_id, is_fold, is_all_in, player_eliminated FROM player");

        self::setGameStateValue("roundStage", 1);
        // Skip player if he has folded or is already all in
        $player_id = self::getPlayerAfter(self::getActivePlayerId());
        while (($players[$player_id]["is_fold"] || $players[$player_id]["is_all_in"] || $players[$player_id]["player_eliminated"])) {
            $player_id = self::getPlayerAfter($player_id);
        }
        $this->gamestate->changeActivePlayer($player_id);
        self::notifyAllPlayers("changeActivePlayer", clienttranslate('${player_name} is now the active player'), array(
            'player_name' => self::getActivePlayerName(),
            'player_id' => $player_id
        ));
        self::giveExtraTime($player_id);
        $this->gamestate->nextState("startRound");
    }

    function stNewBet() {
        // Only set the current bet level to 0 if it is not the pre-flop stage. 
        // In this case the current bet level is the big blind value (or all in value of the blind player)
        if (self::getGameStateValue("roundStage") != 1) {
            self::setGameStateValue("currentBetLevel", 0);
        }
        self::setGameStateValue("numBettingPlayers", 0);
        self::setGameStateValue("minimumRaise", 2 * self::getGameStateValue("smallBlindValue"));

        // Check if all players but one are all in or folded => Immediately end bet round
        $num_players = self::getUniqueValueFromDB("SELECT COUNT(player_id) FROM player");
        $num_folded_players = self::getGameStateValue("numFoldedPlayers");
        $num_all_in_players = self::getGameStateValue("numAllInPlayers");
        $num_eliminated_players = self::getGameStateValue("numEliminatedPlayers");
        $current_bet_level = self::getGameStateValue("currentBetLevel");
        $player_id = self::getActivePlayerId();
        $current_player_bet = self::getPlayersTokens()[$player_id]["bet"];
        if (($num_folded_players + $num_all_in_players + $num_eliminated_players) >= ($num_players - 1)) {
            if (self::getGameStateValue("roundStage") == 1 && $current_bet_level > $current_player_bet) {
                $this->gamestate->nextState("startRound");
            } else {
                $this->gamestate->nextState("allAllIn");
            }
        } else {
            $this->gamestate->nextState("startRound");
        }
    }

    function stNextPlayer() {
        // Check which players are folded
        $sql = "SELECT player_id, is_fold, is_all_in, player_eliminated, player_bet_token_white, player_bet_token_blue, player_bet_token_red, 
            player_bet_token_green, player_bet_token_black FROM player";
        $players = self::getCollectionFromDb($sql);
        $num_folded_players = self::getGameStateValue("numFoldedPlayers");
        $num_all_in_players = self::getGameStateValue("numAllInPlayers");
        $num_eliminated_players = self::getGameStateValue("numEliminatedPlayers");
        $num_betting_players = self::getGameStateValue("numBettingPlayers");        

        // Check if all players except one have folded
        if (($num_folded_players + $num_eliminated_players) >= (count($players) - 1)) {
            $this->gamestate->nextState("endBetRound");
        } else {
            // Check if all players have bet the same amount
            $colors = ["white", "blue", "red", "green", "black"];

            $is_same_bet = TRUE;
            $players_bet = self::getPlayersTokens();
            $current_amount = array_reduce($players_bet, function($max, $player) {
                $max = $player["bet"] > $max ? $player["bet"] : $max;
                return $max;
            });
            foreach ($players as $player_id => $player) {
                if (!$player["is_fold"] && !$player["player_eliminated"]) {
                    if ($players_bet[$player_id]["bet"] != $current_amount) {
                        if ($player["is_all_in"]) {
                            self::trace($player_id . " has bet less than the current amount (${current_amount}) but is all in.");
                        } else {
                            self::trace($player_id . " has bet less than the current amount (${current_amount}). Betting round continues.");
                            $is_same_bet = FALSE;
                            break;
                        }
                    }
                }
            }
            if ($is_same_bet && $num_betting_players >= (count($players) - $num_folded_players - $num_eliminated_players - $num_all_in_players)) { // Ensure all players have had the possibility to bet, fold or check for this betting round
                self::notifyAllPlayers("nextBetRound", clienttranslate('Every non-folded player have bet the same amount or are all in. This ends the betting round.'), array());
                $this->gamestate->nextState("endBetRound");
            } else {
                // Skip player if he has folded or is already all in
                $player_id = self::getPlayerAfter(self::getActivePlayerId());
                while (($players[$player_id]["is_fold"] || $players[$player_id]["is_all_in"] || $players[$player_id]["player_eliminated"]) && ($num_folded_players + $num_all_in_players + $num_eliminated_players) < count($players)) {
                    $player_id = self::getPlayerAfter($player_id);
                }
                $this->gamestate->changeActivePlayer($player_id);
                self::notifyAllPlayers("changeActivePlayer", clienttranslate('${player_name} is now the active player'), array(
                    'player_name' => self::getActivePlayerName(),
                    'player_id' => $player_id
                ));
                self::giveExtraTime($player_id);
                $this->gamestate->nextState("nextPlayer");
            }
        }        
    }

    function stEndBet() {
        $round_stage = self::getGameStateValue("roundStage");
        $num_folded_players = self::getGameStateValue("numFoldedPlayers");
        $num_all_in_players = self::getGameStateValue("numAllInPlayers");
        $num_eliminated_players = self::getGameStateValue("numEliminatedPlayers");

        // Place all tokens in the betting area to the pot
        $sql = "SELECT player_id, player_bet_token_white, player_bet_token_blue, player_bet_token_red, 
            player_bet_token_green, player_bet_token_black, is_fold, is_all_in, player_eliminated FROM player";
        $current_players_bet = self::getCollectionFromDb($sql);

        $sql = "SELECT token_color, token_number FROM token";
        $current_pot = self::getCollectionFromDb($sql, true);
        $end_pot = array();

        $sql_pot = "";
        $sql_bet = "UPDATE player SET ";
        $num_colors_checked = 0;
        $additional_pot = 0;
        foreach($current_pot as $color => $token_number) {
            $added_tokens = 0;
            foreach($current_players_bet as $player_id => $bet_tokens) {
                $added_tokens += $bet_tokens["player_bet_token_".$color];
            }
            $end_pot[$color] = $added_tokens + $token_number; // Number of tokens in the pot after movement
            $additional_pot += $this->token_values[$color] * $added_tokens;
            $sql_pot = "UPDATE token SET token_number = " . $end_pot[$color] . " WHERE token_color = '" . $color . "'";
            self::DbQuery($sql_pot);
            $sql_bet .= "player_bet_token_" . $color . " = 0";
            // Don't add a comma for the last item
            if ($num_colors_checked < (count($current_pot) - 1)) {
                $sql_bet .= ", ";
            }
            $num_colors_checked++;
        }
        self::DbQuery($sql_bet);

        // Only move bets to pot if there are any bet (otherwise it would pause during 3 seconds for nothing)
        if ($additional_pot > 0) {
            self::notifyAllPlayers("moveBetToPot", clienttranslate('${additional_pot} is added to the pot'), array(
                'additional_pot' => $additional_pot,
                'end_pot' => $end_pot
            ));
        }

        if ($round_stage >= 4) {
            // All cards already shown
            $this->gamestate->nextState("endHand");
        } else if (($num_folded_players + $num_eliminated_players) >= (count($current_players_bet) - 1)) {
            // Only one player still not folded
            self::notifyAllPlayers("allFolded", clienttranslate('There is only one player that has not folded. This ends the hand.'), array());
            $this->gamestate->nextState("endHand");
        } else {
            // More than one player still not folded

            // Showdown in case all players are all in
            if (self::getGameStateValue("areHandsRevealed") == 0 && $num_all_in_players >= (count($current_players_bet) - $num_folded_players - $num_eliminated_players - 1)) {
                $cards_in_hand = $this->cards->getCardsInLocation("hand");
                $sql = "SELECT player_id, player_name, player_color, is_fold, is_all_in, player_eliminated, player_score FROM player WHERE is_fold = 0 AND player_eliminated = 0";
                $non_folded_players = self::getCollectionFromDb($sql);
                // Reveal players hands
                self::notifyAllPlayers("revealHands", clienttranslate('There cannot be any further betting round. All non-folded players reveal their hand.'), array(
                    'players' => array_values($non_folded_players),
                    'hands' => array_values($cards_in_hand)
                ));

                self::setGameStateValue("areHandsRevealed", 1);
            }

            $round_stage++;
            switch($round_stage) {
                case 2:
                    // Burn a card before drawing the flop cards
                    $revealed_card = "flop";
                    $this->cards->pickCardForLocation("deck", "discard");
                    $this->cards->pickCardsForLocation(3, "deck", "flop");
                    $new_cards = $this->cards->getCardsInLocation("flop");
                    self::notifyAllPlayers("revealNextCard", clienttranslate('The ${revealed_card} is revealed ${card1} ${card2} ${card3}'), array(
                        'i18n' => array('revealed_card'),
                        'revealed_card' => $revealed_card,
                        'card1' => array_values($new_cards)[0],
                        'card2' => array_values($new_cards)[1],
                        'card3' => array_values($new_cards)[2],
                        'cards' => $new_cards
                    ));
                    break;
                case 3:
                    // Burn a card before drawing the turn card
                    $revealed_card = "turn";
                    $this->cards->pickCardForLocation("deck", "discard");
                    $this->cards->pickCardForLocation("deck", "turn");
                    $new_cards = $this->cards->getCardsInLocation("turn");
                    self::notifyAllPlayers("revealNextCard", clienttranslate('The ${revealed_card} is revealed ${card1}'), array(
                        'i18n' => array('revealed_card'),
                        'revealed_card' => $revealed_card,
                        'card1' => array_values($new_cards)[0],
                        'cards' => $new_cards
                    ));
                    break;
                case 4:
                    // Burn a card before drawing the river card
                    $revealed_card = "river";
                    $this->cards->pickCardForLocation("deck", "discard");
                    $this->cards->pickCardForLocation("deck", "river");
                    $new_cards = $this->cards->getCardsInLocation("river");
                    self::notifyAllPlayers("revealNextCard", clienttranslate('The ${revealed_card} is revealed ${card1}'), array(
                        'i18n' => array('revealed_card'),
                        'revealed_card' => $revealed_card,
                        'card1' => array_values($new_cards)[0],
                        'cards' => $new_cards
                    ));
                    break;
            }
            self::setGameStateValue("roundStage", $round_stage);

            // Set the small blind player active at the start of each betting round
            if (count($current_players_bet) - self::getGameStateValue("numEliminatedPlayers") <= 2) {
                // In headsup the first player to talk after the flop is the big blind
                $player_id = self::getPlayerAfter(self::getGameStateValue("smallBlindPlayer"));
                if (($num_folded_players + $num_all_in_players + $num_eliminated_players) <= (count($current_players_bet) - 1)) {
                    while (($current_players_bet[$player_id]["is_fold"] || $current_players_bet[$player_id]["is_all_in"] || $current_players_bet[$player_id]["player_eliminated"])) {
                        // Skip player if he has folded or is already all in or eliminated (if he quited during the round)
                        $player_id = self::getPlayerAfter($player_id);
                    }
                }
            } else {
                $player_id = self::getGameStateValue("smallBlindPlayer");
                if (($num_folded_players + $num_all_in_players + $num_eliminated_players) <= (count($current_players_bet) - 1)) {
                    while (($current_players_bet[$player_id]["is_fold"] || $current_players_bet[$player_id]["is_all_in"] || $current_players_bet[$player_id]["player_eliminated"])) {
                        // Skip player if he has folded or is already all in or eliminated (if he quited during the round)
                        $player_id = self::getPlayerAfter($player_id);
                    }
                }
            }
            $this->gamestate->changeActivePlayer($player_id);
            self::notifyAllPlayers("changeActivePlayer", clienttranslate('${player_name} is now the active player.'), array(
                'player_name' => self::getActivePlayerName(),
                'player_id' => $player_id
            ));
            self::giveExtraTime($player_id);
            $this->gamestate->nextState("nextBetRound");
        }
    }

    function stEndHand() {

        $sql = "SELECT player_id, player_name, player_color, is_fold, is_all_in, player_eliminated, player_score FROM player";
        $players = self::getCollectionFromDb($sql);
        $non_folded_players = array_filter($players, function($player) {return !$player["is_fold"] && !$player["player_eliminated"];});
        $cards_in_hand = $this->cards->getCardsInLocation("hand");
        $sql = "SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM card WHERE card_location IN ('flop', 'turn', 'river')";
        $cards_on_table = self::getCollectionFromDb($sql);

        self::incStat(1, "turns_number");

        if (count($non_folded_players) > 1) {
            // At least two players still not folded
            foreach($non_folded_players as $player_id => $player) {
                // Combine cards from hand and from the table to make a 7 cards hand
                $player_hand = array_filter($cards_in_hand, function($card) use ($player_id) {return $card["location_arg"] == $player_id;});
                $player_hand = array_merge($player_hand, $cards_on_table);
                // Identify best 5 cards combo in the 7 cards
                $players_best_combo[$player_id] = $this->findComboIn7CardsHand($player_hand);
            }

            // Sort hands descending
            uasort($players_best_combo, array($this,'compareHands'));

            // Rank players according to their combo
            $sorted_player_ids = array_keys($players_best_combo);
            $player_1 = 0;
            $player_2 = 1;
            $rank = 1;
            $players_rank = array();
            while ($player_1 < count($players_best_combo)) {
                $players_rank[$sorted_player_ids[$player_1]] = $rank;
                while ($player_2 < count($players_best_combo) && $this->compareHands($players_best_combo[$sorted_player_ids[$player_1]], $players_best_combo[$sorted_player_ids[$player_2]]) == 0) {
                    $players_rank[$sorted_player_ids[$player_2]] = $rank;
                    $player_2++;
                }
                $rank++;
                $player_1 = $player_2;
                $player_2++;
            }
            self::dump("Players rank:", $players_rank);

            // Reveal players hands
            if (self::getGameStateValue("areHandsRevealed") == 0) {
                self::notifyAllPlayers("revealHands", clienttranslate('The non-folded players reveal their hands'), array(
                    'players' => array_values($non_folded_players),
                    'hands' => array_values($cards_in_hand)
                ));
            }

            // Announce the combo of each player
            foreach($non_folded_players as $player_id => $player) {
                $combo_value = $players_best_combo[$player_id]["comboValue"];
                $player_hand = $players_best_combo[$player_id]["hand"];
                switch($players_best_combo[$player_id]["comboId"]) {
                    case 0:
                        $combo_name = "nothing (Top card: " . $this->values_label[$combo_value + 2] . ")";
                        self::incStat(1, "high_cards", $player_id);
                        break;
                    case 1:
                        $combo_name = "a pair of " . $this->values_label[$combo_value + 2] . "s";
                        self::incStat(1, "pairs", $player_id);
                        break;
                    case 2:
                        $combo_name = "two pairs (" . $this->values_label[(int)floor($combo_value / 100) + 2] . " and " . $this->values_label[$combo_value % 100 + 2] . ")";
                        self::incStat(1, "two_pairs", $player_id);
                        break;
                    case 3:
                        $combo_name = "a three of a kind of " . $this->values_label[$combo_value + 2] . "s";
                        self::incStat(1, "three_of_a_kinds", $player_id);
                        break;
                    case 4:
                        $combo_name = "a straight (Top card: " . $this->values_label[$combo_value + 2] . ")";
                        self::incStat(1, "straights", $player_id);
                        break;
                    case 5:
                        $combo_name = "a flush (Top value: " . $this->values_label[$combo_value + 2] . ")";
                        self::incStat(1, "flushes", $player_id);
                        break;
                    case 6:
                        $combo_name = "a full house (" . $this->values_label[(int)floor($combo_value / 100) + 2] . "s" . " over " . $this->values_label[$combo_value % 100 + 2] . "s" . ")";
                        self::incStat(1, "full_houses", $player_id);
                        break;
                    case 7:
                        $combo_name = "a four of a kind of " . $this->values_label[$combo_value + 2] . "s";
                        self::incStat(1, "four_of_a_kinds", $player_id);
                        break;
                    case 8:
                        $combo_name = "a straight flush (Top card: " . $this->values_label[$combo_value + 2] . ")";
                        self::incStat(1, "straight_flushes", $player_id);
                        break;
                }
                $players_best_combo[$player_id]["comboName"] = $combo_name;
                self::notifyAllPlayers("announceCombo", clienttranslate('${player_name} has ${combo_name} with ${card1} ${card2} ${card3} ${card4} ${card5}'), array(
                    'i18n' => array('combo_name'),
                    'player_name' => $player["player_name"],
                    'combo_name' => $combo_name,
                    'card1' => $player_hand[0],
                    'card2' => $player_hand[1],
                    'card3' => $player_hand[2],
                    'card4' => $player_hand[3],
                    'card5' => $player_hand[4],
                    'player_id' => $player_id,
                    'player_color' => $player["player_color"],
                    'player_best_combo' => $players_best_combo[$player_id]
                ));
            }
        }

        $players_tokens_value = self::getPlayersTokens();
        $players_bet = array_map(function($player) use($players_tokens_value) {return $player["player_score"] - $players_tokens_value[$player["player_id"]]["stock"];}, $players);
        // Deal with cases where there may be secondary pots
        // Identify different pots
        $non_folded_players_bet = array_filter($players_bet, function($player_id) use($players) {
            return $players[$player_id]["is_fold"] != 1 && $players[$player_id]["player_eliminated"] != 1;
        }, ARRAY_FILTER_USE_KEY);
        $pots = array_reduce($non_folded_players_bet, function($pots, $player_bet) {
            if (!in_array($player_bet, $pots)) {
                $pots[] = $player_bet;
            }
            return $pots;
        }, array());
        asort($pots);

        if (count($non_folded_players) > 1) {
            $pot_number = 0;
            foreach ($pots as $pot_id => $pot) {
                $pot_level = $pots[$pot_id]; // Not using $pot because the value of pots is updated at the end of each loop
                if ($pot_number == 0) {
                    $pot_name = "primary pot";
                } else if ($pot_number == 1) {
                    $pot_name = "secondary pot";
                } else {
                    $pot_name = "next side pot";
                }

                // Only players who have bet as much as the pot level can have gains from that pot
                $players_in_pot = array_filter($players_rank, function($player_id) use($non_folded_players_bet, $pot_level) {
                    return $non_folded_players_bet[$player_id] >= $pot_level;
                }, ARRAY_FILTER_USE_KEY);

                // Pot value is the pot level times the number of players in that pot
                // + 
                $folded_players_bet = array_filter($players_bet, function($player_id) use($players) {
                    return $players[$player_id]["is_fold"] == 1;
                }, ARRAY_FILTER_USE_KEY);
                $pot_value = $pot_level * count($players_in_pot);
                foreach ($folded_players_bet as $folded_player_id => $folded_player_bet) {
                    $pot_value += min($folded_player_bet, $pot_level);
                    $players_bet[$folded_player_id] -= min($folded_player_bet, $pot_level);
                }

                // Deal with split pot cases
                // Get players with the same hand value as the winner
                $same_rank_players = array_filter($players_in_pot, function($player_rank) use($players_in_pot) {return $player_rank == min($players_in_pot);});

                if (count($same_rank_players) > 1) {
                    // Create string with winner names concatenated
                    $split_player_names = "";
                    $i = 0;
                    foreach ($same_rank_players as $player_id => $player) {
                        $split_player_names .= $players[$player_id]["player_name"];
                        if ($i < count($same_rank_players) - 2) {
                            $split_player_names .= ", ";
                        } else if ($i == count($same_rank_players) - 2) {
                            $split_player_names .= clienttranslate(" and ");
                        }
                        $i++;
                    }

                    $shares_or_wins = "shares";

                    self::notifyAllPlayers("splitPot", clienttranslate('${split_player_names} have exactly the same winning hand. They share the ${pot_name} and get ${pot_share} each.'), array(
                        'i18n' => array('pot_name'),
                        'split_player_names' => $split_player_names,
                        'pot_name' => $pot_name,
                        'pot_share' => (int)floor($pot_value / count($same_rank_players))
                    ));
                } else {
                    $shares_or_wins = "wins";
                }

                // Calculate the share of each winner
                $gain_remainder = 0;
                foreach ($same_rank_players as $winner_id => $player) {
                    // Deal with side pots => the winner keeps his bet + get up to his from each other players with a lower hand than him
                    $winner_gain = (int)floor($pot_value / count($same_rank_players));
                    $gain_remainder += $pot_value / count($same_rank_players) - floor($pot_value / count($same_rank_players));

                    // Announce kicker in case several players have the same best main combo
                    $winner_best_combo = $players_best_combo[$winner_id];
                    $same_combo_players = array_filter($players_best_combo, function($player_best_combo) use($winner_best_combo) {
                        return $player_best_combo["comboId"] == $winner_best_combo["comboId"] && 
                                $player_best_combo["comboValue"] == $winner_best_combo["comboValue"] && 
                                $player_best_combo["kickerValue"] != $winner_best_combo["kickerValue"];
                    });
                    $same_combo_players[$winner_id] = $winner_best_combo;
                    if (count($same_combo_players) > 1) {
                        // Several players have the same combo
                        $same_combo_player_names = "";
                        $i = 0;
                        // Build list of player names with the same combo
                        foreach ($same_combo_players as $player_id => $player) {
                            $same_combo_player_names .= $players[$player_id]["player_name"];
                            if ($i < count($same_combo_players) - 2) {
                                $same_combo_player_names .= ", ";
                            } else if ($i == count($same_combo_players) - 2) {
                                $same_combo_player_names .= clienttranslate(" and ");
                            }
                            $i++;
                        }
                        self::notifyAllPlayers("announceWinner", clienttranslate('${same_combo_player_names} both have ${combo_name} but ${winner_name} has a better kicker. He/she ${shares_or_wins} the ${pot_name} and gets ${winner_gain}.'), array(
                            'i18n' => array('pot_name', 'combo_name', 'shares_or_wins'),
                            'same_combo_player_names' => $same_combo_player_names,
                            'winner_name' => $players[$winner_id]["player_name"],
                            'shares_or_wins' => $shares_or_wins,
                            'pot_name' => $pot_name,
                            'combo_name' => $players_best_combo[$winner_id]["comboName"],
                            'winner_gain' => $winner_gain,
                            'winner_id' => $winner_id,
                            'winner_color' => $players[$winner_id]["player_color"],
                            'winner_best_combo' => $players_best_combo[$winner_id]
                        ));
                    } else {
                        self::notifyAllPlayers("announceWinner", clienttranslate('${winner_name} ${shares_or_wins} the ${pot_name} with ${combo_name} and gets ${winner_gain}'), array(
                            'i18n' => array('pot_name', 'combo_name', 'shares_or_wins'),
                            'winner_name' => $players[$winner_id]["player_name"],
                            'shares_or_wins' => $shares_or_wins,
                            'pot_name' => $pot_name,
                            'combo_name' => $players_best_combo[$winner_id]["comboName"],
                            'winner_gain' => $winner_gain,
                            'winner_id' => $winner_id,
                            'winner_color' => $players[$winner_id]["player_color"],
                            'winner_best_combo' => $players_best_combo[$winner_id]
                        ));
                    }

                    self::incStat(1, "hands_won", $winner_id);

                    self::moveTokens("pot", "stock_${winner_id}", $winner_gain);
                }

                // If the pot cannot be evenly split, the remainder goes to the player in the earliest position from the Dealer
                $gain_remainder = (int)$gain_remainder;
                if ($gain_remainder > 0) {
                    $earliest_player_id = self::getGameStateValue("smallBlindPlayer");
                    while (!array_key_exists($earliest_player_id, $same_rank_players)) {
                        $earliest_player_id = self::getPlayerAfter($earliest_player_id);
                    }

                    self::notifyAllPlayers("splitPot", clienttranslate('The ${pot_name} cannot be split evenly. ${earliest_player_name} gets the remaining ${gain_remainder} as he is in the earliest position.'), array(
                        'i18n' => array('pot_name'),
                        'pot_name' => $pot_name,
                        'earliest_player_name' => $players[$earliest_player_id]["player_name"],
                        'gain_remainder' => $gain_remainder
                    ));

                    self::moveTokens("pot", "stock_${earliest_player_id}", $gain_remainder);
                }

                // Substract pot level from each player's bet level
                foreach ($players_in_pot as $player_id => $player) {
                    $non_folded_players_bet[$player_id] -= $pot_level;
                }

                // Substract pot level from each other pots
                foreach ($pots as $pot_id => $pot) {
                    $pots[$pot_id] -= $pot_level;
                }

                $pot_number++;
            }
        } else {
            // Only one non-folded player => he gets the whole pot
            $total_pot = array_reduce($players_bet, function($sum, $player_bet) {return $sum += $player_bet;});
            $winner_id = array_keys($non_folded_players)[0];
            self::notifyAllPlayers("allFolded", clienttranslate('${winner_name} wins the round because all other players have folded'), array(
                'winner_name' => $players[$winner_id]["player_name"]
            ));

            
            self::incStat(1, "hands_won", $winner_id);

            self::moveTokens("pot", "stock_${winner_id}", $total_pot);
        }

        // Update players scores
        $players_tokens_value = self::getPlayersTokens();
        foreach ($players_tokens_value as $player_id => $player) {
            $sql = "UPDATE player SET player_score = " . $player["stock"] . " WHERE player_id = " . $player_id;
            self::DbQuery($sql);

            if ($player["stock"] == 0 && !$players[$player_id]["player_eliminated"]) {
                self::eliminatePlayer($player_id);
                // The earlier the player is eliminated the worse he is in ranking
                $sql = "UPDATE player SET player_score_aux = " . self::getGameStateValue("roundNumber") . " WHERE player_id = " . $player_id;
                self::DbQuery($sql);
                self::incGameStateValue("numEliminatedPlayers", 1);
                self::notifyAllPlayers("eliminatePlayer", '', array(
                    'eliminated_player' => $player_id
                ));
            }

            if ($player["stock"] != 0) {
                // Increment stats
                self::incStat(1, "turns_number", $player_id);
            }
        }

        self::notifyAllPlayers("updateScores", clienttranslate('Scores are updated with the new player chip stocks'), array(
            'players_tokens_value' => $players_tokens_value
        ));

        // If there is only one player left, the game ends
        $num_remaining_players = count(array_filter($players_tokens_value, function($player) {return $player["stock"] > 0;}));
        if ($num_remaining_players <= 1) {
            $this->gamestate->nextState("endGame");
        } else {   
            // Put all cards back to the deck
            $this->cards->moveAllCardsInLocation("hand", "deck");
            $this->cards->moveAllCardsInLocation("discard", "deck");
            $this->cards->moveAllCardsInLocation("flop", "deck");
            $this->cards->moveAllCardsInLocation("turn", "deck");
            $this->cards->moveAllCardsInLocation("river", "deck");
    
            // Deal with rounds-limited gamemodes
            if ($this->getGameStateValue('gameEndVariant') == 2) {
                switch($this->getGameStateValue('handsNumberLimit')) {
                    case 1:
                        $max_hands_number = 5;
                        break;
                    case 2:
                        $max_hands_number = 10;
                        break;
                    case 3:
                        $max_hands_number = 20;
                        break;
                    default:
                        $max_hands_number = 10;
                        break;
                }
                if ($this->getGameStateValue('roundNumber') >= $max_hands_number) {
                    $this->gamestate->nextState("endGame");
                }
            }
            
            // Shuffle deck
            $this->cards->shuffle('deck');
    
            self::notifyAllPlayers("discardAllCards", clienttranslate('All cards are discarded. Deck is reshuffled.'), array(
                'players' => $non_folded_players
            ));
    
            // Update the dealer player and make him the active player
            $players = self::getCollectionFromDb("SELECT player_id, player_name, is_fold, is_all_in, player_eliminated FROM player");
            $old_small_blind_player = self::getGameStateValue("smallBlindPlayer");
            if (count($players) - self::getGameStateValue("numEliminatedPlayers") <= 2) {
                // Heads up case (two players left) => dealer is small blind

                // New small blind player is the next non-eliminated player left to the current small blind player
                $new_small_blind_player = self::getPlayerAfter($old_small_blind_player);
                self::trace("Next small blind player is ${new_small_blind_player}");
                while ($players[$new_small_blind_player]["player_eliminated"]) {
                    // Skip player if he is eliminated
                    self::trace("${new_small_blind_player} was eliminated.");
                    $new_small_blind_player = self::getPlayerAfter($new_small_blind_player);
                    self::trace("Next small blind player is ${new_small_blind_player}");
                }
                self::setGameStateValue("smallBlindPlayer", $new_small_blind_player);

                // Dealer is also the small blind player
                $dealer = $new_small_blind_player;
                self::setGameStateValue("dealerId", $dealer);
            } else {
                self::trace("Next dealer is ${old_small_blind_player}");
                if (!$players[$old_small_blind_player]["player_eliminated"]) {
                    $dealer = $old_small_blind_player;
                } else {
                    while ($players[$old_small_blind_player]["player_eliminated"]) {
                        // Skip player if he is eliminated
                        self::trace("${old_small_blind_player} was eliminated.");
                        $old_small_blind_player = self::getPlayerAfter($old_small_blind_player);
                        self::trace("Next dealer is ${old_small_blind_player}");
                    }
                    $dealer = $old_small_blind_player;
                }

                self::setGameStateValue("dealerId", $dealer);

                $new_small_blind_player = self::getPlayerAfter($old_small_blind_player);
                self::trace("Next small blind player is ${new_small_blind_player}");
                while ($players[$new_small_blind_player]["player_eliminated"]) {
                    // Skip player if he is eliminated
                    self::trace("${new_small_blind_player} was eliminated.");
                    $new_small_blind_player = self::getPlayerAfter($new_small_blind_player);
                    self::trace("Next small blind player is ${new_small_blind_player}");
                }
                self::setGameStateValue("smallBlindPlayer", $new_small_blind_player);   
            }
            
            $this->gamestate->changeActivePlayer($new_small_blind_player);
            self::notifyAllPlayers("changeDealer", clienttranslate('The dealer becomes ${player_name}'), array(
                'player_name' => $players[$dealer]["player_name"],
                'dealer_id' => $dealer
            ));
            self::notifyAllPlayers("changeActivePlayer", clienttranslate('The small blind passes to ${player_name}'), array(
                'player_name' => self::getActivePlayerName(),
                'player_id' => $new_small_blind_player,
                'dealer_id' => $dealer
            ));
            self::giveExtraTime($new_small_blind_player);
            $this->gamestate->nextState("nextHand");
        }
    }

    function stZombiePass() {
        $players = self::getCollectionFromDb("SELECT player_id, is_fold, is_all_in, player_eliminated FROM player");

        $num_folded_players = self::getGameStateValue("numFoldedPlayers");
        $num_all_in_players = self::getGameStateValue("numAllInPlayers");
        $num_eliminated_players = self::getGameStateValue("numEliminatedPlayers");

        // Skip player if he has folded or is already all in
        $player_id = self::getPlayerAfter(self::getActivePlayerId());
        while (($players[$player_id]["is_fold"] || $players[$player_id]["is_all_in"] || $players[$player_id]["player_eliminated"]) && ($num_folded_players + $num_all_in_players + $num_eliminated_players) < (count($players) - 1)) {
            $player_id = self::getPlayerAfter($player_id);
        }
        $this->gamestate->changeActivePlayer($player_id);
        self::giveExtraTime($player_id);
        $this->gamestate->nextState("nextPlayer");
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
                    $round_stage = self::getGameStateValue("roundStage");
                    $small_blind_player = self::getGameStateValue("smallBlindPlayer");
                    $player_id = self::getActivePlayerId();
                    $player_current_tokens = self::getPlayersTokens()[$player_id];
            
                    // Build tokens array as if it was sent by the frontend
                    $sql = "SELECT player_id, player_score, player_stock_token_white, player_stock_token_blue, player_stock_token_red, 
                        player_stock_token_green, player_stock_token_black, player_bet_token_white, player_bet_token_blue, player_bet_token_red, 
                        player_bet_token_green, player_bet_token_black FROM player WHERE player_id = ${player_id}";
                    $player_stock = self::getCollectionFromDb($sql)[$player_id];
                    $tokens = array(
                        $player_stock['player_stock_token_white'], $player_stock['player_bet_token_white'], 
                        $player_stock['player_stock_token_blue'], $player_stock['player_bet_token_blue'],
                        $player_stock['player_stock_token_red'], $player_stock['player_bet_token_red'],
                        $player_stock['player_stock_token_green'], $player_stock['player_bet_token_green'],
                        $player_stock['player_stock_token_black'], $player_stock['player_bet_token_black'],
                    );
            
                    if ($round_stage == 0) {
                        // Blinds stage => place blind
                        if ($player_id == $small_blind_player) {
                            self::placeSmallBlind($tokens);
                        } else {
                            self::placeBigBlind($tokens);
                        }
                    } else {
                        // After blinds => give of tokens to the bank and fold
                        $player_name = self::getActivePlayerName();
            
                        $diff_stock = array(
                            "white" => -$player_stock['player_stock_token_white'],
                            "blue" => -$player_stock['player_stock_token_blue'],
                            "red" => -$player_stock['player_stock_token_red'],
                            "green" => -$player_stock['player_stock_token_green'],
                            "black" => -$player_stock['player_stock_token_black']
                        );
            
                        // Update playe's stock with new number of tokens
                        $colors = ["white", "blue", "red", "green", "black"];

                        $amount_already_bet = $player_stock["player_score"] - $player_current_tokens["stock"];
                        $sql = "UPDATE player SET player_score = ${amount_already_bet}, ";
                        foreach($colors as $color) {
                            $sql .= "player_stock_token_${color} = 0, ";
                        }
                        // Removing last ',' if exists
                        if (substr($sql, -2) == ', ') {
                            $sql = substr($sql, 0, -2);
                        }
                        $sql .= " WHERE player_id = '" . $player_id. "'";
                        self::DbQuery($sql);
                        self::notifyAllPlayers("makeChange", clienttranslate('${player_name} gives all her/his stock to the bank'), array(
                            'player_name' => $player_name,
                            'player_id' => $player_id,
                            'diff_stock' => $diff_stock
                        ));
            
                        // Build tokens array as if it was sent by the frontend
                        $sql = "SELECT player_id, player_stock_token_white, player_stock_token_blue, player_stock_token_red, 
                            player_stock_token_green, player_stock_token_black, player_bet_token_white, player_bet_token_blue, player_bet_token_red, 
                            player_bet_token_green, player_bet_token_black FROM player WHERE player_id = ${player_id}";
                        $player_stock = self::getCollectionFromDb($sql)[$player_id];
                        $tokens = array(
                            $player_stock['player_stock_token_white'], $player_stock['player_bet_token_white'], 
                            $player_stock['player_stock_token_blue'], $player_stock['player_bet_token_blue'],
                            $player_stock['player_stock_token_red'], $player_stock['player_bet_token_red'],
                            $player_stock['player_stock_token_green'], $player_stock['player_bet_token_green'],
                            $player_stock['player_stock_token_black'], $player_stock['player_bet_token_black'],
                        );
                                    
                        self::fold($player_id, $tokens);
                    }
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

        if( $from_version <= 2101072144 ) {
            // ! important ! Use DBPREFIX_<table_name> for all tables

            $sql = "ALTER TABLE DBPREFIX_player ADD wants_autoblinds BOOLEAN DEFAULT false;";
            self::applyDbUpgradeToAllDB( $sql );
        }

        if( $from_version <= 2101121126 ) {
            // ! important ! Use DBPREFIX_<table_name> for all tables

            $sql = "ALTER TABLE DBPREFIX_player ADD wants_manualbet BOOLEAN DEFAULT false;";
            self::applyDbUpgradeToAllDB( $sql );
        }

    }    
}
