/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * texasholdem implementation : © <Hugo Frammery> <hugo@frammery.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * texasholdem.js
 *
 * texasholdem user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter"
],
function (dojo, declare) {
    return declare("bgagame.texasholdem", ebg.core.gamegui, {
        constructor: function(){
            console.log('texasholdem constructor');
              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;
            this.tokenValues = [];

        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        
        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );
            
            // Get token values
            this.tokenValues = gamedatas.tokenvalues;

            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];

                // TODO: Setting up players boards if needed

                var tokenColors = ["white", "blue", "red", "green", "black"];

                var playerBoardStockTokens = "";
                var playerBoardBetTokens = "";

                tokenColors.forEach(color => {
                    var stockTokens, betTokens;
                    switch(color) {
                        case "white": 
                            stockTokens = player.stock_white;
                            betTokens = player.bet_white;
                            break;
                        case "blue": 
                            stockTokens = player.stock_blue;
                            betTokens = player.bet_blue;
                            break;
                        case "red": 
                            stockTokens = player.stock_red;
                            betTokens = player.bet_red;
                            break;
                        case "green": 
                            stockTokens = player.stock_green;
                            betTokens = player.bet_green;
                            break;
                        case "black": 
                            stockTokens = player.stock_black;
                            betTokens = player.bet_black;
                            break;
                        default:
                            stockTokens = 0;
                            break;
                    }

                    // Stock tokens
                    dojo.place(this.format_block('jstpl_player_stock_token', {
                        TEXT_CLASS: color == "white" ? "tokennumberdark" : "tokennumberlight",
                        TOKEN_NUM: stockTokens
                    }), 'token' + color + '_' + player_id, "first");
                    if (stockTokens > 0) {
                        if (dojo.hasClass('token' + color + '_' + player_id, "tokenhidden")) {
                            dojo.removeClass('token' + color + '_' + player_id, "tokenhidden");
                        }
                    } else {
                        dojo.addClass('token' + color + '_' + player_id, "tokenhidden");
                    }
                    playerBoardStockTokens += this.format_block('jstpl_player_board_token', {
                            type: "stock",
                            color: color,
                            player_id: player_id,
                            num_tokens: stockTokens
                        });

                    // Bet tokens
                    dojo.place(this.format_block('jstpl_player_bet_token', {
                        TEXT_CLASS: color == "white" ? "tokennumberdark" : "tokennumberlight",
                        TOKEN_NUM: betTokens
                    }), 'bettoken' + color + '_' + player_id, "first");
                    if (betTokens > 0) {
                        if (dojo.hasClass('bettoken' + color + '_' + player_id, "tokenhidden")) {
                            dojo.removeClass('bettoken' + color + '_' + player_id, "tokenhidden");
                        }
                    } else {
                        dojo.addClass('bettoken' + color + '_' + player_id, "tokenhidden");
                    }
                    playerBoardBetTokens += this.format_block('jstpl_player_board_token', {
                            type: "bet",
                            color: color,
                            player_id: player_id,
                            num_tokens: betTokens
                        });
                });

                // Player board tokens
                dojo.place(this.format_block('jstpl_player_board_tokens', {
                    player_id: player_id,
                    STOCK_TOKENS: playerBoardStockTokens,
                    BET_TOKENS: playerBoardBetTokens
                }), ('player_board_' + player_id));

                if (player_id == this.player_id) {
                    // Add relevant onclick event to all immediate children of the element with id playertabletokens_{player_id}
                    dojo.query('#playertabletokens_' + player_id + ' > *').connect('onclick', this, 'onStockTokenClicked');
                    // Add relevant onclick event to all immediate children of the element with id bettingarea_{player_id}
                    dojo.query('#bettingarea_' + player_id + ' > *').connect('onclick', this, 'onBetTokenClicked');
                
                    // Autoblinds slider
                    if (player.wants_autoblinds == 1) {
                        $("autoblinds").checked = true;
                    }

                    // Betmode slider
                    if (player.wants_manualbet == 1) {
                        $("betmode").checked = true;
                    }
                }

                // Compute total values
                this.updateTotal("stock", player_id);
                this.updateTotal("bet", player_id);
                var playerbettotal = $("playerbettotal_" + player_id);
                var playerstocktotal = $("playerstocktotal_" + player_id);
                if (parseInt(player.eliminated)) {
                    this.fadeOutAndDestroy(playerbettotal, 500);
                    this.fadeOutAndDestroy(playerstocktotal, 500);
                }

                // Add totals tooltips
                if (player_id == this.player_id) {
                    this.addTooltip(playerstocktotal.id, _("This is the amount you still have in stock"), '');
                    this.addTooltip(playerbettotal.id, _("This is the amount you have bet for this betting round"), '');
                } else {
                    this.addTooltip(playerstocktotal.id, _("This is the amount " + player.name + " still has in stock"), '');
                    this.addTooltip(playerbettotal.id, _("This is the amount " + player.name + " has bet for this betting round"), '');
                }

                // Add tooltips to tokens
                this.updateTooltips("pot", null);
                this.updateTooltips("stock", player_id);
                this.updateTooltips("bet", player_id);

                // Add tooltips to option sliders
                this.addTooltip("autoblinds", _("This option lets you define if you want your blinds to be placed automatically or if you prefer to place them manually by clicking on your chips for more immersion"), '');
                this.addTooltip("betmode", _("This option lets you define if you want to choose the amount of your raises by entering a number in a pop-up window or by manually placing chips in your betting area by clicking on them"), '');
            }

            // Highlight the table of the currently active player
            var playerTable = $("playertablecards_" + gamedatas.activeplayerid).parentElement;
            dojo.addClass(playerTable, "highlighted-border");

            // Place the dealer button
            var dealerButton = $("dealer_button_" + gamedatas.dealer);
            dojo.removeClass(dealerButton, "dealer-button-hidden");
            
            // TODO: Set up your game interface here, according to "gamedatas"

            // Display tokens bet at previous round stages
            var tableTokens = gamedatas.tokensontable;
            for (var i in tableTokens) {
                var color = tableTokens[i].token_color;
                var tokenNum  = tableTokens[i].token_number;

                dojo.place(this.format_block('jstpl_table_token', {
                    TEXT_CLASS: color == "white" ? "tokennumberdark" : "tokennumberlight",
                    TOKEN_NUM: tokenNum
                }), 'token' + color + '_table', "first");

                if (tokenNum > 0) {
                    if (dojo.hasClass('token' + color + '_table', "tokenhidden")) {
                        dojo.removeClass('token' + color + '_table', "tokenhidden");
                    }
                } else {
                    dojo.addClass('token' + color + '_table', "tokenhidden");
                }
            }

            // Compute total values
            this.updateTotal("pot", null);
            this.addTooltip('pottotal', _("This is the total amount bet at previous betting rounds by all players"), '');

            // Display cards in hand
            var hands = gamedatas.hands;
            var playerToIsLeftCard = [];
            for (var i in gamedatas.players) {
                // Display folded message if player is folded
                if (parseInt(gamedatas.players[i].is_fold)) {
                    dojo.place('<div class = "folded"><span>Folded</span></div>', 'playertablecards_' + gamedatas.players[i].id);
                } else {
                    playerToIsLeftCard.push({
                        id: gamedatas.players[i].id,
                        isLeftCard: true
                    });
                }
            }
            for (var cardId in hands) {
                var card = hands[cardId];
                var player = playerToIsLeftCard.filter(player => player.id == card.location_arg)[0];

                dojo.place(this.format_block('jstpl_card', {
                    CARD_LOCATION_CLASS: player.isLeftCard == true ? "cardinhand cardinhandleft" : "cardinhand cardinhandright",
                    CARD_VISIBILITY_CLASS: card.location_arg == this.player_id ? "cardvisible" : "cardhidden",
                    // For cards in another player's hand, set background position to 0, otherwise one could know the card by inspecting the HTML element style
                    BACKGROUND_POSITION_LEFT_PERCENTAGE: card.location_arg == this.player_id ? -100 * (card.type_arg - 2) : 0,
                    BACKGROUND_POSITION_TOP_PERCENTAGE: card.location_arg == this.player_id ? -100 * (card.type - 1) : 0
                }), 'playertablecards_' + card.location_arg);

                if (player.isLeftCard) {
                    player.isLeftCard = false;
                }
            }

            // Display cards on table
            // Flop
            var isFlopShown = Object.keys(gamedatas.cardsflop).length > 0;
            for (var cardId in [0, 1, 2]) {
                dojo.place(this.format_block('jstpl_card', {
                    CARD_LOCATION_CLASS: "cardflop",
                    CARD_VISIBILITY_CLASS: isFlopShown ? "cardvisible" : "cardhidden",
                    BACKGROUND_POSITION_LEFT_PERCENTAGE: isFlopShown ? -100 * (gamedatas.cardsflop[Object.keys(gamedatas.cardsflop)[cardId]].type_arg - 2) : 0,
                    BACKGROUND_POSITION_TOP_PERCENTAGE: isFlopShown ? -100 * (gamedatas.cardsflop[Object.keys(gamedatas.cardsflop)[cardId]].type - 1) : 0
                }), 'flop'+(parseInt(cardId)+1));
            }
            // Turn
            var isTurnShown = Object.keys(gamedatas.cardturn).length > 0;
            dojo.place(this.format_block('jstpl_card', {
                CARD_LOCATION_CLASS: "cardturn",
                CARD_VISIBILITY_CLASS: isTurnShown ? "cardvisible" : "cardhidden",
                BACKGROUND_POSITION_LEFT_PERCENTAGE: isTurnShown ? -100 * (gamedatas.cardturn[Object.keys(gamedatas.cardturn)[0]].type_arg - 2) : 0,
                BACKGROUND_POSITION_TOP_PERCENTAGE: isTurnShown ? -100 * (gamedatas.cardturn[Object.keys(gamedatas.cardturn)[0]].type - 1) : 0
            }), 'turn');
            // River
            var isRiverShown = Object.keys(gamedatas.cardriver).length > 0;
            dojo.place(this.format_block('jstpl_card', {
                CARD_LOCATION_CLASS: "cardriver",
                CARD_VISIBILITY_CLASS: isRiverShown ? "cardvisible" : "cardhidden",
                BACKGROUND_POSITION_LEFT_PERCENTAGE: isRiverShown ? -100 * (gamedatas.cardriver[Object.keys(gamedatas.cardriver)[0]].type_arg - 2) : 0,
                BACKGROUND_POSITION_TOP_PERCENTAGE: isRiverShown ? -100 * (gamedatas.cardriver[Object.keys(gamedatas.cardriver)[0]].type - 1) : 0
            }), 'river');

            // Connect autoblinds check box
            dojo.query('#autoblinds').connect('change', this, 'onAutoblindsChange');

            // Connect betmode check box
            dojo.query('#betmode').connect('change', this, 'onBetmodeChange');

            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );
            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Show some HTML block at this game state
                dojo.style( 'my_html_block_id', 'display', 'block' );
                
                break;
           */
           
           
            case 'dummmy':
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );
                
                break;
           */
           
           
            case 'dummmy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
                /*               
                Example:

                case 'myGameState':
                
                // Add 3 action buttons in the action status bar:
                
                    this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' ); 
                    this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' ); 
                    this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' ); 
                    break;

                */

                    case 'smallBlind':
                        this.addActionButton('place_small_blind', _('Place small blind'), 'onPlaceSmallBlind', null, false, "red"); 
                        this.addActionButton('change', _('Make change'), 'onMakeChange');
                        break;

                    case 'bigBlind':
                        this.addActionButton('place_big_blind', _('Place big blind'), 'onPlaceBigBlind', null, false, "red"); 
                        this.addActionButton('change', _('Make change'), 'onMakeChange');
                        break;

                    case 'playerTurn':
                        if (args.check) this.addActionButton('check', _('Check'), 'onCheck'); 
                        if (args.call) this.addActionButton('call', _('Call'), 'onCall'); 
                        if (args.raise_by_first) {
                            this.addActionButton('raiseByFirst', _('Raise by ' + args.raise_by_first), 'onRaiseBy', null, false, "red");
                        }
                        if (args.raise_by_second) {
                            this.addActionButton('raiseBySecond', _('Raise by ' + args.raise_by_second), 'onRaiseBy', null, false, "red");
                        }
                        if (args.raise_by_third) {
                            this.addActionButton('raiseByThird', _('Raise by ' + args.raise_by_third), 'onRaiseBy', null, false, "red");
                        }
                        if (args.raise) {
                            if (!$("betmode").checked) {
                                this.addActionButton('raise', _('Choose raise'), 'onRaise', null, false, "red"); 
                            } else {
                                this.addActionButton('raise', _('Confirm raise'), 'onRaise', null, false, "red");
                                this.addTooltip('raise', 'You need can move chips to your betting area by clicking on them', _('This will raise by the amount of chips you currently have in your betting area'));
                            }
                        }
                        if (args.bet_first) {
                            this.addActionButton('betFirst', _('Bet ' + args.bet_first), 'onRaiseBy', null, false, "red");
                        }
                        if (args.bet_second) {
                            this.addActionButton('betSecond', _('Bet ' + args.bet_second), 'onRaiseBy', null, false, "red");
                        }
                        if (args.bet_third) {
                            this.addActionButton('betThird', _('Bet ' + args.bet_third), 'onRaiseBy', null, false, "red");
                        }
                        if (args.bet) this.addActionButton('bet', _('Choose bet'), 'onRaise', null, false, "red");
                        if (args.fold) this.addActionButton('fold', _('Fold'), 'onFold', null, false, "gray");
                        if (args.all_in) this.addActionButton('all_in', _('All in'), 'onAllIn', null, false, "red"); 
                        this.addActionButton('change', _('Make change'), 'onMakeChange');

                        break;

                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */

        getSlideTopAndLeftProperties: function(playerTable, source, dest) {
            var sourcePos = dojo.position(source.id);
            var targetPos = dojo.position(dest.id);

            var screenWidth = window.screen.width;
            var targetTopValue, targetLeftValue;
            if (screenWidth < 1400) {
                if (playerTable == null || dojo.hasClass(playerTable, "playertable_SW") || dojo.hasClass(playerTable, "playertable_NW") || dojo.hasClass(playerTable, "playertable_W")) {
                    targetTopValue = -(targetPos.x - sourcePos.x) + dojo.getStyle(source.id, "top");
                    targetLeftValue = targetPos.y - sourcePos.y + dojo.getStyle(source.id, "left");
                } else if (dojo.hasClass(playerTable, "playertable_S") || dojo.hasClass(playerTable, "playertable_N")) {
                    targetTopValue = targetPos.y - sourcePos.y + dojo.getStyle(source.id, "top");
                    targetLeftValue = targetPos.x - sourcePos.x + dojo.getStyle(source.id, "left");
                } else if (dojo.hasClass(playerTable, "playertable_E") || dojo.hasClass(playerTable, "playertable_NE") || dojo.hasClass(playerTable, "playertable_SE")) {
                    targetTopValue = targetPos.x - sourcePos.x + dojo.getStyle(source.id, "top");
                    targetLeftValue = -(targetPos.y - sourcePos.y) + dojo.getStyle(source.id, "left");
                }
            } else {
                if (playerTable == null || dojo.hasClass(playerTable, "playertable_SW") || dojo.hasClass(playerTable, "playertable_S") || 
                    dojo.hasClass(playerTable, "playertable_SE") || dojo.hasClass(playerTable, "playertable_NW") || 
                    dojo.hasClass(playerTable, "playertable_N") || dojo.hasClass(playerTable, "playertable_NE")) {
                    targetTopValue = targetPos.y - sourcePos.y + dojo.getStyle(source.id, "top");
                    targetLeftValue = targetPos.x - sourcePos.x + dojo.getStyle(source.id, "left");
                } else if (dojo.hasClass(playerTable, "playertable_W")) {
                    targetTopValue = -(targetPos.x - sourcePos.x) + dojo.getStyle(source.id, "top");
                    targetLeftValue = targetPos.y - sourcePos.y + dojo.getStyle(source.id, "left");
                } else if (dojo.hasClass(playerTable, "playertable_E")) {
                    targetTopValue = targetPos.x - sourcePos.x + dojo.getStyle(source.id, "top");
                    targetLeftValue = -(targetPos.y - sourcePos.y) + dojo.getStyle(source.id, "left");
                }
            }

            return [targetTopValue, targetLeftValue];
        },

        updateTotal: function(source, sourceArg) {
            var colors = ["white", "blue", "red", "green", "black"];
            
            var totalValue = 0;
            colors.forEach((color) => {
                // Retrieve HTML elements corresponding to tokens in both the player's stock and his betting area
                switch(source) {
                    case "pot":
                        var token = $("token" + color + "_table");
                        break;
                    case "stock": 
                        var token = $("token" + color + "_" + sourceArg);
                        break;
                    case "bet":
                        var token = $("bettoken" + color + "_" + sourceArg);
                        break;
                }
                totalValue += parseInt(token.firstElementChild.textContent) * this.tokenValues[color];

            }, this);

            switch(source) {
                case "pot":
                    dojo.html.set($("pottotal"), totalValue);
                    break;
                case "stock": 
                    dojo.html.set($("playerstocktotal_" + sourceArg), totalValue);
                    break;
                case "bet":
                    dojo.html.set($("playerbettotal_" + sourceArg), totalValue);
                    break;
            }
        },

        updatePlayerBoardTokens: function(source, playerId) {
            var colors = ["white", "blue", "red", "green", "black"];
            
            colors.forEach((color) => {
                // Retrieve HTML elements corresponding to tokens in both the player's stock and his betting area
                if (source == "stock") {
                    // Stock 
                    var tableToken = $("token" + color + "_" + playerId);
                    var boardTokenNum = $("panelstocktokencount_" + color + "_" + playerId);
                    dojo.html.set(boardTokenNum, "x" + parseInt(tableToken.firstElementChild.textContent));
                } else if (source == "bet") {
                    // Bet
                    tableToken = $("bettoken" + color + "_" + playerId);
                    boardTokenNum = $("panelbettokencount_" + color + "_" + playerId);
                    dojo.html.set(boardTokenNum, "x" + parseInt(tableToken.firstElementChild.textContent));
                }
            }, this);
        },

        updateTooltips: function(source, sourceArg) {
            var colors = ["white", "blue", "red", "green", "black"];

            colors.forEach((color) => {
                // Retrieve HTML elements corresponding to tokens in both the player's stock and his betting area
                switch(source) {
                    case "pot":
                        var token = $("token" + color + "_table");
                        var tokenNumber = token.firstElementChild.textContent;
                        this.removeTooltip(token);
                        if (tokenNumber > 1) {
                            this.addTooltip(
                                token.id, 
                                _("There are " + tokenNumber + " " + color + " chips"), 
                                ''
                            );
                        } else if (tokenNumber == 1) {
                            this.addTooltip(
                                token.id, 
                                _("There is " + tokenNumber + " " + color + " chip"), 
                                ''
                            );
                        }
                        break;
                    case "stock": 
                        var token = $("token" + color + "_" + sourceArg);
                        var tokenNumber = token.firstElementChild.textContent;
                        this.removeTooltip(token);
                        if (sourceArg == this.player_id) {
                            if (tokenNumber > 1) {
                                this.addTooltip(
                                    token.id, 
                                    _("You have " + tokenNumber + " " + color + " chips in your stock"), 
                                    _("Click to move " + this.tokenValues[color] + " to your current bet")
                                );
                            } else if (tokenNumber == 1) {
                                this.addTooltip(
                                    token.id, 
                                    _("You have " + tokenNumber + " " + color + " chip in your stock"), 
                                    _("Click to move " + this.tokenValues[color] + " to your current bet")
                                );
                            }
                        } else {
                            if (tokenNumber > 1) {
                                this.addTooltip(
                                    token.id, 
                                    _("There are " + tokenNumber + " " + color + " chips"), 
                                    ''
                                );
                            } else if (tokenNumber == 1) {
                                this.addTooltip(
                                    token.id, 
                                    _("There is " + tokenNumber + " " + color + " chip"), 
                                    ''
                                );
                            }
                        }
                        break;
                    case "bet":
                        var token = $("bettoken" + color + "_" + sourceArg);
                        var tokenNumber = token.firstElementChild.textContent;
                        this.removeTooltip(token);
                        if (sourceArg == this.player_id) {
                            if (tokenNumber > 1) {
                                this.addTooltip(
                                    token.id, 
                                    _("You have " + tokenNumber + " " + color + " chips in your betting area"), 
                                    _("Click to move " + this.tokenValues[color] + " back to your stock")
                                );
                            } else if (tokenNumber == 1) {
                                this.addTooltip(
                                    token.id, 
                                    _("You have " + tokenNumber + " " + color + " chip in your betting area"), 
                                    _("Click to move " + this.tokenValues[color] + " back to your stock")
                                );
                            }
                        } else {
                            if (tokenNumber > 1) {
                                this.addTooltip(
                                    token.id, 
                                    _("There are " + tokenNumber + " " + color + " chips"), 
                                    ''
                                );
                            } else if (tokenNumber == 1) {
                                this.addTooltip(
                                    token.id, 
                                    _("There is " + tokenNumber + " " + color + " chip"), 
                                    ''
                                );
                            }
                        }
                        break;
                }
            }, this);
        },

        // Define an display up to 3 tokens combination the player can exchange against the tokens he has put in the given area
        updateReceivedTokens: function() {
            // Get current number of tokens in the given area
            var givenTokens = $("changegiventokens").children;

            var colors = ["white", "blue", "red", "green", "black"];
            var nonZeroGivenTokenColors = [];

            var givenValue = 0;
            var maxToken = "white";
            colors.forEach(color => {
                var token = givenTokens["changegiventokens_" + color];
                var number = parseInt(token.firstElementChild.textContent);

                if (number > 0) {
                    maxToken = color;
                    nonZeroGivenTokenColors.push(color);
                }
                givenValue += number * this.tokenValues[color];
            });

            var lowestValueProposition = {
                white: 0,
                blue: 0,
                red: 0,
                green: 0,
                black: 0
            };
            var mixedProposition = {
                white: 0,
                blue: 0,
                red: 0,
                green: 0,
                black: 0
            };
            var highestValueProposition = {
                white: 0,
                blue: 0,
                red: 0,
                green: 0,
                black: 0
            };

            // Remove selection border if no tokens are given
            if (givenValue <= 0) {
                dojo.query(".highlighted-border").removeClass("highlighted-border");
            }

            // Lowest token proposition
            // => Only tokens of the lowest possible value
            var numLowestTokens = Math.floor(givenValue / this.tokenValues[colors[0]]);
            var lowestToken = $("changereceivedtokenslowest_" + colors[0]);
            dojo.html.set(lowestToken.firstElementChild, numLowestTokens);
            lowestValueProposition[colors[0]] = numLowestTokens;
            if (numLowestTokens > 0 && dojo.hasClass(lowestToken, "tokendisabled")) {
                dojo.removeClass(lowestToken, "tokendisabled");
            } else if (numLowestTokens <= 0 && !dojo.hasClass(lowestToken, "tokendisabled")) {
                dojo.addClass(lowestToken, "tokendisabled");
            }

            // Mixed token proposition
            // => Mix of token colors. The goal is to split the value of given tokens evenly among the "lower" value tokens
            var remainingGivenValue = givenValue;
            var lowerValueColors = colors.filter(color => nonZeroGivenTokenColors.indexOf(color) == -1 && colors.indexOf(color) < colors.indexOf(maxToken));
            var targetValuePerTokenColor = remainingGivenValue / lowerValueColors.length;
            colors.slice().reverse().forEach(color => {
                // Only provide tokens of "lower" colors than the one provided
                if (lowerValueColors.indexOf(color) != -1 && color != colors[0]) {
                    var numTokens = Math.floor(targetValuePerTokenColor / this.tokenValues[color]);
                    var token = $("changereceivedtokensmixed_" + color);
                    dojo.html.set(token.firstElementChild, numTokens);
                    mixedProposition[color] = numTokens;

                    if (numTokens > 0 && dojo.hasClass(token, "tokendisabled")) {
                        dojo.removeClass(token, "tokendisabled");
                    } else if (numTokens <= 0 && !dojo.hasClass(token, "tokendisabled")) {
                        dojo.addClass(token, "tokendisabled");
                    }

                    remainingGivenValue -= numTokens * this.tokenValues[color];
                } else if (color == colors[0]) {
                    // Complete with tokens of the lowest value
                    var numTokens = Math.floor(remainingGivenValue / this.tokenValues[color]);
                    var token = $("changereceivedtokensmixed_" + color);
                    dojo.html.set(token.firstElementChild, numTokens);
                    mixedProposition[color] = numTokens;

                    if (numTokens > 0 && dojo.hasClass(token, "tokendisabled")) {
                        dojo.removeClass(token, "tokendisabled");
                    } else if (numTokens <= 0 && !dojo.hasClass(token, "tokendisabled")) {
                        dojo.addClass(token, "tokendisabled");
                    }

                    remainingGivenValue -= numTokens * this.tokenValues[color];
                } else {
                    var token = $("changereceivedtokensmixed_" + color);
                    dojo.html.set(token.firstElementChild, 0);
                    mixedProposition[color] = 0;
                    if (!dojo.hasClass(token, "tokendisabled")) {
                        dojo.addClass(token, "tokendisabled");
                    }
                }
            });

            // Highest token proposition
            // Convert given tokens into tokens of value as high as possible
            var remainingGivenValue = givenValue;
            colors.slice().reverse().forEach(color => {
                // Only provide tokens of different colors than the one provided
                if (nonZeroGivenTokenColors.indexOf(color) == -1 || color == colors[0]) {
                    var numTokens = Math.floor(remainingGivenValue / this.tokenValues[color]);
                    var token = $("changereceivedtokenshighest_" + color);
                    dojo.html.set(token.firstElementChild, numTokens);
                    highestValueProposition[color] = numTokens;

                    if (numTokens > 0 && dojo.hasClass(token, "tokendisabled")) {
                        dojo.removeClass(token, "tokendisabled");
                    } else if (numTokens <= 0 && !dojo.hasClass(token, "tokendisabled")) {
                        dojo.addClass(token, "tokendisabled");
                    }

                    remainingGivenValue -= numTokens * this.tokenValues[color];
                } else {
                    var token = $("changereceivedtokenshighest_" + color);
                    dojo.html.set(token.firstElementChild, 0);
                    highestValueProposition[color] = 0;
                    if (!dojo.hasClass(token, "tokendisabled")) {
                        dojo.addClass(token, "tokendisabled");
                    }
                }
            });

            // Remove duplicated proposition
            if (givenValue > 0) {
                if (JSON.stringify(lowestValueProposition) === JSON.stringify(mixedProposition)) {
                    var duplicatedSelection = $("proposed_change_receivedtokensmixed");
                    var selectionTokens = duplicatedSelection.children;
                    for (var i = 0; i < selectionTokens.length; i++) {
                        dojo.html.set(selectionTokens[i].firstElementChild, 0);
                        dojo.addClass(selectionTokens[i], "tokendisabled")
                    }
                }
                if (JSON.stringify(lowestValueProposition) === JSON.stringify(highestValueProposition)) {
                    var duplicatedSelection = $("proposed_change_receivedtokenshighest");
                    var selectionTokens = duplicatedSelection.children;
                    for (var i = 0; i < selectionTokens.length; i++) {
                        dojo.html.set(selectionTokens[i].firstElementChild, 0);
                        dojo.addClass(selectionTokens[i], "tokendisabled")
                    }
                }
                if (JSON.stringify(mixedProposition) === JSON.stringify(highestValueProposition)) {
                    var duplicatedSelection = $("proposed_change_receivedtokenshighest");
                    var selectionTokens = duplicatedSelection.children;
                    for (var i = 0; i < selectionTokens.length; i++) {
                        dojo.html.set(selectionTokens[i].firstElementChild, 0);
                        dojo.addClass(selectionTokens[i], "tokendisabled")
                    }
                }
            }

        },

        raiseBy: function(raiseAmount) {
            var colors = ["white", "blue", "red", "green", "black"];
            
            var valuesString = "";
            colors.forEach(color => {
                // Retrieve HTML elements corresponding to tokens in both the player's stock and his betting area
                var stockToken = $("token" + color + "_" + this.player_id);
                var betToken = $("bettoken" + color + "_" + this.player_id);
                // Extract number of token from HTML element and build a string to pass to the server
                valuesString += stockToken.firstElementChild.textContent + ";";
                valuesString += betToken.firstElementChild.textContent + ";";
            });

            this.ajaxcall("/texasholdem/texasholdem/raiseBy.html", { 
                lock: true, 
                tokens: valuesString,
                raiseValue: raiseAmount
            }, this, function(result) {}, function(is_error) {});
        },

        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */
        
        /* Example:
        
        onMyMethodToCall1: function( evt )
        {
            console.log( 'onMyMethodToCall1' );
            
            // Preventing default browser reaction
            dojo.stopEvent( evt );

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'myAction' ) )
            {   return; }

            this.ajaxcall( "/texasholdem/texasholdem/myAction.html", { 
                                                                    lock: true, 
                                                                    myArgument1: arg1, 
                                                                    myArgument2: arg2,
                                                                    ...
                                                                 }, 
                         this, function( result ) {
                            
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)
                            
                         }, function( is_error) {

                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                         } );        
        },        
        
        */

        onStockTokenClicked: function(event) {
            console.log("onStockTokenClicked");

            // Preventing default browser reaction
            dojo.stopEvent(event);

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if(!this.checkAction('placeBet', false) && !this.checkAction('placeSmallBlind', false) && !this.checkAction('placeBigBlind')) return;

            var stockToken = event.currentTarget;
            var betToken = $("bet" + stockToken.id);
            var color = stockToken.id.replace(/token/, "").replace(/_[0-9]+/, "");

            // Retrieve current number of tokens of that color in stock
            var currentStock = parseInt(stockToken.firstElementChild.textContent);

            // Check if the player has at least one token of that color to bet
            if (currentStock > 0) {
                currentStock--;

                // Move a token from the player's stock to the betting area
                // 1) Decrement number of token in stock and hide the token if it was the last one
                dojo.html.set(stockToken.firstElementChild, currentStock);
                // 2) Update stock total
                this.updateTotal("stock", this.player_id);
                this.updateTooltips("stock", this.player_id);
                if (currentStock <= 0) {
                    dojo.addClass(stockToken.id, "tokenhidden");
                }
                // 3) Place the visual of a token on top of the token stock pile
                dojo.place('<div class = "token token' + color + ' behind" id = "slidingstocktoken_' + color + '_' + currentStock + '"></div>', "playertabletokens_" + this.player_id);
                // 4) Slide the token from the stock to the betting area
                var anim = this.slideToObject('slidingstocktoken_' + color + '_' + currentStock, betToken.id);
                dojo.connect(anim, 'onEnd', this, function(node) {
                    // 5) Destroy token visual used for the animation
                    dojo.destroy(node);
                    // 6) Increment the number from the betting area
                    dojo.html.set(betToken.firstElementChild, parseInt(betToken.firstElementChild.textContent) + 1);
                    // 7) Unhide the betting area token if it the first token of that color
                    if (dojo.hasClass(betToken.id, "tokenhidden")) {
                        dojo.removeClass(betToken.id, "tokenhidden");
                    }
                    // 8) Update bet total
                    this.updateTotal("bet", this.player_id);
                    this.updatePlayerBoardTokens("bet", this.player_id);
                    this.updateTooltips("bet", this.player_id);
                });
                anim.play();
            }
            this.updatePlayerBoardTokens("stock", this.player_id);
        },

        onBetTokenClicked: function(event) {
            console.log("onBetTokenClicked");
            
            // Preventing default browser reaction
            dojo.stopEvent(event);

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if(!this.checkAction('placeBet', false) && !this.checkAction('placeSmallBlind', false) && !this.checkAction('placeBigBlind')) return;

            var betToken = event.currentTarget;
            var stockToken = $(betToken.id.replace(/bet/, ""));
            var color = stockToken.id.replace(/token/, "").replace(/_[0-9]+/, "");

            // Retrieve current number of tokens of that color in the betting area
            var currentBet = parseInt(betToken.firstElementChild.textContent);

            // Check if at least one token of that color has been bet
            if (currentBet > 0) {
                currentBet--;

                // Move a token from the player's betting area to the stock
                // 1) Decrement number of token in betting area and hide the token if it was the last one
                dojo.html.set(betToken.firstElementChild, currentBet);
                // 2) Update bet total
                this.updateTotal("bet", this.player_id);
                this.updateTooltips("bet", this.player_id);
                if (currentBet <= 0) {
                    dojo.addClass(betToken.id, "tokenhidden");
                }
                // 3) Place the visual of a token on top of the token betting area pile
                dojo.place('<div class = "token token' + color + ' bettoken behind" id = "slidingbettoken_' + color + '_' + currentBet + '"></div>', "bettingarea_" + this.player_id);
                // 4) Slide the token from the betting area to the stock
                var anim = this.slideToObject('slidingbettoken_' + color + '_' + currentBet, stockToken.id);
                dojo.connect(anim, 'onEnd', this, function(node) {
                    // 5) Destroy token visual used for the animation
                    dojo.destroy(node);
                    // 6) Increment the number from the stock
                    dojo.html.set(stockToken.firstElementChild, parseInt(stockToken.firstElementChild.textContent) + 1);
                    // 7) Unhide the stock token if it the first token of that color
                    if (dojo.hasClass(stockToken.id, "tokenhidden")) {
                        dojo.removeClass(stockToken.id, "tokenhidden");
                    }
                    // 8) Update stock total
                    this.updateTotal("stock", this.player_id);
                    this.updatePlayerBoardTokens("stock", this.player_id);
                    this.updateTooltips("stock", this.player_id);
                });
                anim.play();
            }
            this.updatePlayerBoardTokens("bet", this.player_id);
        },

        onPlaceSmallBlind: function() {
            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if(!this.checkAction('placeSmallBlind')) return;

            var colors = ["white", "blue", "red", "green", "black"];
            
            var valuesString = "";
            colors.forEach(color => {
                // Retrieve HTML elements corresponding to tokens in both the player's stock and his betting area
                var stockToken = $("token" + color + "_" + this.player_id);
                var betToken = $("bettoken" + color + "_" + this.player_id);
                // Extract number of token from HTML element and build a string to pass to the server
                valuesString += stockToken.firstElementChild.textContent + ";";
                valuesString += betToken.firstElementChild.textContent + ";";
            });

            this.ajaxcall("/texasholdem/texasholdem/smallBlind.html", { 
                lock: true,
                tokens: valuesString
            }, this, function(result) {}, function(is_error) {});
        },

        onPlaceBigBlind: function() {
            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if(!this.checkAction('placeBigBlind')) return;

            var colors = ["white", "blue", "red", "green", "black"];
            
            var valuesString = "";
            colors.forEach(color => {
                // Retrieve HTML elements corresponding to tokens in both the player's stock and his betting area
                var stockToken = $("token" + color + "_" + this.player_id);
                var betToken = $("bettoken" + color + "_" + this.player_id);
                // Extract number of token from HTML element and build a string to pass to the server
                valuesString += stockToken.firstElementChild.textContent + ";";
                valuesString += betToken.firstElementChild.textContent + ";";
            });

            this.ajaxcall("/texasholdem/texasholdem/bigBlind.html", { 
                lock: true,
                tokens: valuesString
            }, this, function(result) {}, function(is_error) {});
        },

        onCheck: function() {
            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if(!this.checkAction('placeBet')) return;

            var colors = ["white", "blue", "red", "green", "black"];
            
            var valuesString = "";
            colors.forEach(color => {
                // Retrieve HTML elements corresponding to tokens in both the player's stock and his betting area
                var stockToken = $("token" + color + "_" + this.player_id);
                var betToken = $("bettoken" + color + "_" + this.player_id);
                // Extract number of token from HTML element and build a string to pass to the server
                valuesString += stockToken.firstElementChild.textContent + ";";
                valuesString += betToken.firstElementChild.textContent + ";";
            });

            this.ajaxcall("/texasholdem/texasholdem/check.html", { 
                lock: true,
                tokens: valuesString
            }, this, function(result) {}, function(is_error) {});
        },

        onCall: function() {
            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if(!this.checkAction('placeBet')) return;

            var colors = ["white", "blue", "red", "green", "black"];
            
            var valuesString = "";
            colors.forEach(color => {
                // Retrieve HTML elements corresponding to tokens in both the player's stock and his betting area
                var stockToken = $("token" + color + "_" + this.player_id);
                var betToken = $("bettoken" + color + "_" + this.player_id);
                // Extract number of token from HTML element and build a string to pass to the server
                valuesString += stockToken.firstElementChild.textContent + ";";
                valuesString += betToken.firstElementChild.textContent + ";";
            });

            this.ajaxcall("/texasholdem/texasholdem/call.html", { 
                lock: true,
                tokens: valuesString
            }, this, function(result) {}, function(is_error) {});
        },

        onRaise: function() {
            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if(!this.checkAction('placeBet')) return;

            var betmodeSlider = $('betmode');

            if (!betmodeSlider.checked) {
                // Use modal to choose the bet amount

                // Create the new dialog over the play zone.
                this.raiseDlg = new ebg.popindialog();
                this.raiseDlg.create('chooseRaise');
                this.raiseDlg.setTitle(_("Choose Amount"));
                this.raiseDlg.setMaxWidth(500); // Optional

                var html = this.format_block('jstpl_choose_raise_dialog', {
                    INPUT_LABEL: _("Amount"),
                    RAISE_BUTTON_LABEL: _("OK"),
                    CANCEL_BUTTON_LABEL: _("Cancel")
                });  
                
                // Show the dialog
                this.raiseDlg.setContent(html);
                this.raiseDlg.show();
                

                dojo.connect($('raisebutton'), 'onclick', this, function(evt) {
                    var raiseAmount = $("raiseAmount").value;
                    this.raiseBy(raiseAmount);
                    evt.preventDefault();
                    this.raiseDlg.destroy();
                });

                dojo.connect($('cancelbutton'), 'onclick', this, function(evt) {
                    evt.preventDefault();
                    this.raiseDlg.destroy();
                });

                dojo.connect($('chooseRaise'), 'onkeypress', this, function(evt) {
                    var keycode = (evt.keyCode ? evt.keyCode : evt.which);
                    if (keycode == '13') {
                        $('raisebutton').click()
                    } else if (keycode == '27') {
                        $('cancelbutton').click()
                    }
                });
            } else {
                // Click on chips to define the bet amount
                var colors = ["white", "blue", "red", "green", "black"];
            
                var valuesString = "";
                colors.forEach(color => {
                    // Retrieve HTML elements corresponding to tokens in both the player's stock and his betting area
                    var stockToken = $("token" + color + "_" + this.player_id);
                    var betToken = $("bettoken" + color + "_" + this.player_id);
                    // Extract number of token from HTML element and build a string to pass to the server
                    valuesString += stockToken.firstElementChild.textContent + ";";
                    valuesString += betToken.firstElementChild.textContent + ";";
                });

                this.ajaxcall("/texasholdem/texasholdem/raise.html", { 
                    lock: true, 
                    tokens: valuesString
                }, this, function(result) {}, function(is_error) {});
            }
        },

        onRaiseBy: function(event) {
            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if(!this.checkAction('placeBet')) return;

            var raiseValue = parseInt(event.target.innerText.replace(/Raise by /, "").replace(/Bet /, ""));

            this.raiseBy(raiseValue);
        },

        onFold: function() {
            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if(!this.checkAction('fold')) return;

            var colors = ["white", "blue", "red", "green", "black"];
            
            var valuesString = "";
            colors.forEach(color => {
                // Retrieve HTML elements corresponding to tokens in both the player's stock and his betting area
                var stockToken = $("token" + color + "_" + this.player_id);
                var betToken = $("bettoken" + color + "_" + this.player_id);
                // Extract number of token from HTML element and build a string to pass to the server
                valuesString += stockToken.firstElementChild.textContent + ";";
                valuesString += betToken.firstElementChild.textContent + ";";
            });

            this.ajaxcall("/texasholdem/texasholdem/fold.html", { 
                lock: true, 
                player_id: this.player_id, 
                tokens: valuesString
             }, this, function(result) {}, function(is_error) {});
        },

        onAllIn: function() {
            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if(!this.checkAction('placeBet')) return;

            this.confirmationDialog( _('Are you sure you want to go all in?'), dojo.hitch( this, function() {
                this.confirmAllIn();
            } ) ); 
            return;
        },

        confirmAllIn: function() {
            var colors = ["white", "blue", "red", "green", "black"];
            
            var valuesString = "";
            colors.forEach(color => {
                // Retrieve HTML elements corresponding to tokens in both the player's stock and his betting area
                var stockToken = $("token" + color + "_" + this.player_id);
                var betToken = $("bettoken" + color + "_" + this.player_id);
                // Extract number of token from HTML element and build a string to pass to the server
                var currentStock = parseInt(stockToken.firstElementChild.textContent);
                var currentBet = parseInt(betToken.firstElementChild.textContent);

                // After the All In, all token are placed in the betting area
                valuesString += 0 + ";"; // No token left in stock
                valuesString += (currentStock + currentBet).toString() + ";"; // All in betting area

                // Simulate clicks on stock token
                for (var i = 0; i < currentStock; i++) {
                    stockToken.click();
                }
            });

            this.ajaxcall("/texasholdem/texasholdem/allIn.html", { 
                lock: true, 
                tokens: valuesString
            }, this, function(result) {}, function(is_error) {});
        },

        onMakeChange: function() {
            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if(!this.checkAction('makeChange')) return;

            // Create the new dialog over the play zone.
            this.myDlg = new ebg.popindialog();
            this.myDlg.create('changeBoard');
            this.myDlg.setTitle(_("Change Board"));
            this.myDlg.setMaxWidth(500); // Optional
            
            // Create the HTML of my dialog.

            var colors = ["white", "blue", "red", "green", "black"];

            // The current player stock tokens
            var changeStockHtml = "";
            colors.forEach(color => {
                stockToken = $("token" + color + "_" + this.player_id);
                stockValue = parseInt(stockToken.firstElementChild.textContent);
                changeStockHtml += this.format_block('jstpl_change_token', {
                    TOKEN_COLOR: color,
                    TOKEN_CLASS: stockValue == 0 ? "tokendisabled changetoken" : "changetoken",
                    TOKEN_ID: "changeyourstock_" + color,
                    TEXT_CLASS: color == "black" ? "tokennumberlight" : "tokennumberdark",
                    TOKEN_NUM: stockValue
                });
            })
            // Tokens the player has chosen to give
            var changeGivenHtml = "";
            colors.forEach(color => {
                changeGivenHtml += this.format_block('jstpl_change_token', {
                    TOKEN_COLOR: color,
                    TOKEN_CLASS: "tokendisabled bettoken",
                    TOKEN_ID: "changegiventokens_" + color,
                    TEXT_CLASS: color == "black" ? "tokennumberlight" : "tokennumberdark",
                    TOKEN_NUM: 0
                });
            })

            // Propositions of combinations of tokens in exchange of the token given by the player
            var changeReceivedHtml = "";
                // Lowest token value proposition
            changeReceivedHtml += this.format_block('jstpl_change_proposed_change', {
                PROPOSED_CHANGE_ID: "receivedtokenslowest",
                TOKENS: colors.reduce((changeReceivedTokensHtml, color) => {
                    changeReceivedTokensHtml += this.format_block('jstpl_change_token', {
                        TOKEN_COLOR: color,
                        TOKEN_CLASS: "tokendisabled bettoken",
                        TOKEN_ID: "changereceivedtokenslowest_" + color,
                        TEXT_CLASS: color == "black" ? "tokennumberlight" : "tokennumberdark",
                        TOKEN_NUM: 0
                    });
                    return changeReceivedTokensHtml;
                }, "")
            });
                // Mixed token value proposition
            changeReceivedHtml += this.format_block('jstpl_change_proposed_change', {
                PROPOSED_CHANGE_ID: "receivedtokensmixed",
                TOKENS: colors.reduce((changeReceivedTokensHtml, color) => {
                    changeReceivedTokensHtml += this.format_block('jstpl_change_token', {
                        TOKEN_COLOR: color,
                        TOKEN_CLASS: "tokendisabled bettoken",
                        TOKEN_ID: "changereceivedtokensmixed_" + color,
                        TEXT_CLASS: color == "black" ? "tokennumberlight" : "tokennumberdark",
                        TOKEN_NUM: 0
                    });
                    return changeReceivedTokensHtml;
                }, "")
            });
                // Highest token value proposition
            changeReceivedHtml += this.format_block('jstpl_change_proposed_change', {
                PROPOSED_CHANGE_ID: "receivedtokenshighest",
                TOKENS: colors.reduce((changeReceivedTokensHtml, color) => {
                    changeReceivedTokensHtml += this.format_block('jstpl_change_token', {
                        TOKEN_COLOR: color,
                        TOKEN_CLASS: "tokendisabled bettoken",
                        TOKEN_ID: "changereceivedtokenshighest_" + color,
                        TEXT_CLASS: color == "black" ? "tokennumberlight" : "tokennumberdark",
                        TOKEN_NUM: 0
                    });
                    return changeReceivedTokensHtml;
                }, "")
            });

            var html = this.format_block('jstpl_change_board', {
                STOCK_TITLE: _("Your stock <br>(click to give)"),
                CHANGE_STOCK: changeStockHtml,
                GIVEN_TITLE: _("What you give"),
                CHANGE_GIVEN: changeGivenHtml,
                RECEIVED_TITLE: _("What you get (select your choice)"),
                CHANGE_RECEIVED: changeReceivedHtml,
                CHANGE_BUTTON_LABEL: _("Change"),
                CANCEL_BUTTON_LABEL: _("Cancel")
            });  
            
            // Show the dialog
            this.myDlg.setContent(html);
            this.myDlg.show();
            
            // Define onclick events for token clicks and button clicks
            colors.forEach(color => {
                // Clicks on stock token
                dojo.connect($('changeyourstock_' + color), 'onclick', this, function(evt) {
                    this.onChangeTokenClick("stock", color);
                    evt.preventDefault();
                });
                // Clicks on given token
                dojo.connect($('changegiventokens_' + color), 'onclick', this, function(evt) {
                    this.onChangeTokenClick("given", color);
                    evt.preventDefault();
                });
            });
            // Clicks on received tokens
            dojo.query(".changereceivedproposition").connect('onclick', this, function(evt) {
                var clickedProposition = evt.currentTarget;

                var isAllDisabled = true;
                var tokens = clickedProposition.children;
                for (var i = 0; i < tokens.length; i++) {
                    if (!dojo.hasClass(tokens[i], "tokendisabled")) {
                        isAllDisabled = false;
                        break;
                    }
                }

                if (!isAllDisabled && !dojo.hasClass(clickedProposition, "highlighted-border")) {
                    // Clear previous selection
                    dojo.removeClass("proposed_change_receivedtokenslowest", "highlighted-border");
                    dojo.removeClass("proposed_change_receivedtokensmixed", "highlighted-border");
                    dojo.removeClass("proposed_change_receivedtokenshighest", "highlighted-border");

                    // Highlight selected proposition
                    dojo.addClass(clickedProposition, "highlighted-border");
                }
            });
            

            dojo.connect($('changebutton'), 'onclick', this, function(evt) {
                this.onValidateChange();
                evt.preventDefault();
            });

            dojo.connect($('cancelbutton'), 'onclick', this, function(evt) {
                evt.preventDefault();
                this.myDlg.destroy();
            });
        },

        onChangeTokenClick: function(type, color) {
            if (type == "stock") {
                var sourceToken = $('changeyourstock_' + color);
                var destinationToken =  $('changegiventokens_' + color);
                var slidingTokenClass = "token token" + color + " changetoken behind";
            } else {
                var sourceToken = $('changegiventokens_' + color);
                var destinationToken =  $('changeyourstock_' + color);
                var slidingTokenClass = "token token" + color + " bettoken behind";
            }

            // Retrieve current number of tokens of that color in the source area
            var currentSource = parseInt(sourceToken.firstElementChild.textContent);

            // Check if at least one token of that color has been bet
            if (currentSource > 0) {
                currentSource--;

                // Move a token from the source to the destination
                // 1) Decrement number of token in the source area and disable the token if it was the last one
                dojo.html.set(sourceToken.firstElementChild, currentSource);
                if (currentSource <= 0) {
                    dojo.addClass(sourceToken.id, "tokendisabled");
                }
                // 2) Place the visual of a token on top of the token source area
                dojo.place('<div class = "' + slidingTokenClass + '" id = "sliding' + type + 'token_' + color + '_' + currentSource + '"></div>', sourceToken.parentElement.id);
                // 3) Slide the token from the source area to the destination area
                var anim = this.slideToObject("sliding" + type + "token_" + color + "_" + currentSource, destinationToken.id);
                dojo.connect(anim, 'onEnd', function(node) {
                    // 4) Destroy token visual used for the animation
                    dojo.destroy(node);
                });
                // 5) Increment the number from the destination
                dojo.html.set(destinationToken.firstElementChild, parseInt(destinationToken.firstElementChild.textContent) + 1);
                // 6) Unhide the destination token if it the first token of that color
                if (dojo.hasClass(destinationToken.id, "tokendisabled")) {
                    dojo.removeClass(destinationToken.id, "tokendisabled");
                }

                // 7) Trigger function to compute the tokens to receive in exchange of the ones in the given area.
                this.updateReceivedTokens();

                anim.play();
            }
        },

        onValidateChange: function() {
            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if(!this.checkAction('makeChange')) return;

            var colors = ["white", "blue", "red", "green", "black"];

            // Get series of tokens currently selected
            var selectedProposition = dojo.query(".changereceivedproposition.highlighted-border")[0];

            var valuesString = "";
            if (selectedProposition != null) {
                var selectedPropositionType = selectedProposition.id.replace(/proposed_change_receivedtokens/, "");
            
                colors.forEach(color => {
                    // Retrieve HTML elements corresponding to tokens in both the player's stock and his betting area
                    var givenToken = $("changegiventokens_" + color);
                    var receivedToken = $("changereceivedtokens" + selectedPropositionType + "_" + color);
                    // Extract number of token from HTML element and build a string to pass to the server
                    valuesString += givenToken.firstElementChild.textContent + ";";
                    valuesString += receivedToken.firstElementChild.textContent + ";";
                });
            } else {
                valuesString = "0;0;0;0;0;0;0;0;0;0;";
            }
            
            this.ajaxcall("/texasholdem/texasholdem/makeChange.html", { 
                lock: true, 
                tokens: valuesString
             }, this, function(result) {}, function(is_error) {});
        },

        onAutoblindsChange: function(event) {
            this.ajaxcall("/texasholdem/texasholdem/autoblinds.html", { 
                lock: true, 
                playerId: this.player_id,
                isAutoblinds: event.target.checked ? 1 : 0
             }, this, function(result) {}, function(is_error) {});
        },

        onBetmodeChange: function(event) {
            if (this.isCurrentPlayerActive()) {
                // Update choose raise button
            if (event.target.checked) {
                dojo.html.set($('raise'), _('Confirm raise'));
                this.removeTooltip('raise');
                this.addTooltip('raise', 'You need can move chips to your betting area by clicking on them', _('This will raise by the amount of chips you currently have in your betting area'));
            } else {
                dojo.html.set($('raise'), _('Choose raise'));
                this.addTooltip('raise', '', _('This will open a sub-window to let you choose a raise amount'));
            }
            }

            // If the player has already moved chips to the betting area during the 
            // current turn, they are moved back to his stock if he disables manual betting
            var colors = ["white", "blue", "red", "green", "black"];
            
            var valuesString = "";
            colors.forEach(color => {
                // Retrieve HTML elements corresponding to tokens in both the player's stock and his betting area
                var stockToken = $("token" + color + "_" + this.player_id);
                var betToken = $("bettoken" + color + "_" + this.player_id);
                // Extract number of token from HTML element and build a string to pass to the server
                valuesString += stockToken.firstElementChild.textContent + ";";
                valuesString += betToken.firstElementChild.textContent + ";";
            });

            this.ajaxcall("/texasholdem/texasholdem/betmode.html", { 
                lock: true, 
                playerId: this.player_id,
                isBetManual: event.target.checked ? 1 : 0,
                tokens: valuesString
             }, this, function(result) {}, function(is_error) {});
        },
        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your texasholdem.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            // TODO: here, associate your game notifications with local methods
            
            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            // 

            dojo.subscribe('increaseBlinds', this, "notif_increaseBlinds");
            dojo.subscribe('dealCardsPlayer', this, "notif_dealCardsPlayer");
            this.notifqueue.setSynchronous('dealCardsPlayer', 1000);

            dojo.subscribe('moveTokens', this, "notif_moveTokens");
            this.notifqueue.setSynchronous('moveTokens', 2000);
            dojo.subscribe('changeRequired', this, "notif_changeRequired");
            this.notifqueue.setSynchronous('changeRequired', 2000);

            dojo.subscribe('betPlaced', this, "notif_betPlaced");
            this.notifqueue.setSynchronous('betPlaced', 2000);
            dojo.subscribe('fold', this, "notif_fold");
            this.notifqueue.setSynchronous('fold', 1000);

            dojo.subscribe('makeChange', this, "notif_makeChange");
            this.notifqueue.setSynchronous('makeChange', 2000);

            dojo.subscribe('revealNextCard', this, "notif_revealNextCard");
            this.notifqueue.setSynchronous('revealNextCard', 3000);

            dojo.subscribe('moveBetToPot', this, "notif_moveBetToPot");
            this.notifqueue.setSynchronous('moveBetToPot', 3000);
            dojo.subscribe('revealHands', this, "notif_revealHands");
            this.notifqueue.setSynchronous('revealHands', 3000);
            dojo.subscribe('announceCombo', this, "notif_announceCombo");
            this.notifqueue.setSynchronous('announceCombo', 3000);

            dojo.subscribe('announceWinner', this, "notif_announceWinner");
            this.notifqueue.setSynchronous('announceWinner', 6000);
            dojo.subscribe('movePotToStock', this, "notif_movePotToStock");
            this.notifqueue.setSynchronous('movePotToStock', 3000);

            dojo.subscribe('updateScores', this, "notif_updateScores");
            dojo.subscribe('discardAllCards', this, "notif_discardAllCards");
            this.notifqueue.setSynchronous('discardAllCards', 1000);
            dojo.subscribe('eliminatePlayer', this, "notif_eliminatePlayer");
            this.notifqueue.setSynchronous('eliminatePlayer', 1000);

            dojo.subscribe('changeActivePlayer', this, "notif_changeActivePlayer");
            dojo.subscribe('changeDealer', this, "notif_changeDealer");

            dojo.subscribe('autoblindsChange', this, "notif_autoblindsChange");
            dojo.subscribe('betmodeChange', this, "notif_betmodeChange");
            dojo.subscribe('announceAction', this, "notif_announceAction");
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
        /*
        Example:
        
        notif_cardPlayed: function( notif )
        {
            console.log( 'notif_cardPlayed' );
            console.log( notif );
            
            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
            
            // TODO: play the card in the user interface.
        },    
        
        */
        notif_increaseBlinds: function(notif) {
            console.log('notif_increaseBlinds');

            var smallBlind = notif.args.small_blind;
            var bigBlind = notif.args.big_blind;
            this.showMessage(_("The blinds are increased to " + smallBlind + "/" + bigBlind), "info")
        },

        notif_dealCardsPlayer: function(notif) {
            console.log('notif_dealCardsPlayer');

            // Display cards in hand
            var hands = notif.args.hands;
            var playerToIsLeftCard = [];
            for (var i in notif.args.players) {
                playerToIsLeftCard.push({
                    id: notif.args.players[i].player_id,
                    isLeftCard: true
                });
            }

            for (var cardId in hands) {
                var card = hands[cardId];
                var player = playerToIsLeftCard.filter(player => player.id == card.location_arg)[0];

                dojo.place(this.format_block('jstpl_card', {
                    CARD_LOCATION_CLASS: player.isLeftCard == true ? "cardinhand cardinhandleft" : "cardinhand cardinhandright",
                    CARD_VISIBILITY_CLASS: card.location_arg == this.player_id ? "cardvisible" : "cardhidden",
                    // For cards in another player's hand, set background position to 0, otherwise one could know the card by inspecting the HTML element style
                    BACKGROUND_POSITION_LEFT_PERCENTAGE: card.location_arg == this.player_id ? -100 * (card.type_arg - 2) : 0,
                    BACKGROUND_POSITION_TOP_PERCENTAGE: card.location_arg == this.player_id ? -100 * (card.type - 1) : 0
                }), 'playertablecards_' + card.location_arg);

                if (player.isLeftCard) {
                    player.isLeftCard = false;
                }
            }

            // Display cards on table
            // Flop
            for (var cardId in [0, 1, 2]) {
                dojo.place(this.format_block('jstpl_card', {
                    CARD_LOCATION_CLASS: "cardflop",
                    CARD_VISIBILITY_CLASS: "cardhidden",
                    BACKGROUND_POSITION_LEFT_PERCENTAGE: 0,
                    BACKGROUND_POSITION_TOP_PERCENTAGE: 0
                }), 'flop'+(parseInt(cardId)+1));
            }
            // Turn
            dojo.place(this.format_block('jstpl_card', {
                CARD_LOCATION_CLASS: "cardturn",
                CARD_VISIBILITY_CLASS: "cardhidden",
                BACKGROUND_POSITION_LEFT_PERCENTAGE: 0,
                BACKGROUND_POSITION_TOP_PERCENTAGE: 0
            }), 'turn');
            // River
            dojo.place(this.format_block('jstpl_card', {
                CARD_LOCATION_CLASS: "cardriver",
                CARD_VISIBILITY_CLASS: "cardhidden",
                BACKGROUND_POSITION_LEFT_PERCENTAGE: 0,
                BACKGROUND_POSITION_TOP_PERCENTAGE: 0
            }), 'river');
        },

        notif_moveTokens: function(notif) {
            console.log('notif_moveTokens');

            var from = notif.args.from;
            var to = notif.args.to;
            
            var colors = ["white", "blue", "red", "green", "black"];
        
            colors.forEach(color => {
                var sourceToken, destinationToken;
                var playerTable;
                var fromPlayerId, toPlayerId;
                var slidingTokenClass;

                // Retrieve HTML elements corresponding to tokens in the source
                if (from == "pot") {
                    fromPlayerId = null;
                    sourceToken = $("token" + color + "_table");
                    slidingTokenClass = "token token" + color + " behind";
                    sourceTotalName = "pot";
                } else if (from.includes("stock")) {
                    fromPlayerId = from.replace("stock_", "");
                    sourceToken = $("token" + color + "_" + fromPlayerId);
                    playerTable = $("playertablecards_" + fromPlayerId).parentElement;
                    slidingTokenClass = "token token" + color + " behind";
                    sourceTotalName = "stock";
                } else if (from.includes("bet")) {
                    fromPlayerId = from.replace("bet_", "");
                    sourceToken = $("bettoken" + color + "_" + fromPlayerId);
                    playerTable = $("playertablecards_" + fromPlayerId).parentElement;
                    slidingTokenClass = "token token" + color + " bettoken behind";
                    sourceTotalName = "bet";
                }
                // Retrieve HTML elements corresponding to tokens in the destination
                if (to == "pot") {
                    toPlayerId = null;
                    destinationToken = $("token" + color + "_table");
                    destinationTotalName = "pot";
                } else if (to.includes("stock")) {
                    toPlayerId = to.replace("stock_", "");
                    destinationToken = $("token" + color + "_" + toPlayerId);
                    destinationTotalName = "stock";
                } else if (to.includes("bet")) {
                    toPlayerId = to.replace("bet_", "");
                    destinationToken = $("bettoken" + color + "_" + toPlayerId);
                    destinationTotalName = "bet";
                }

                // Extract number of token from HTML element
                var numLoops = parseInt(notif.args.token_diff[color]);
                numLoops = Math.min(10, numLoops); // Cap the number of token animated to 10

                // Animate tokens slide from table tokens to player's stock
                for (var i = 0; i < numLoops; i++) {
                    // 1) Place the visual of a token on top of the source token pile
                    dojo.place('<div class = "' + slidingTokenClass + '" id = "slidingtoken_' + color + '_' + from + '_' + to + '_' + i + '"></div>', sourceToken.parentElement.id);
                    // 2) Slide the token from the pot to the player's stock
                    var [targetTopValue, targetLeftValue] = this.getSlideTopAndLeftProperties(playerTable, sourceToken, destinationToken);

                    var anim = dojo.fx.slideTo({
                            node: 'slidingtoken_' + color + '_' + from + '_' + to + '_' + i,
                            top: targetTopValue.toString(),
                            left: targetLeftValue.toString(),
                            units: "px",
                            duration: 500 + i * 70
                    });
                    dojo.connect(anim, 'onEnd', this, function(node) {
                        // 3) Destroy token visual used for the animation
                        dojo.destroy(node);
                        // 4) Set the number of destination tokens expected for this color at the end of all token animations
                        dojo.html.set(destinationToken.firstElementChild, notif.args.to_tokens[color]);
                        // 5) Update destination total
                        this.updateTotal(destinationTotalName, toPlayerId);
                        if (destinationTotalName == "stock" || destinationTotalName == "bet") {
                            this.updatePlayerBoardTokens(destinationTotalName, toPlayerId);
                            this.updateTooltips(destinationTotalName, toPlayerId);
                        } else {
                            this.updateTooltips("pot", null);
                        }
                        // 6) Unhide the player's stock token if it is the first token of that color
                        if (dojo.hasClass(destinationToken.id, "tokenhidden") && notif.args.to_tokens[color] > 0) {
                            dojo.removeClass(destinationToken.id, "tokenhidden");
                        }
                        // 7)  Set the number of source tokens expected for this color at the end of all token animations
                        dojo.html.set(sourceToken.firstElementChild, notif.args.from_tokens[color]);
                        // 8) Update source total
                        this.updateTotal(sourceTotalName, fromPlayerId);
                        if (sourceTotalName == "stock" || sourceTotalName == "bet") {
                            this.updatePlayerBoardTokens(sourceTotalName, fromPlayerId);
                            this.updateTooltips(sourceTotalName, fromPlayerId);
                        } else {
                            this.updateTooltips("pot", null);
                        }
                        // 9) Hide source token if it was the last token of that color
                        if (!dojo.hasClass(sourceToken.id, "tokenhidden") && notif.args.from_tokens[color] == 0) {
                            dojo.addClass(sourceToken.id, "tokenhidden");
                        }
                        // 10) Unhide source token if it has at least one token of that color at the end of the animation
                        if (dojo.hasClass(sourceToken.id, "tokenhidden") && notif.args.from_tokens[color] > 0) {
                            dojo.removeClass(sourceToken.id, "tokenhidden");
                        }
                    });
                    anim.play();
                }
            });
        },

        notif_changeRequired: function(notif) {
            console.log('notif_changeRequired');

            var bigColor = notif.args.color; // Color of high value token
            var smallColor = notif.args.small_color; // Color of low value token
            var numSmallTokens = notif.args.num_small_tokens;
            var from = notif.args.from;

            // Retrieve HTML elements corresponding to tokens in the source
            if (from == "pot") {
                sourceGivenToken = $("token" + bigColor + "_table");
                sourceReceivedToken = $("token" + smallColor + "_table");
                playerTable = null;
                slidingTokenClass = "token token" + bigColor + " behind";
            } else if (from.includes("stock")) {
                fromPlayerId = from.replace("stock_", "");
                sourceGivenToken = $("token" + bigColor + "_" + fromPlayerId);
                sourceReceivedToken = $("token" + smallColor + "_" + fromPlayerId);
                playerTable = $("playertablecards_" + fromPlayerId).parentElement;
                slidingTokenClass = "token token" + bigColor + " behind";
            } else if (from.includes("bet")) {
                fromPlayerId = from.replace("bet_", "");
                sourceGivenToken = $("bettoken" + bigColor + "_" + fromPlayerId);
                sourceReceivedToken = $("bettoken" + smallColor + "_" + fromPlayerId);
                playerTable = $("playertablecards_" + fromPlayerId).parentElement;
                slidingTokenClass = "token token" + bigColor + " bettoken behind";
            }
            var changeGivenToken = $("changetoken" + bigColor);
            var changeReceivedToken = $("changetoken" + smallColor);
            var currentGivenTokens = parseInt(sourceGivenToken.firstElementChild.textContent);
            var currentReceivedTokens = parseInt(sourceReceivedToken.firstElementChild.textContent);

            // Animate token going to the change board
            dojo.html.set(sourceGivenToken.firstElementChild, currentGivenTokens - 1);
            if (currentGivenTokens - 1 <= 0) {
                dojo.addClass(sourceGivenToken.id, "tokenhidden");
            }
            // Place the visual of a token on top of the source of change token pile
            dojo.place('<div class = "' + slidingTokenClass + '" id = "slidingtoken_' + bigColor + '"></div>', sourceGivenToken.parentElement.id);
            // Slide the token from the source of change to the change area
            var [targetTopValue, targetLeftValue] = this.getSlideTopAndLeftProperties(playerTable, sourceGivenToken, changeGivenToken);

            var anim = dojo.fx.slideTo({
                    node: 'slidingtoken_' + bigColor,
                    top: targetTopValue.toString(),
                    left: targetLeftValue.toString(),
                    units: "px",
                    duration: 1000
            });
            dojo.connect(anim, 'onEnd', function(node) {
                // Destroy token visual used for the animation
                dojo.destroy(node);
            });
            anim.play();

            // Animate tokens coming from the change board
            for (var i = 0; i < numSmallTokens; i++) {
                currentReceivedTokens++;
                // Move a token from the change area to the source of change
                // Place the visual of a token on top of the token change area pile
                dojo.place('<div class = "token token' + smallColor + ' changetoken behind" id = "slidingchangetoken_' + smallColor + '_' + currentReceivedTokens + '"></div>', "changetable");
                // Slide the token from the change area to the source of change
                    // Identify target and source absolute position in the DOM
                var sourcePos = dojo.position(changeReceivedToken.id);
                var targetPos = dojo.position(sourceReceivedToken.id);
                    // Compute the value of the left and top properties based on the translation to do
                var targetTopValue = targetPos.y - sourcePos.y + dojo.getStyle(changeReceivedToken.id, "top");
                var targetLeftValue = targetPos.x - sourcePos.x + dojo.getStyle(changeReceivedToken.id, "left");
                var anim = dojo.fx.slideTo({
                        node: 'slidingchangetoken_' + smallColor + '_' + currentReceivedTokens,
                        top: targetTopValue.toString(),
                        left: targetLeftValue.toString(),
                        units: "px",
                        duration: 1000 + i * 70 * (20 / (20 + numSmallTokens))
                });
                dojo.connect(anim, 'onEnd', function(node) {
                    // Destroy token visual used for the animation
                    dojo.destroy(node);
                    // Increment the number from the source of change
                    dojo.html.set(sourceReceivedToken.firstElementChild, currentReceivedTokens);
                    // Unhide the source of change token if it the first token of that color
                    if (dojo.hasClass(sourceReceivedToken.id, "tokenhidden")) {
                        dojo.removeClass(sourceReceivedToken.id, "tokenhidden");
                    }
                });
                anim.play();
            }

        },

        notif_betPlaced: function(notif) {
            console.log('notif_betPlaced');

            // Avoid animating again player's own bet
            if (notif.args.player_id != this.player_id || notif.args.show_all) {
                var diffStock = notif.args.diff_stock;
                var playerTable = $("playertablecards_" + notif.args.player_id).parentElement;

                Object.keys(diffStock).forEach(color => {
                    var tokenDiff = diffStock[color];

                    var stockToken = $("token" + color + "_" + notif.args.player_id);
                    var betToken = $("bettoken" + color + "_" + notif.args.player_id);

                    // Retrieve current number of tokens of that color in stock
                    var currentStock = parseInt(stockToken.firstElementChild.textContent);
                    var currentBet = parseInt(betToken.firstElementChild.textContent);
                    
                    // Token goes from player's stock to the betting area
                    if (tokenDiff < 0) {
                        console.log("Token goes from player's stock to the betting area");
                        for (var i = 0; i > tokenDiff; i--) {
                            currentStock--;
                            currentBet++;
                            // Move a token from the player's stock to the betting area
                            // 1) Decrement number of token in stock and hide the token if it was the last one
                            dojo.html.set(stockToken.firstElementChild, currentStock);
                            // 2) Update stock total
                            this.updateTotal("stock", notif.args.player_id);
                            this.updatePlayerBoardTokens("stock", notif.args.player_id);
                            this.updateTooltips("stock", notif.args.player_id);
                            if (currentStock <= 0) {
                                dojo.addClass(stockToken.id, "tokenhidden");
                            }
                            // 3) Place the visual of a token on top of the token stock pile
                            dojo.place('<div class = "token token' + color + ' behind" id = "slidingstocktoken_' + color + '_' + currentStock + '"></div>', "playertabletokens_" + notif.args.player_id);
                            // 4) Slide the token from the stock to the betting area
                            var [targetTopValue, targetLeftValue] = this.getSlideTopAndLeftProperties(playerTable, stockToken, betToken);

                            var anim = dojo.fx.slideTo({
                                    node: 'slidingstocktoken_' + color + '_' + currentStock,
                                    top: targetTopValue.toString(),
                                    left: targetLeftValue.toString(),
                                    units: "px",
                                    duration: 500 - i * 70 * (20 / (20 - tokenDiff))
                            });
                            dojo.connect(anim, 'onEnd', this, function(node) {
                                // 5) Destroy token visual used for the animation
                                dojo.destroy(node);
                                // 6) Increment the number from the betting area
                                dojo.html.set(betToken.firstElementChild, currentBet);
                                // 7) Update bet total
                                this.updateTotal("bet", notif.args.player_id);
                                this.updatePlayerBoardTokens("bet", notif.args.player_id);
                                this.updateTooltips("bet", notif.args.player_id);
                                // 8) Unhide the betting area token if it the first token of that color
                                if (dojo.hasClass(betToken.id, "tokenhidden")) {
                                    dojo.removeClass(betToken.id, "tokenhidden");
                                }
                            });
                            anim.play();
                        }
                    // Token goes from player's betting area to the stock
                    } else if (tokenDiff > 0) {
                        console.log("Token goes from player's betting area to the stock");
                        for (var i = 0; i < tokenDiff; i++) {
                            currentStock++;
                            currentBet--;
                            // Move a token from the player's betting area to the stock
                            // 1) Decrement number of token in betting area and hide the token if it was the last one
                            dojo.html.set(betToken.firstElementChild, currentBet);
                            // 2) Update bet total
                            this.updateTotal("bet", notif.args.player_id);
                            this.updatePlayerBoardTokens("bet", notif.args.player_id);
                            this.updateTooltips("bet", notif.args.player_id);
                            if (currentBet <= 0) {
                                dojo.addClass(betToken.id, "tokenhidden");
                            }
                            // 3) Place the visual of a token on top of the token betting area pile
                            dojo.place('<div class = "token token' + color + ' bettoken behind" id = "slidingbettoken_' + color + '_' + currentBet + '"></div>', "bettingarea_" + notif.args.player_id);
                            // 4) Slide the token from the betting area to the stock
                            var [targetTopValue, targetLeftValue] = this.getSlideTopAndLeftProperties(playerTable, betToken, stockToken);
                            var anim = dojo.fx.slideTo({
                                    node: 'slidingbettoken_' + color + '_' + currentBet,
                                    top: targetTopValue.toString(),
                                    left: targetLeftValue.toString(),
                                    units: "px",
                                    duration: 500 + i * 70 * (20 / (20 + tokenDiff))
                            });
                            dojo.connect(anim, 'onEnd', this, function(node) {
                                // 5) Destroy token visual used for the animation
                                dojo.destroy(node);
                                // 6) Increment the number from the player's stock
                                dojo.html.set(stockToken.firstElementChild, currentStock);
                                // 7) Update stock total
                                this.updateTotal("stock", notif.args.player_id);
                                this.updatePlayerBoardTokens("stock", notif.args.player_id);
                                this.updateTooltips("stock", notif.args.player_id);
                                // 8) Unhide the stock token if it the first token of that color
                                if (dojo.hasClass(stockToken.id, "tokenhidden")) {
                                    dojo.removeClass(stockToken.id, "tokenhidden");
                                }
                            });
                            anim.play();
                        }
                    }
                });
            }
        },

        notif_fold: function(notif) {
            console.log('notif_fold');

            var playerCards = $("playertablecards_" + notif.args.player_id);

            var anim1 = dojo.fx.slideTo({
                node: playerCards,
                top: dojo.getStyle(playerCards.id, "top") - 200,
                left: dojo.getStyle(playerCards.id, "left"),
                units: "px",
                duration: 1500
            });
            anim2 = dojo.fadeOut({
                node: playerCards,
                duration: 1500
            });

            dojo.connect(anim2, 'onEnd', function(node) {
                for (var i = 0; i < 2; i++) {
                    var cardNode = node.children[0];
                    dojo.destroy(cardNode);
                }
                // Slide back the empty playertablecards div
                dojo.fx.slideTo({
                    node: playerCards,
                    top: dojo.getStyle(playerCards.id, "top") + 200,
                    left: dojo.getStyle(playerCards.id, "left"),
                    units: "px",
                    duration: 5
                }).play();
                dojo.fadeIn({
                    node: playerCards,
                    duration: 5
                }).play();
                dojo.place('<div class = "folded"><span>Folded</span></div>', 'playertablecards_' + notif.args.player_id);
            });
            anim1.play();
            anim2.play();
        },

        notif_makeChange: function(notif) {
            console.log('notif_makeChange');

            // Close change modal
            if (notif.args.player_id == this.player_id && this.myDlg) {
                this.myDlg.destroy();
            }

            var diffStock = notif.args.diff_stock;
            var playerTable = $("playertablecards_" + notif.args.player_id).parentElement;

            Object.keys(diffStock).forEach(color => {
                var tokenDiff = diffStock[color];

                var stockToken = $("token" + color + "_" + notif.args.player_id);
                var changeToken = $("changetoken" + color);

                // Retrieve current number of tokens of that color in stock
                var currentStock = parseInt(stockToken.firstElementChild.textContent);
                
                // Token goes from player's stock to the change area
                if (tokenDiff < 0) {
                    for (var i = 0; i > tokenDiff; i--) {
                        currentStock--;
                        // Move a token from the player's stock to the change area
                        // 1) Decrement number of token in stock and hide the token if it was the last one
                        dojo.html.set(stockToken.firstElementChild, currentStock);
                        if (currentStock <= 0) {
                            dojo.addClass(stockToken.id, "tokenhidden");
                        }
                        // 2) Place the visual of a token on top of the token stock pile
                        dojo.place('<div class = "token token' + color + ' behind" id = "slidingstocktoken_' + color + '_' + currentStock + '"></div>', "playertabletokens_" + notif.args.player_id);
                        // 3) Slide the token from the stock to the change area
                        var [targetTopValue, targetLeftValue] = this.getSlideTopAndLeftProperties(playerTable, stockToken, changeToken);

                        var anim = dojo.fx.slideTo({
                                node: 'slidingstocktoken_' + color + '_' + currentStock,
                                top: targetTopValue.toString(),
                                left: targetLeftValue.toString(),
                                units: "px",
                                duration: 1000 - i * 70 * (20 / (20 - tokenDiff))
                        });
                        dojo.connect(anim, 'onEnd', function(node) {
                            // 4) Destroy token visual used for the animation
                            dojo.destroy(node);
                        });
                        anim.play();
                    }
                // Token goes from the change area to the player's stock
                } else if (tokenDiff > 0) {
                    for (var i = 0; i < tokenDiff; i++) {
                        currentStock++;
                        // Move a token from the change area to the player's stock
                        // 1) Place the visual of a token on top of the token change area pile
                        dojo.place('<div class = "token token' + color + ' changetoken behind" id = "slidingchangetoken_' + color + '_' + currentStock + '"></div>', "changetable");
                        // 2) Slide the token from the change area to the player's stock
                            // 2.1) Identify target and source absolute position in the DOM
                        var sourcePos = dojo.position(changeToken.id);
                        var targetPos = dojo.position(stockToken.id);
                            // 2.2) Compute the value of the left and top properties based on the translation to do
                        var targetTopValue = targetPos.y - sourcePos.y + dojo.getStyle(changeToken.id, "top");
                        var targetLeftValue = targetPos.x - sourcePos.x + dojo.getStyle(changeToken.id, "left");
                        var anim = dojo.fx.slideTo({
                                node: 'slidingchangetoken_' + color + '_' + currentStock,
                                top: targetTopValue.toString(),
                                left: targetLeftValue.toString(),
                                units: "px",
                                duration: 1000 + i * 70 * (20 / (20 + tokenDiff))
                        });
                        dojo.connect(anim, 'onEnd', this, function(node) {
                            // 3) Destroy token visual used for the animation
                            dojo.destroy(node);
                            // 4) Increment the number from the player's stock
                            dojo.html.set(stockToken.firstElementChild, currentStock);
                            this.updatePlayerBoardTokens("stock", notif.args.player_id);
                            // 5) Unhide the stock token if it the first token of that color
                            if (dojo.hasClass(stockToken.id, "tokenhidden")) {
                                dojo.removeClass(stockToken.id, "tokenhidden");
                            }
                        });
                        anim.play();
                    }
                }
                // Update player board stock tokens
                this.updatePlayerBoardTokens("stock", notif.args.player_id);
            });
        },

        notif_moveBetToPot: function(notif) {
            console.log('notif_moveBetToPot');

            // Loop over each player's betting area
            dojo.query(".bettokens").forEach(bettingArea => {
                var playerId =  bettingArea.id.replace(/bettingarea_/, "");

                var playerTable = $("playertablecards_" + playerId).parentElement;
                var colors = ["white", "blue", "red", "green", "black"];
            
                colors.forEach(color => {
                    // Retrieve HTML elements corresponding to tokens in the player's betting area
                    var betToken = $("bettoken" + color + "_" + playerId);
                    var tableToken = $("token" + color + "_table");
                    // Extract number of token from HTML element
                    var numLoops = parseInt(betToken.firstElementChild.textContent);
                    numLoops = Math.min(10, numLoops); // Cap the number of token animated to 10

                    // Animate tokens slide from betting area to table tokens
                    for (var i = 0; i < numLoops; i++) {
                        // 1) Place the visual of a token on top of the token bet pile
                        dojo.place('<div class = "token token' + color + ' bettoken behind" id = "slidingbettoken_' + color + '_' + playerId + '_' + i + '"></div>', "bettingarea_" + playerId);
                        // 2) Slide the token from the betting area to the pot
                        var [targetTopValue, targetLeftValue] = this.getSlideTopAndLeftProperties(playerTable, betToken, tableToken);

                        var anim = dojo.fx.slideTo({
                                node: 'slidingbettoken_' + color + '_' + playerId + '_' + i,
                                top: targetTopValue.toString(),
                                left: targetLeftValue.toString(),
                                units: "px",
                                duration: 500 + i * 70
                        });
                        dojo.connect(anim, 'onEnd', this, function(node) {
                            // 3) Destroy token visual used for the animation
                            dojo.destroy(node);
                            // 4) Set the number of table tokens expected for this color at the end of all token animations
                            dojo.html.set(tableToken.firstElementChild, notif.args.end_pot[color]);
                            // 5) Update pot total
                            this.updateTotal("pot");
                            this.updateTooltips("pot", null);
                            // 6) Unhide the table token if it the first token of that color
                            if (dojo.hasClass(tableToken.id, "tokenhidden")) {
                                dojo.removeClass(tableToken.id, "tokenhidden");
                            }
                            // 7) Hide betting area token and set its number to 0 (value expected at the end of all token animations)
                            dojo.html.set(betToken.firstElementChild, 0);
                            dojo.addClass(betToken.id, "tokenhidden");
                            // 8) Update bet total
                            this.updateTotal("bet", playerId);
                            this.updatePlayerBoardTokens("bet", playerId);
                            this.updateTooltips("bet", playerId);
                        });
                        anim.play();
                    }
                });
            });
        },

        notif_revealNextCard: function(notif) {
            console.log('notif_revealNextCard');

            var roundStage = notif.args.revealed_card;
            var cards = notif.args.cards;

            switch(roundStage) {
                case "flop":
                    for (var cardId in [0, 1, 2]) {
                        // Place visible card behind the facedown card
                        dojo.place(this.format_block('jstpl_card', {
                            CARD_LOCATION_CLASS: "cardflop",
                            CARD_VISIBILITY_CLASS: "cardvisible flipped-card",
                            BACKGROUND_POSITION_LEFT_PERCENTAGE: -100 * (parseInt(cards[Object.keys(cards)[cardId]].type_arg) - 2),
                            BACKGROUND_POSITION_TOP_PERCENTAGE: -100 * (parseInt(cards[Object.keys(cards)[cardId]].type) - 1)
                        }), 'flop'+(parseInt(cardId)+1));
                        // Add the flip-card class to flip both the face down and visble cards
                        dojo.addClass('flop'+(parseInt(cardId)+1), "flip-card");
                    }
                    // Remove the facedown card
                    dojo.query(".cardflop.cardhidden").forEach(node => {
                        var anim = dojo.fadeOut({
                            node: node,
                            delay: 1000,
                            duration: 10
                        });
                        dojo.connect(anim, "onEnd", function(node2) {
                            dojo.destroy(node);
                        });
                        anim.play();
                    });
                    break;
                case "turn":
                    // Place visible card behind the facedown card
                    dojo.place(this.format_block('jstpl_card', {
                        CARD_LOCATION_CLASS: "cardturn",
                        CARD_VISIBILITY_CLASS: "cardvisible flipped-card",
                        BACKGROUND_POSITION_LEFT_PERCENTAGE: -100 * (parseInt(cards[Object.keys(cards)[0]].type_arg) - 2),
                        BACKGROUND_POSITION_TOP_PERCENTAGE: -100 * (parseInt(cards[Object.keys(cards)[0]].type) - 1)
                    }), 'turn');
                    // Add the flip-card class to flip both the face down and visble cards
                    dojo.addClass('turn', "flip-card");
                    // Remove the facedown card
                    dojo.query(".cardturn.cardhidden").forEach(node => {
                        var anim = dojo.fadeOut({
                            node: node,
                            delay: 1000,
                            duration: 10
                        });
                        dojo.connect(anim, "onEnd", function(node2) {
                            dojo.destroy(node);
                        });
                        anim.play();
                    });
                    break;
                case "river":
                    // Place visible card behind the facedown card
                    dojo.place(this.format_block('jstpl_card', {
                        CARD_LOCATION_CLASS: "cardriver",
                        CARD_VISIBILITY_CLASS: "cardvisible flipped-card",
                        BACKGROUND_POSITION_LEFT_PERCENTAGE: -100 * (parseInt(cards[Object.keys(cards)[0]].type_arg) - 2),
                        BACKGROUND_POSITION_TOP_PERCENTAGE: -100 * (parseInt(cards[Object.keys(cards)[0]].type) - 1)
                    }), 'river');
                    // Add the flip-card class to flip both the face down and visble cards
                    dojo.addClass('river', "flip-card");
                    // Remove the facedown card
                    dojo.query(".cardriver.cardhidden").forEach(node => {
                        var anim = dojo.fadeOut({
                            node: node,
                            delay: 1000,
                            duration: 10
                        });
                        dojo.connect(anim, "onEnd", function(node2) {
                            dojo.destroy(node);
                        });
                        anim.play();
                    });
                    break;
            }

        },

        notif_revealHands: function(notif) {
            console.log('notif_revealHands');

            var players = notif.args.players;
            var players_combo = notif.args.players_best_combo;
            var hands = notif.args.hands;

            players.forEach(player => {
                // Reveal player's cards
                if (player.player_id != this.player_id) {
                    var playerHand = hands.filter(card => card.location_arg == player.player_id);
                    var cardLeft = playerHand[0];
                    var cardRight = playerHand[1];

                    // Place visible cards behind the facedown cards
                    dojo.place(this.format_block('jstpl_card', {
                        CARD_LOCATION_CLASS: "cardinhand cardinhandleft",
                        CARD_VISIBILITY_CLASS: "cardvisible flipped-card behind",
                        BACKGROUND_POSITION_LEFT_PERCENTAGE: -100 * (cardLeft.type_arg - 2),
                        BACKGROUND_POSITION_TOP_PERCENTAGE: -100 * (cardLeft.type - 1)
                    }), 'playertablecards_' + player.player_id);
                    dojo.place(this.format_block('jstpl_card', {
                        CARD_LOCATION_CLASS: "cardinhand cardinhandright",
                        CARD_VISIBILITY_CLASS: "cardvisible flipped-card",
                        BACKGROUND_POSITION_LEFT_PERCENTAGE: -100 * (cardRight.type_arg - 2),
                        BACKGROUND_POSITION_TOP_PERCENTAGE: -100 * (cardRight.type - 1)
                    }), 'playertablecards_' + player.player_id);

                    // Add the flip-card class to flip both the face down and visble cards
                    dojo.addClass('playertablecards_' + player.player_id, "flip-card");
                }
            });
            // Remove the facedown cards
            dojo.query(".cardinhand.cardhidden").forEach(node => {
                var anim = dojo.fadeOut({
                    node: node,
                    delay: 1000,
                    duration: 10
                });
                dojo.connect(anim, "onEnd", function(node2) {
                    dojo.destroy(node);
                });
                anim.play();
            });
        },

        notif_announceCombo: function(notif) {
            console.log('notif_announceCombo');

            var playerId = notif.args.player_id;
            var playerColor = notif.args.player_color;
            var comboCards = notif.args.player_best_combo.hand;
            var comboName = notif.args.combo_name;

            // Highlight cards constituting the combo
            dojo.query(".card.cardvisible").forEach(card => {
                var cardValue = (-parseInt(getComputedStyle(card).backgroundPositionX.slice(0, -1)) / 100) + 2;
                var cardSuit = (-parseInt(getComputedStyle(card).backgroundPositionY.slice(0, -1)) / 100) + 1;
                if (comboCards.filter(card => (card.type_arg == cardValue && card.type == cardSuit)).length > 0) {
                    dojo.style(card, {
                        "border": "3px solid",
                        "border-color": "#" + playerColor,
                        "opacity": "1"
                    });
                } else if ((card.parentElement.id.replace(/playertablecards_/, "") == playerId) || !dojo.hasClass(card, "cardinhand")) {
                    dojo.style(card, {
                        "border": "none",
                        "opacity": "0.2"
                    });
                } else {
                    dojo.style(card, {
                        "border": "none",
                        "opacity": "1"
                    });
                }
            });

            // Display text indicating the combo
            dojo.place('<span class = "combotext" id = "combotext_' + playerId + '">' + comboName + '</span>', "bettingarea_" + playerId);

        },

        notif_announceWinner: function(notif) {
            console.log('notif_announceWinner');

            var playerId = notif.args.winner_id;
            var playerColor = notif.args.winner_color;
            var comboCards = notif.args.winner_best_combo.hand;
            var comboName = notif.args.combo_name;

            // Highlight cards constituting the combo
            dojo.query(".card.cardvisible").forEach(card => {
                var cardValue = (-parseInt(getComputedStyle(card).backgroundPositionX.slice(0, -1)) / 100) + 2;
                var cardSuit = (-parseInt(getComputedStyle(card).backgroundPositionY.slice(0, -1)) / 100) + 1;
                if (comboCards.filter(card => (card.type_arg == cardValue && card.type == cardSuit)).length > 0) {
                    dojo.style(card, {
                        "border": "3px solid",
                        "border-color": "#" + playerColor,
                        "opacity": "1"
                    });
                } else if ((card.parentElement.id.replace(/playertablecards_/, "") == playerId) || !dojo.hasClass(card, "cardinhand")) {
                    dojo.style(card, {
                        "border": "none",
                        "opacity": "0.2"
                    });
                } else {
                    dojo.style(card, {
                        "border": "none",
                        "opacity": "1"
                    });
                }
            });

            // Make the winner playertable blink
            var playerTable = $("bettingarea_" + playerId).parentElement;

            dojo.fx.chain([
                dojo.fadeOut({node: playerTable, delay: 3000}),
                dojo.fadeIn({node: playerTable}),
                dojo.fadeOut({node: playerTable}),
                dojo.fadeIn({node: playerTable})
            ]).play();
        },

        notif_movePotToStock: function(notif) {
            console.log('notif_movePotToStock');

            var playerId = notif.args.winner_id;

            var playerTable = $("playertablecards_" + playerId).parentElement;
            var colors = ["white", "blue", "red", "green", "black"];
        
            colors.forEach(color => {
                // Retrieve HTML elements corresponding to tokens in the player's stock
                var stockToken = $("token" + color + "_" + playerId);
                var tableToken = $("token" + color + "_table");
                // Extract number of token from HTML element
                var numLoops = parseInt(tableToken.firstElementChild.textContent);
                numLoops = Math.min(10, numLoops); // Cap the number of token animated to 10

                // Animate tokens slide from table tokens to player's stock
                for (var i = 0; i < numLoops; i++) {
                    // 1) Place the visual of a token on top of the token bet pile
                    dojo.place('<div class = "token token' + color + ' behind" id = "slidingtabletoken_' + color + '_' + playerId + '_' + i + '"></div>', "previousroundtokens");
                    // 2) Slide the token from the pot to the player's stock
                        // 2.1) Identify target and source absolute position in the DOM
                    var sourcePos = dojo.position(tableToken.id);
                    var targetPos = dojo.position(stockToken.id);
                        // 2.2) Compute the value of the left and top properties based on the translation to do
                    var targetTopValue = targetPos.y - sourcePos.y + dojo.getStyle(tableToken.id, "top");
                    var targetLeftValue = targetPos.x - sourcePos.x + dojo.getStyle(tableToken.id, "left");

                    var anim = dojo.fx.slideTo({
                            node: 'slidingtabletoken_' + color + '_' + playerId + '_' + i,
                            top: targetTopValue.toString(),
                            left: targetLeftValue.toString(),
                            units: "px",
                            duration: 500 + i * 70
                    });
                    dojo.connect(anim, 'onEnd', this, function(node) {
                        // 3) Destroy token visual used for the animation
                        dojo.destroy(node);
                        // 4) Set the number of table tokens expected for this color at the end of all token animations
                        dojo.html.set(stockToken.firstElementChild, notif.args.end_player_stock[color]);
                        // 8) Update stock total
                        this.updateTotal("stock", playerId);
                        this.updatePlayerBoardTokens("stock", playerId);
                        this.updateTooltips("stock", playerId);
                        // 9) Unhide the player's stock token if it the first token of that color
                        if (dojo.hasClass(stockToken.id, "tokenhidden")) {
                            dojo.removeClass(stockToken.id, "tokenhidden");
                        }
                        // 10) Hide table token and set its number to 0 (value expected at the end of all token animations)
                        dojo.html.set(tableToken.firstElementChild, 0);
                        dojo.addClass(tableToken.id, "tokenhidden");
                        // 11) Update pot total
                        this.updateTotal("pot");
                        this.updateTooltips("pot", null);
                    });
                    anim.play();
                }
            });
        },

        notif_updateScores: function(notif) {
            console.log('notif_updateScores');

            // Update players' scores
            for (var player_id in notif.args.players_tokens_value) {
                this.scoreCtrl[player_id].toValue(notif.args.players_tokens_value[player_id].stock);
            }
        },

        notif_discardAllCards: function(notif) {
            console.log('notif_discardAllCards');

            dojo.query(".card").forEach(node => {
                this.fadeOutAndDestroy(node);
            });

            dojo.query(".combotext").forEach(node => {
                this.fadeOutAndDestroy(node);
            });

            dojo.query(".folded").forEach(node => {
                this.fadeOutAndDestroy(node);
            });

            // Remove the flip-card class to "unflip" all cards containers
            dojo.removeClass('flop1', "flip-card");
            dojo.removeClass('flop2', "flip-card");
            dojo.removeClass('flop3', "flip-card");
            dojo.removeClass('turn', "flip-card");
            dojo.removeClass('river', "flip-card");

            dojo.query(".playertablecards").forEach(node => {
                dojo.removeClass(node, "flip-card");
            });
        },

        notif_eliminatePlayer: function(notif) {
            console.log('notif_eliminatePlayer');

            var playerbettotal = $("playerbettotal_" + notif.args.eliminated_player);
            var playerstocktotal = $("playerstocktotal_" + notif.args.eliminated_player);
            this.fadeOutAndDestroy(playerbettotal, 500);
            this.fadeOutAndDestroy(playerstocktotal, 500);
        },

        notif_changeActivePlayer: function(notif) {
            console.log('notif_changeActivePlayer');

            var playerTable = $("playertablecards_" + notif.args.player_id).parentElement;

            dojo.query(".highlighted-border").removeClass("highlighted-border");
            dojo.addClass(playerTable, "highlighted-border");
        },

        notif_changeDealer: function(notif) {
            console.log('notif_changeDealer');

            var currentDealerButton = dojo.query(".dealer-button:not(.dealer-button-hidden)")[0];
            var newDealerButton = $("dealer_button_" + notif.args.dealer_id);
            var playerTable = currentDealerButton.parentElement;

            // Place the visual of a dealer button on top of its current position
            dojo.place('<div class="dealer-button" id="slidingbutton"></div>', currentDealerButton.parentElement);
            // Slide the button from the current dealer to the next one
                // Identify target and source absolute position in the DOM
                var [targetTopValue, targetLeftValue] = this.getSlideTopAndLeftProperties(playerTable, currentDealerButton, newDealerButton);

            var anim = dojo.fx.slideTo({
                    node: 'slidingbutton',
                    top: targetTopValue.toString(),
                    left: targetLeftValue.toString(),
                    units: "px",
                    duration: 1000
            });
            dojo.connect(anim, 'onEnd', function(node) {
                dojo.destroy(node);
                dojo.removeClass(newDealerButton.id, "dealer-button-hidden");
            });
            anim.play();
            dojo.addClass(currentDealerButton.id, "dealer-button-hidden");
        },

        notif_autoblindsChange: function(notif) {
            console.log('notif_autoblindsChange');
            this.showMessage(_("Autoblinds configuration change applied"), "info");
        },

        notif_betmodeChange: function(notif) {
            console.log('notif_betmodeChange');
            this.showMessage(_("Bet mode change applied"), "info");
        },

        notif_announceAction: function(notif) {
            console.log('notif_announceAction');
            var playerId = notif.args.player_id;

            if (playerId != this.player_id) {
                var message = notif.args.message;

                this.showBubble("overall_player_board_" + playerId, _(message), 0, 5000);
            }
        }
   });             
});
