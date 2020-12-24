/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * texasholdem implementation : © <Your name here> <Your email address here>
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
            
            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                         
                // TODO: Setting up players boards if needed

                var tokenColors = ["white", "blue", "red", "green", "black"];

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
                    }), 'token' + color + '_' + player_id);
                    if (stockTokens > 0) {
                        if (dojo.hasClass('token' + color + '_' + player_id, "tokenhidden")) {
                            dojo.removeClass('token' + color + '_' + player_id, "tokenhidden");
                        }
                    } else {
                        dojo.addClass('token' + color + '_' + player_id, "tokenhidden");
                    }

                    // Bet tokens
                    dojo.place(this.format_block('jstpl_player_bet_token', {
                        TEXT_CLASS: color == "white" ? "tokennumberdark" : "tokennumberlight",
                        TOKEN_NUM: betTokens
                    }), 'bettoken' + color + '_' + player_id);
                    if (betTokens > 0) {
                        if (dojo.hasClass('bettoken' + color + '_' + player_id, "tokenhidden")) {
                            dojo.removeClass('bettoken' + color + '_' + player_id, "tokenhidden");
                        }
                    } else {
                        dojo.addClass('bettoken' + color + '_' + player_id, "tokenhidden");
                    }
                });

                if (player_id == this.player_id) {
                    // Add relevant onclick event to all immediate children of the element with id playertabletokens_{player_id}
                    dojo.query('#playertabletokens_' + player_id + ' > *').connect('onclick', this, 'onStockTokenClicked');
                    // Add relevant onclick event to all immediate children of the element with id bettingarea_{player_id}
                    dojo.query('#bettingarea_' + player_id + ' > *').connect('onclick', this, 'onBetTokenClicked');
                }

            }
            
            // TODO: Set up your game interface here, according to "gamedatas"

            // Display tokens bet at previous round stages
            var tableTokens = gamedatas.tokensontable;
            for (var i in tableTokens) {
                var color = tableTokens[i].token_color;
                var tokenNum  = tableTokens[i].token_number;

                dojo.place(this.format_block('jstpl_table_token', {
                    TEXT_CLASS: color == "white" ? "tokennumberdark" : "tokennumberlight",
                    TOKEN_NUM: tokenNum
                }), 'token' + color + '_table');

                if (tokenNum > 0) {
                    if (dojo.hasClass('token' + color + '_table', "tokenhidden")) {
                        dojo.removeClass('token' + color + '_table', "tokenhidden");
                    }
                } else {
                    dojo.addClass('token' + color + '_table', "tokenhidden");
                }
            }

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
                        this.addActionButton('place_small_blind', _('Place small blind'), 'onPlaceBet'); 
                        break;

                    case 'bigBlind':
                        this.addActionButton('place_big_blind', _('Place big blind'), 'onPlaceBet'); 
                        break;

                    case 'playerTurn':
                        this.addActionButton('place_bet', _('Place bet'), 'onPlaceBet'); 
                        this.addActionButton('all_in', _('All in'), 'onAllIn'); 
                        this.addActionButton('fold', _('Fold'), 'onFold');
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
            if(!this.checkAction('placeBet')) return;

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
                if (currentStock <= 0) {
                    dojo.addClass(stockToken.id, "tokenhidden");
                }
                // 2) Place the visual of a token on top of the token stock pile
                dojo.place('<div class = "token token' + color + ' behind" id = "slidingstocktoken_' + color + '_' + currentStock + '"></div>', "playertabletokens_" + this.player_id);
                // 3) Slide the token from the stock to the betting area
                var anim = this.slideToObject('slidingstocktoken_' + color + '_' + currentStock, betToken.id);
                dojo.connect(anim, 'onEnd', function(node) {
                    // 4) Destroy token visual used for the animation
                    dojo.destroy(node);
                    // 5) Increment the number from the betting area
                    dojo.html.set(betToken.firstElementChild, parseInt(betToken.firstElementChild.textContent) + 1);
                    // 6) Unhide the betting area token if it the first token of that color
                    if (dojo.hasClass(betToken.id, "tokenhidden")) {
                        dojo.removeClass(betToken.id, "tokenhidden");
                    }
                });
                anim.play();
            }
        },

        onBetTokenClicked: function(event) {
            console.log("onBetTokenClicked");
            
            // Preventing default browser reaction
            dojo.stopEvent(event);

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if(!this.checkAction('placeBet')) return;

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
                if (currentBet <= 0) {
                    dojo.addClass(betToken.id, "tokenhidden");
                }
                // 2) Place the visual of a token on top of the token betting area pile
                dojo.place('<div class = "token token' + color + ' bettoken behind" id = "slidingbettoken_' + color + '_' + currentBet + '"></div>', "bettingarea_" + this.player_id);
                // 3) Slide the token from the betting area to the stock
                var anim = this.slideToObject('slidingbettoken_' + color + '_' + currentBet, stockToken.id);
                dojo.connect(anim, 'onEnd', function(node) {
                    // 4) Destroy token visual used for the animation
                    dojo.destroy(node);
                    // 5) Increment the number from the stock
                    dojo.html.set(stockToken.firstElementChild, parseInt(stockToken.firstElementChild.textContent) + 1);
                    // 6) Unhide the stock token if it the first token of that color
                    if (dojo.hasClass(stockToken.id, "tokenhidden")) {
                        dojo.removeClass(stockToken.id, "tokenhidden");
                    }
                });
                anim.play();
            }
        },

        onPlaceBet: function() {
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

            this.ajaxcall("/texasholdem/texasholdem/placeBet.html", { 
                lock: true, 
                tokens: valuesString
             }, this, function(result) {}, function(is_error) {});
        },

        onFold: function() {
            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if(!this.checkAction('fold')) return;

            this.ajaxcall("/texasholdem/texasholdem/fold.html", { 
                lock: true, 
                player_id: this.player_id,
             }, this, function(result) {}, function(is_error) {});
        },

        onAllIn: function() {
            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if(!this.checkAction('placeBet')) return;

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

            this.ajaxcall("/texasholdem/texasholdem/placeBet.html", { 
                lock: true, 
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

            dojo.subscribe('dealCards', this, "notif_dealCards");

            dojo.subscribe('betPlaced', this, "notif_betPlaced");
            this.notifqueue.setSynchronous('betPlaced', 2000);
            dojo.subscribe('fold', this, "notif_fold");
            this.notifqueue.setSynchronous('fold', 1000);

            dojo.subscribe('revealNextCard', this, "notif_revealNextCard");

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

            dojo.subscribe('discardAllCards', this, "notif_discardAllCards");
            this.notifqueue.setSynchronous('discardAllCards', 1000);
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

        notif_dealCards: function(notif) {
            console.log('notif_dealCards');

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

        notif_betPlaced: function(notif) {
            console.log('notif_betPlaced');

            // Avoid animating again player's own bet
            if (notif.args.player_id != this.player_id) {
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
                        for (var i = 0; i > tokenDiff; i--) {
                            currentStock--;
                            currentBet++;
                            // Move a token from the player's stock to the betting area
                            // 1) Decrement number of token in stock and hide the token if it was the last one
                            dojo.html.set(stockToken.firstElementChild, currentStock);
                            if (currentStock <= 0) {
                                dojo.addClass(stockToken.id, "tokenhidden");
                            }
                            // 2) Place the visual of a token on top of the token stock pile
                            dojo.place('<div class = "token token' + color + ' behind" id = "slidingstocktoken_' + color + '_' + currentStock + '"></div>', "playertabletokens_" + notif.args.player_id);
                            // 3) Slide the token from the stock to the betting area
                                // 3.1) Identify target and source absolute position in the DOM
                            var sourcePos = dojo.position(stockToken.id);
                            var targetPos = dojo.position(betToken.id);
                                // 3.2) Compute the value of the left and top properties based on the translation to do
                            var targetTopValue, targetLeftValue;
                            if (dojo.hasClass(playerTable, "playertable_SW") || dojo.hasClass(playerTable, "playertable_S") || dojo.hasClass(playerTable, "playertable_SE")) {
                                targetTopValue = targetPos.y - sourcePos.y + dojo.getStyle(stockToken.id, "top");
                                targetLeftValue = targetPos.x - sourcePos.x + dojo.getStyle(stockToken.id, "left");
                            } else if (dojo.hasClass(playerTable, "playertable_W")) {
                                targetTopValue = -(targetPos.x - sourcePos.x) + dojo.getStyle(stockToken.id, "top");
                                targetLeftValue = targetPos.y - sourcePos.y + dojo.getStyle(stockToken.id, "left");
                            } else if (dojo.hasClass(playerTable, "playertable_NW") || dojo.hasClass(playerTable, "playertable_N") || dojo.hasClass(playerTable, "playertable_NE")) {
                                targetTopValue = -(targetPos.y - sourcePos.y) + dojo.getStyle(stockToken.id, "top");
                                targetLeftValue = -(targetPos.x - sourcePos.x) + dojo.getStyle(stockToken.id, "left");
                            } else if (dojo.hasClass(playerTable, "playertable_E")) {
                                targetTopValue = targetPos.x - sourcePos.x + dojo.getStyle(stockToken.id, "top");
                                targetLeftValue = -(targetPos.y - sourcePos.y) + dojo.getStyle(stockToken.id, "left");
                            }

                            var anim = dojo.fx.slideTo({
                                    node: 'slidingstocktoken_' + color + '_' + currentStock,
                                    top: targetTopValue.toString(),
                                    left: targetLeftValue.toString(),
                                    units: "px",
                                    duration: 500 - i * 70 * (20 / (20 - tokenDiff))
                            });
                            dojo.connect(anim, 'onEnd', function(node) {
                                // 4) Destroy token visual used for the animation
                                dojo.destroy(node);
                                // 5) Increment the number from the betting area
                                dojo.html.set(betToken.firstElementChild, currentBet);
                                // 6) Unhide the betting area token if it the first token of that color
                                if (dojo.hasClass(betToken.id, "tokenhidden")) {
                                    dojo.removeClass(betToken.id, "tokenhidden");
                                }
                            });
                            anim.play();
                        }
                    // Token goes from player's betting area to the stock
                    } else if (tokenDiff > 0) {
                        for (var i = 0; i < tokenDiff; i++) {
                            currentStock++;
                            currentBet--;
                            // Move a token from the player's stock to the betting area
                            // 1) Decrement number of token in stock and hide the token if it was the last one
                            dojo.html.set(betToken.firstElementChild, currentBet);
                            if (currentBet <= 0) {
                                dojo.addClass(betToken.id, "tokenhidden");
                            }
                            // 2) Place the visual of a token on top of the token stock pile
                            dojo.place('<div class = "token token' + color + ' bettoken behind" id = "slidingbettoken_' + color + '_' + currentBet + '"></div>', "bettingarea_" + notif.args.player_id);
                            // 3) Slide the token from the stock to the betting area
                                // 3.1) Identify target and source absolute position in the DOM
                            var sourcePos = dojo.position(betToken.id);
                            var targetPos = dojo.position(stockToken.id);
                                // 3.2) Compute the value of the left and top properties based on the translation to do
                            var targetTopValue, targetLeftValue;
                            if (dojo.hasClass(playerTable, "playertable_SW") || dojo.hasClass(playerTable, "playertable_S") || dojo.hasClass(playerTable, "playertable_SE")) {
                                targetTopValue = targetPos.y - sourcePos.y + dojo.getStyle(betToken.id, "top");
                                targetLeftValue = targetPos.x - sourcePos.x + dojo.getStyle(betToken.id, "left");
                            } else if (dojo.hasClass(playerTable, "playertable_W")) {
                                targetTopValue = -(targetPos.x - sourcePos.x) + dojo.getStyle(betToken.id, "top");
                                targetLeftValue = targetPos.y - sourcePos.y + dojo.getStyle(betToken.id, "left");
                            } else if (dojo.hasClass(playerTable, "playertable_NW") || dojo.hasClass(playerTable, "playertable_N") || dojo.hasClass(playerTable, "playertable_NE")) {
                                targetTopValue = -(targetPos.y - sourcePos.y) + dojo.getStyle(betToken.id, "top");
                                targetLeftValue = -(targetPos.x - sourcePos.x) + dojo.getStyle(betToken.id, "left");
                            } else if (dojo.hasClass(playerTable, "playertable_E")) {
                                targetTopValue = targetPos.x - sourcePos.x + dojo.getStyle(betToken.id, "top");
                                targetLeftValue = -(targetPos.y - sourcePos.y) + dojo.getStyle(betToken.id, "left");
                            }
                            var anim = dojo.fx.slideTo({
                                    node: 'slidingbettoken_' + color + '_' + currentBet,
                                    top: targetTopValue.toString(),
                                    left: targetLeftValue.toString(),
                                    units: "px",
                                    duration: 500 + i * 70 * (20 / (20 + tokenDiff))
                            });
                            dojo.connect(anim, 'onEnd', function(node) {
                                // 4) Destroy token visual used for the animation
                                dojo.destroy(node);
                                // 5) Increment the number from the betting area
                                dojo.html.set(stockToken.firstElementChild, currentStock);
                                // 6) Unhide the betting area token if it the first token of that color
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
                            // 2.1) Identify target and source absolute position in the DOM
                        var sourcePos = dojo.position(betToken.id);
                        var targetPos = dojo.position(tableToken.id);
                            // 2.2) Compute the value of the left and top properties based on the translation to do
                        var targetTopValue, targetLeftValue;
                        if (dojo.hasClass(playerTable, "playertable_SW") || dojo.hasClass(playerTable, "playertable_S") || dojo.hasClass(playerTable, "playertable_SE")) {
                            targetTopValue = targetPos.y - sourcePos.y + dojo.getStyle(betToken.id, "top");
                            targetLeftValue = targetPos.x - sourcePos.x + dojo.getStyle(betToken.id, "left");
                        } else if (dojo.hasClass(playerTable, "playertable_W")) {
                            targetTopValue = -(targetPos.x - sourcePos.x) + dojo.getStyle(betToken.id, "top");
                            targetLeftValue = targetPos.y - sourcePos.y + dojo.getStyle(betToken.id, "left");
                        } else if (dojo.hasClass(playerTable, "playertable_NW") || dojo.hasClass(playerTable, "playertable_N") || dojo.hasClass(playerTable, "playertable_NE")) {
                            targetTopValue = -(targetPos.y - sourcePos.y) + dojo.getStyle(betToken.id, "top");
                            targetLeftValue = -(targetPos.x - sourcePos.x) + dojo.getStyle(betToken.id, "left");
                        } else if (dojo.hasClass(playerTable, "playertable_E")) {
                            targetTopValue = targetPos.x - sourcePos.x + dojo.getStyle(betToken.id, "top");
                            targetLeftValue = -(targetPos.y - sourcePos.y) + dojo.getStyle(betToken.id, "left");
                        }

                        var anim = dojo.fx.slideTo({
                                node: 'slidingbettoken_' + color + '_' + playerId + '_' + i,
                                top: targetTopValue.toString(),
                                left: targetLeftValue.toString(),
                                units: "px",
                                duration: 500 + i * 70
                        });
                        dojo.connect(anim, 'onEnd', function(node) {
                            // 3) Destroy token visual used for the animation
                            dojo.destroy(node);
                            // 4) Set the number of table tokens expected for this color at the end of all token animations
                            dojo.html.set(tableToken.firstElementChild, notif.args.end_pot[color]);
                            // 5) Unhide the table token if it the first token of that color
                            if (dojo.hasClass(tableToken.id, "tokenhidden")) {
                                dojo.removeClass(tableToken.id, "tokenhidden");
                            }
                            // 6) Hide betting area token and set its number to 0 (value expected at the end of all token animations)
                            dojo.html.set(betToken.firstElementChild, 0);
                            dojo.addClass(betToken.id, "tokenhidden");
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
                    dojo.place('<div class = "token token' + color + ' token behind" id = "slidingtabletoken_' + color + '_' + playerId + '_' + i + '"></div>', "previousroundtokens");
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
                    dojo.connect(anim, 'onEnd', function(node) {
                        // 3) Destroy token visual used for the animation
                        dojo.destroy(node);
                        // 4) Set the number of table tokens expected for this color at the end of all token animations
                        dojo.html.set(stockToken.firstElementChild, notif.args.end_player_stock[color]);
                        // 5) Unhide the player's stock token if it the first token of that color
                        if (dojo.hasClass(stockToken.id, "tokenhidden")) {
                            dojo.removeClass(stockToken.id, "tokenhidden");
                        }
                        // 6) Hide table token and set its number to 0 (value expected at the end of all token animations)
                        dojo.html.set(tableToken.firstElementChild, 0);
                        dojo.addClass(tableToken.id, "tokenhidden");
                    });
                    anim.play();
                }
            });
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
        }
   });             
});
