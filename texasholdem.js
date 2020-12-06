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
                    if (stockTokens > 0) {
                        if (dojo.hasClass('token' + color + '_' + player_id, "tokenhidden")) {
                            dojo.removeClass('token' + color + '_' + player_id, "tokenhidden");
                        }
                        dojo.place(this.format_block('jstpl_player_stock_token', {
                            TEXT_CLASS: color == "white" ? "tokennumberdark" : "tokennumberlight",
                            TOKEN_NUM: stockTokens
                        }), 'token' + color + '_' + player_id);
                    } else {
                        dojo.addClass('token' + color + '_' + player_id, "tokenhidden");
                    }

                    // Bet tokens
                    if (betTokens > 0) {
                        if (dojo.hasClass('bettoken' + color + '_' + player_id, "tokenhidden")) {
                            dojo.removeClass('bettoken' + color + '_' + player_id, "tokenhidden");
                        }
                        dojo.place(this.format_block('jstpl_player_bet_token', {
                            TEXT_CLASS: color == "white" ? "tokennumberdark" : "tokennumberlight",
                            TOKEN_NUM: betTokens
                        }), 'bettoken' + color + '_' + player_id);
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

                if (tokenNum > 0) {
                    if (dojo.hasClass('token' + color + '_table', "tokenhidden")) {
                        dojo.removeClass('token' + color + '_table', "tokenhidden");
                    }
                    dojo.place(this.format_block('jstpl_table_token', {
                        TEXT_CLASS: color == "white" ? "tokennumberdark" : "tokennumberlight",
                        TOKEN_NUM: tokenNum
                    }), 'token' + color + '_table');
                } else {
                    dojo.addClass('token' + color + '_table', "tokenhidden");
                }
            }

            // Display cards in hand
            var hands = gamedatas.hands;
            var playerToIsLeftCard = [];
            for (var i in gamedatas.players) {
                playerToIsLeftCard.push({
                    id: gamedatas.players[i].id,
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
            var isFlopShown = Object.keys(gamedatas.cardsflop).length > 0;
            for (var cardId in [0, 1, 2]) {

                dojo.place(this.format_block('jstpl_card', {
                    CARD_LOCATION_CLASS: "cardflop",
                    CARD_VISIBILITY_CLASS: isFlopShown ? "cardvisible" : "cardhidden",
                    BACKGROUND_POSITION_LEFT_PERCENTAGE: isFlopShown ? -100 * (gamedatas.cardsflop[Object.keys(gamedatas.cardsflop)[cardId]].type_arg - 2) : 0,
                    BACKGROUND_POSITION_TOP_PERCENTAGE: isFlopShown ? -100 * (gamedatas.cardsflop[Object.keys(gamedatas.cardsflop)[cardId]].type - 1) : 0
                }), 'river');
            }
            // Turn
            var isTurnShown = Object.keys(gamedatas.cardturn).length > 0;
            dojo.place(this.format_block('jstpl_card', {
                CARD_LOCATION_CLASS: "cardturn",
                CARD_VISIBILITY_CLASS: isTurnShown ? "cardvisible" : "cardhidden",
                BACKGROUND_POSITION_LEFT_PERCENTAGE: isTurnShown ? -100 * (gamedatas.cardturn.type_arg - 2) : 0,
                BACKGROUND_POSITION_TOP_PERCENTAGE: isTurnShown ? -100 * (gamedatas.cardturn.type - 1) : 0
            }), 'river');
            // River
            var isRiverShown = Object.keys(gamedatas.cardriver).length > 0;
            dojo.place(this.format_block('jstpl_card', {
                CARD_LOCATION_CLASS: "cardturn",
                CARD_VISIBILITY_CLASS: isRiverShown ? "cardvisible" : "cardhidden",
                BACKGROUND_POSITION_LEFT_PERCENTAGE: isRiverShown ? -100 * (gamedatas.cardriver.type_arg - 2) : 0,
                BACKGROUND_POSITION_TOP_PERCENTAGE: isRiverShown ? -100 * (gamedatas.cardriver.type - 1) : 0
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
                // Create the token number div if it does not exist
                if (betToken.children.length == 0) {
                    dojo.place(this.format_block('jstpl_player_bet_token', {
                        TEXT_CLASS: color == "white" ? "tokennumberdark" : "tokennumberlight",
                        TOKEN_NUM: 0
                    }), betToken.id);
                }
                currentStock--;

                // Move a token from the player's stock to the betting area
                // 1) Decrement number of token in stock and hide the token if it was the last one
                dojo.html.set(stockToken.firstElementChild, currentStock);
                if (currentStock <= 0) {
                    dojo.addClass(stockToken.id, "tokenhidden");
                }
                // 2) Place the visual of a token on top of the token stock pile
                dojo.place('<div class = "token token' + color + '" id = "slidingstocktoken_' + color + '_' + currentStock + '"></div>', "playertabletokens_" + this.player_id);
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

            // Check if at least one token of that color has been bet
            if (!dojo.hasClass(betToken.id, "tokenhidden")) {
                // Retrieve current number of tokens of that color in the betting area
                var currentBet = parseInt(betToken.firstElementChild.textContent);

                currentBet--;

                // Move a token from the player's betting area to the stock
                // 1) Decrement number of token in betting area and hide the token if it was the last one
                dojo.html.set(betToken.firstElementChild, currentBet);
                if (currentBet <= 0) {
                    dojo.addClass(betToken.id, "tokenhidden");
                }
                // 2) Place the visual of a token on top of the token betting area pile
                dojo.place('<div class = "token token' + color + ' bettoken" id = "slidingbettoken_' + color + '_' + currentBet + '"></div>', "bettingarea_" + this.player_id);
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
   });             
});
