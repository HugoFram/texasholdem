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
 * gameoptions.inc.php
 *
 * texasholdem game options description
 * 
 * In this file, you can define your game options (= game variants).
 *   
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in texasholdem.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

$game_options = array(

    /*
    
    // note: game variant ID should start at 100 (ie: 100, 101, 102, ...). The maximum is 199.
    100 => array(
                'name' => totranslate('my game option'),    
                'values' => array(

                            // A simple value for this option:
                            1 => array( 'name' => totranslate('option 1') )

                            // A simple value for this option.
                            // If this value is chosen, the value of "tmdisplay" is displayed in the game lobby
                            2 => array( 'name' => totranslate('option 2'), 'tmdisplay' => totranslate('option 2') ),

                            // Another value, with other options:
                            //  description => this text will be displayed underneath the option when this value is selected to explain what it does
                            //  beta=true => this option is in beta version right now (there will be a warning)
                            //  alpha=true => this option is in alpha version right now (there will be a warning, and starting the game will be allowed only in training mode except for the developer)
                            //  nobeginner=true  =>  this option is not recommended for beginners
                            3 => array( 'name' => totranslate('option 3'), 'description' => totranslate('this option does X'), 'beta' => true, 'nobeginner' => true )
                        ),
                'default' => 1
            ),

    */

    100 => array(
        'name' => totranslate('Game end'),    
        'values' => array(
            1 => array(
                'name' => totranslate("Winner takes all")
            ),
            2 => array(
                'name' => totranslate("Limited number of hands")
            )
        ),
        'default' => 1
    ),

    101 => array(
        'name' => totranslate('Number of hands'),    
        'values' => array(
            1 => array(
                'name' => totranslate("5 hands")
            ),
            2 => array(
                'name' => totranslate("10 hands")
            ),
            3 => array(
                'name' => totranslate("20 hands")
            )
        ),
        'displaycondition' => array( // Note: do not display this option unless these conditions are met
            array(
                'type' => 'otheroption',
                'id' => 100,
                'value' => array(2)
            )
            ),
        'default' => 2
    ),

    102 => array(
        'name' => totranslate('Blinds increase frequency'),    
        'values' => array(
            1 => array(
                'name' => totranslate("Very fast - every 2 hands"),
                'description' => totranslate("Blinds start at 1/2 and double every 2 hands or when a player is eliminated")
            ),
            2 => array(
                'name' => totranslate("Fast - every 5 hands"),
                'description' => totranslate("Blinds start at 1/2 and double every 5 hands or when a player is eliminated")
            ),
            3 => array(
                'name' => totranslate("Normal - every 10 hands"),
                'description' => totranslate("Blinds start at 1/2 and double every 10 hands or when a player is eliminated")
            ),
            4 => array(
                'name' => totranslate("Slow - every 20 hands"),
                'description' => totranslate("Blinds start at 1/2 and double every 20 hands or when a player is eliminated"),
                'nobeginner' => TRUE
            )
        ),
        'default' => 2
    ),

    103 => array(
        'name' => totranslate('Initial amount of chips'),    
        'values' => array(
            1 => array(
                'name' => totranslate("Default - 100 total value per player"),
                'description' => totranslate("Each player starts with a total chips value of 100. This is the default option.")
            ),
            2 => array(
                'name' => totranslate("200 total value per player"),
                'description' => totranslate("Each player starts with a total chips value of 200."),
                'nobeginner' => TRUE
            ),
            3 => array(
                'name' => totranslate("500 total value per player"),
                'description' => totranslate("Each player starts with a total chips value of 500."),
                'nobeginner' => TRUE
            ),
            4 => array(
                'name' => totranslate("1000 total value per player"),
                'description' => totranslate("Each player starts with a total chips value of 1000."),
                'nobeginner' => TRUE
            )
        ),
        'default' => 1
    ),

    104 => array(
        'name' => totranslate('All in minimum raise exception'),    
        'values' => array(
            1 => array(
                'name' => totranslate("Yes"),
                'description' => totranslate("The All In action is NOT subject to the minimum raise amount rule. Players can go all in to raise a bet even if they don't have enough chips in stock to raise by the current minimum bet or raise amount.")
            ),
            2 => array(
                'name' => totranslate("No"),
                'description' => totranslate("The All In action is subject to the minimum raise amount rule. Players can not go all in to raise a bet if they don't have enough chips in stock to raise by the current minimum bet or raise amount."),
                'nobeginner' => TRUE
            )
        ),
        'default' => 1
    ),

    105 => array(
        'name' => totranslate('Show remaining river cards'),    
        'values' => array(
            1 => array(
                'name' => totranslate("Yes"),
                'description' => totranslate("All the cards of the river are shown at the end of a hand even if all players but one have folded.")
            ),
            2 => array(
                'name' => totranslate("No"),
                'description' => totranslate("Regular rule: if all players but one have folded, the remaining river cards are not revealed.")
            )
        ),
        'default' => 2
    )

);

$game_preferences = array(
    100 => array(
			'name' => totranslate('Table cards display mode'),
			'needReload' => true, // after user changes this preference game interface would auto-reload
			'values' => array(
					1 => array('name' => totranslate('Face-down cards'), 'cssPref' => 'hiddencard_card' ),
					2 => array('name' => totranslate('Placeholders'), 'cssPref' => 'hiddencard_placeholder' )
			)
    ),
    101 => array(
            'name' => totranslate('Background style'),
            'needReload' => true, // after user changes this preference game interface would auto-reload
            'values' => array(
                    1 => array('name' => totranslate('Dark wood vertical'), 'cssPref' => 'dark-wood-vertical-background'),
                    2 => array('name' => totranslate('Dark wood horizontal'), 'cssPref' => 'dark-wood-horizontal-background'),
                    3 => array('name' => totranslate('Standard BGA background'))
            )
    ),
    102 => array(
            'name' => totranslate('Table style'),
            'needReload' => true, // after user changes this preference game interface would auto-reload
            'values' => array(
                    1 => array('name' => totranslate('Random')),
                    2 => array('name' => totranslate('Blue')),
                    3 => array('name' => totranslate('Red')),
                    4 => array('name' => totranslate('Green'))
            )
    ),
    103 => array(
            'name' => totranslate('Table scale (for mobile only)'),
            'needReload' => true, // after user changes this preference game interface would auto-reload
            'values' => array(
                    1 => array('name' => totranslate('Large')),
                    2 => array('name' => totranslate('Medium')),
                    3 => array('name' => totranslate('Small'))
                )
    )
);
