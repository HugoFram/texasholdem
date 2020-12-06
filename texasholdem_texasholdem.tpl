{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- texasholdem implementation : © <Your name here> <Your email address here>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    texasholdem_texasholdem.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->


<div id = "table">
    <!-- BEGIN player -->
    <div class="playertable whiteblock playertable_{DIR}">
        <div class = "bettokens" id = "bettingarea_{PLAYER_ID}">
            <div class = "token tokenwhite bettoken" id = "bettokenwhite_{PLAYER_ID}">
                
            </div>
            <div class = "token tokenblue bettoken" id = "bettokenblue_{PLAYER_ID}">

            </div>
            <div class = "token tokenred bettoken" id = "bettokenred_{PLAYER_ID}">

            </div>
            <div class = "token tokengreen bettoken" id = "bettokengreen_{PLAYER_ID}">

            </div>
            <div class = "token tokenblack bettoken" id = "bettokenblack_{PLAYER_ID}">

            </div>
        </div>
        <div class="playertablename" style="color:#{PLAYER_COLOR}">
            {PLAYER_NAME}
        </div>
        <div class="playertablecards" id="playertablecards_{PLAYER_ID}">

        </div>
        <div class="playertabletokens" id="playertabletokens_{PLAYER_ID}">
            <div class = "token tokenwhite" id = "tokenwhite_{PLAYER_ID}">
                
            </div>
            <div class = "token tokenblue" id = "tokenblue_{PLAYER_ID}">

            </div>
            <div class = "token tokenred" id = "tokenred_{PLAYER_ID}">

            </div>
            <div class = "token tokengreen" id = "tokengreen_{PLAYER_ID}">

            </div>
            <div class = "token tokenblack" id = "tokenblack_{PLAYER_ID}">

            </div>
        </div>
    </div>
    <!-- END player -->
    <div class = "playingarea">
        <div class = "tabletokens" id = "previousroundtokens">
            <div class = "token tokenwhite" id = "tokenwhite_table">

            </div>
            <div class = "token tokenblue" id = "tokenblue_table">

            </div>
            <div class = "token tokenred"  id = "tokenred_table">

            </div>
            <div class = "token tokengreen tokenhidden" id = "tokengreen_table">

            </div>
            <div class = "token tokenblack" id = "tokenblack_table">

            </div>
        </div>
        <div class = "tablecards" id = "river">
            
        </div>
    </div>
</div>

<script type="text/javascript">

// Javascript HTML templates

/*
// Example:
var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${MY_ITEM_ID}"></div>';

*/

// TEXT_CLASS = tokennumberlight, tokennumberdark
var jstpl_player_stock_token='<div class = "tokennumber ${TEXT_CLASS}">${TOKEN_NUM}</div>';
var jstpl_player_bet_token='<div class = "tokennumber ${TEXT_CLASS}">${TOKEN_NUM}</div>';
var jstpl_table_token='<div class = "tokennumber ${TEXT_CLASS}">${TOKEN_NUM}</div>';

// CARD_LOCATION_CLASS = cardflop, cardturn, cardriver, cardinhand cardinhandleft, cardinhand cardinhandright
// CARD_VISIBILITY_CLASS = cardvisible, cardhidden
// If cardhidden --> background-position must be 0% 0%
var jstpl_card='<div class = "card ${CARD_LOCATION_CLASS} ${CARD_VISIBILITY_CLASS}" style = "background-position: ${BACKGROUND_POSITION_LEFT_PERCENTAGE}% ${BACKGROUND_POSITION_TOP_PERCENTAGE}%;"></div>';
</script>  

{OVERALL_GAME_FOOTER}
