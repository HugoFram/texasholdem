{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- texasholdem implementation : © <Hugo Frammery> <hugo@frammery.com>
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

<div class="changetable whiteblock" id = "changetable">
    <div class = "token tokenwhite changetoken changetoken-vertical" id = "changetokenwhite">
        <span class = "changetokenvalue">{CHANGE_TOKEN_VALUE_WHITE}</span>
    </div>
    <div class = "token tokenblue changetoken changetoken-vertical" id = "changetokenblue">
        <span class = "changetokenvalue changetoken-vertical">{CHANGE_TOKEN_VALUE_BLUE}</span>
    </div>
    <div class = "token tokenred changetoken changetoken-vertical" id = "changetokenred">
        <span class = "changetokenvalue">{CHANGE_TOKEN_VALUE_RED}</span>
    </div>
    <div class = "token tokengreen changetoken changetoken-vertical" id = "changetokengreen">
        <span class = "changetokenvalue">{CHANGE_TOKEN_VALUE_GREEN}</span>
    </div>
    <div class = "token tokenblack changetoken changetoken-vertical" id = "changetokenblack">
        <span class = "changetokenvalue">{CHANGE_TOKEN_VALUE_BLACK}</span>
    </div>
</div>
<div id = "table">
    <!-- BEGIN player -->
    <div class="playertable whiteblock playertable_{DIR}">
        <div class = "dealer-button dealer-button-hidden orientation_{DIR}" id = "dealer_button_{PLAYER_ID}"></div>
        <div class = "bettokens orientation_{DIR}" id = "bettingarea_{PLAYER_ID}">
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
        <div class="total-badge playerbettotal orientation_{DIR}" id = "playerbettotal_{PLAYER_ID}" style = "background-color:#{PLAYER_COLOR}55">{PLAYER_BET_TOTAL}</div>
        <div class="playertablename" id = "playertablename_{PLAYER_ID}" style="color:#{PLAYER_COLOR}">
            {PLAYER_NAME}
        </div>
        <div class="total-badge playerstocktotal" id = "playerstocktotal_{PLAYER_ID}" style = "background-color:#{PLAYER_COLOR}55">{PLAYER_STOCK_TOTAL}</div>
        <div class="playertablecards" id="playertablecards_{PLAYER_ID}">

        </div>
        <div class="playertabletokens" id="playertabletokens_{PLAYER_ID}">
            <div class = "token tokenred" id = "tokenred_{PLAYER_ID}">

            </div>
            <div class = "token tokenwhite" id = "tokenwhite_{PLAYER_ID}">

            </div>
            <div class = "token tokengreen" id = "tokengreen_{PLAYER_ID}">

            </div>
            <div class = "token tokenblue" id = "tokenblue_{PLAYER_ID}">

            </div>
            <div class = "token tokenblack" id = "tokenblack_{PLAYER_ID}">

            </div>
        </div>
    </div>
    <!-- END player -->
    <div class = "playingarea">
        <div class = "tabletokens" id = "previousroundtokens">
            <div class = "token tokenred"  id = "tokenred_table">

            </div>
            <div class = "token tokenwhite" id = "tokenwhite_table">

            </div>
            <div class = "token tokengreen" id = "tokengreen_table">

            </div>
            <div class = "token tokenblue" id = "tokenblue_table">

            </div>
            <div class = "token tokenblack" id = "tokenblack_table">

            </div>
        </div>
        <div class="total-badge pottotal" id = "pottotal">0</div>
        <div class = "tablecards">
            <div id = "flop1">
                <span class = "placeholdertext">{FLOP}</span>
            </div>
            <div id = "flop2">
                <span class = "placeholdertext">{FLOP}</span>
            </div>
            <div id = "flop3">
                <span class = "placeholdertext">{FLOP}</span>
            </div>
            <div id = "turn">
                <span class = "placeholdertext">{TURN}</span>
            </div>
            <div id = "river">
                <span class = "placeholdertext">{RIVER}</span>
            </div>
        </div>
    </div>
</div>
<div class="slider-checkbox">
    <input type="checkbox" class="autoblinds" name="autoblinds" id="autoblinds">
    <label for = "autoblinds" class="label">{AUTOBLINDS_DESCRIPTION}</label>
</div>
<div class="slider-checkbox">
    <input type="checkbox" class="autocheck" name="autocheck" id="autocheck">
    <label for = "autocheck" class="label">{AUTOCHECK_DESCRIPTION}</label>
</div>
<div class="slider-checkbox">
    <input type="checkbox" class="autocall" name="autocall" id="autocall">
    <label for = "autocall" class="label">{AUTOCALL_DESCRIPTION}</label>
</div>
<div class="slider-checkbox">
    <input type="checkbox" class="confirmactions" name="confirmactions" id="confirmactions">
    <label for = "confirmactions" class="label">{CONFIRMACTIONS_DESCRIPTION}</label>
</div>
<div class="slider-checkbox">
    <input type="checkbox" class="betmode" name="betmode" id="betmode">
    <label for = "betmode" class="label">{BETMODE_DESCRIPTION}</label>
</div>
<div class="slider-checkbox">
    <input type="checkbox" class="doshowhand" name="doshowhand" id="doshowhand">
    <label for = "doshowhand" class="label">{DOSHOWHAND_DESCRIPTION}</label>
</div>
<p class="label" id = "blind_level"></p>
<p class="label" id = "hand_number"></p>
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
var jstpl_pile_token='<div class = "token ${TEXT_CLASS}" id = "${TOKEN_ID}" style = "transform: translateY(${TOKEN_TRANSLATE}px); top: 0px; left: 0px;">'

// CARD_LOCATION_CLASS = cardflop, cardturn, cardriver, cardinhand cardinhandleft, cardinhand cardinhandright
// CARD_VISIBILITY_CLASS = cardvisible, cardhidden
// If cardhidden --> background-position must be 0% 0%
var jstpl_card='<div class = "card ${CARD_LOCATION_CLASS} ${CARD_VISIBILITY_CLASS}" style = "background-position: ${BACKGROUND_POSITION_LEFT_PERCENTAGE}% ${BACKGROUND_POSITION_TOP_PERCENTAGE}%;"></div>';

// Change board dialog template
var jstpl_change_board=' \
    <div class = "changemodal"> \
        <div class = "changemodalmain"> \
            <div class = "changemodalleftpane"> \
                <div class = "changemodaltitle">${STOCK_TITLE}</div> \
                <div class="changetokens-vertical" id="changeyourstock"> \
                    ${CHANGE_STOCK} \
                </div> \
            </div> \
            <div class = "changemodalrightpane"> \
                <div class="changegivensection"> \
                    <div class = "changemodaltitle">${GIVEN_TITLE}</div> \
                    <div class = "changetokens-horizontal" id="changegiventokens"> \
                        ${CHANGE_GIVEN} \
                    </div> \
                </div> \
                <div class="changereceivedsection" id="changereceivedtokens"> \
                    <div class = "changemodaltitle">${RECEIVED_TITLE}</div> \
                    ${CHANGE_RECEIVED} \
                </div> \
            </div> \
        </div> \
        <div class = "changemodalfooter"> \
            <a type="button" class = "action-button bgabutton bgabutton_blue" id = "changebutton">${CHANGE_BUTTON_LABEL}</a> \
            <a type="button" class = "action-button bgabutton bgabutton_blue" id = "cancelbutton">${CANCEL_BUTTON_LABEL}</a> \
        </div> \
    </div> \
';

var jstpl_change_token = ' \
    <div class = "token token${TOKEN_COLOR} ${TOKEN_CLASS}" id = "${TOKEN_ID}"> \
        <div class = "changetokennumber ${TEXT_CLASS}">${TOKEN_NUM}</div> \
    </div> \
';

var jstpl_change_proposed_change = '<div class = "changetokens-horizontal changereceivedproposition" id = "proposed_change_${PROPOSED_CHANGE_ID}">${TOKENS}</div>';

var jstpl_player_board_tokens = '<div class = "paneltokens"> \
        <div id = "panelstocktoken_${player_id}" class = "paneltokenscolumn"><span>Stock</span>${STOCK_TOKENS}</div> \
        <div id = "panelbettoken_${player_id}" class = "paneltokenscolumn"><span>Bet</span>${BET_TOKENS}</div> \
    </div>';

var jstpl_choose_raise_dialog = ' \
    <label for = "raiseAmount">${INPUT_LABEL}</label> \
    <input type = "number" id = "raiseAmount" value = 0><br> \
    <a type="button" class = "action-button bgabutton bgabutton_blue" id = "raisebutton">${RAISE_BUTTON_LABEL}</a> \
    <a type="button" class = "action-button bgabutton bgabutton_blue" id = "cancelbutton">${CANCEL_BUTTON_LABEL}</a> \
';

</script>

<audio id="audiosrc_texasholdem_COINSOUND" src="{GAMETHEMEURL}img/texasholdem_COINSOUND.mp3" preload="none" autobuffer></audio>
<audio id="audiosrc_o_texasholdem_COINSOUND" src="{GAMETHEMEURL}img/texasholdem_COINSOUND.ogg" preload="none" autobuffer></audio>
<audio id="audiosrc_texasholdem_CARDSOUND" src="{GAMETHEMEURL}img/texasholdem_CARDSOUND.mp3" preload="none" autobuffer></audio>
<audio id="audiosrc_o_texasholdem_CARDSOUND" src="{GAMETHEMEURL}img/texasholdem_CARDSOUND.ogg" preload="none" autobuffer></audio>

{OVERALL_GAME_FOOTER}
