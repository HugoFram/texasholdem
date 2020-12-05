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
            <div class = "token tokenwhite bettoken">
                <div class = "tokennumber tokennumberdark" id = "bettokenwhite_{PLAYER_ID}">{PLAYER_BET_TOKEN_WHITE}</div>
            </div>
            <div class = "token tokenblue bettoken">
                <div class = "tokennumber tokennumberlight" id = "bettokenblue_{PLAYER_ID}">{PLAYER_BET_TOKEN_BLUE}</div>
            </div>
            <div class = "token tokenred bettoken tokenhidden">
                <div class = "tokennumber tokennumberlight" id = "bettokenred_{PLAYER_ID}">{PLAYER_BET_TOKEN_RED}</div>
            </div>
            <div class = "token tokengreen bettoken">
                <div class = "tokennumber tokennumberlight" id = "bettokengreen_{PLAYER_ID}">{PLAYER_BET_TOKEN_GREEN}</div>
            </div>
            <div class = "token tokenblack bettoken">
                <div class = "tokennumber tokennumberlight" id = "bettokenblack_{PLAYER_ID}">{PLAYER_BET_TOKEN_BLACK}</div>
            </div>
        </div>
        <div class="playertablename" style="color:#{PLAYER_COLOR}">
            {PLAYER_NAME}
        </div>
        <div class="playertablecards" id="playertablecards_{PLAYER_ID}">
            <div class = "card cardinhand cardinhandleft cardvisible" style = "background-position: -300% -100%;"></div>
            <div class = "card cardinhand cardinhandright cardvisible" style = "background-position: -700% -200%;"></div>
        </div>
        <div class="playertabletokens" id="playertabletokens_{PLAYER_ID}">
            <div class = "token tokenwhite">
                <div class = "tokennumber tokennumberdark" id = "tokenwhite_{PLAYER_ID}">{PLAYER_TOKEN_WHITE}</div>
            </div>
            <div class = "token tokenblue">
                <div class = "tokennumber tokennumberlight" id = "tokenblue_{PLAYER_ID}">{PLAYER_TOKEN_BLUE}</div>
            </div>
            <div class = "token tokenred">
                <div class = "tokennumber tokennumberlight" id = "tokenred_{PLAYER_ID}">{PLAYER_TOKEN_RED}</div>
            </div>
            <div class = "token tokengreen">
                <div class = "tokennumber tokennumberlight" id = "tokengreen_{PLAYER_ID}">{PLAYER_TOKEN_GREEN}</div>
            </div>
            <div class = "token tokenblack">
                <div class = "tokennumber tokennumberlight" id = "tokenblack_{PLAYER_ID}">{PLAYER_TOKEN_BLACK}</div>
            </div>
        </div>
    </div>
    <!-- END player -->
    <div class = "playingarea">
        <div class = "tabletokens" id = "previousroundtokens">
            <div class = "token tokenwhite">
                <div class = "tokennumber tokennumberdark" id = "tokenwhite_table">1</div>
            </div>
            <div class = "token tokenblue">
                <div class = "tokennumber tokennumberlight" id = "tokenblue_table">7</div>
            </div>
            <div class = "token tokenred">
                <div class = "tokennumber tokennumberlight" id = "tokenred_table">12</div>
            </div>
            <div class = "token tokengreen tokenhidden">
                <div class = "tokennumber tokennumberlight" id = "tokengreen_table"></div>
            </div>
            <div class = "token tokenblack">
                <div class = "tokennumber tokennumberlight" id = "tokenblack_table">53</div>
            </div>
        </div>
        <div class = "tablecards" id = "river">
            <div class = "card cardflop cardvisible" style = "background-position: -1000% -100%;"></div>
            <div class = "card cardflop cardvisible" style = "background-position: -1000% 0%;"></div>
            <div class = "card cardflop cardvisible" style = "background-position: -1100% 0%;"></div>
            <div class = "card cardturn cardhidden"></div>
            <div class = "card cardriver cardhidden"></div>
        </div>
    </div>
</div>

<script type="text/javascript">

// Javascript HTML templates

/*
// Example:
var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${MY_ITEM_ID}"></div>';

*/

</script>  

{OVERALL_GAME_FOOTER}
