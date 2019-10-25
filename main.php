<?php

/*
 SCRIPT CONFIGURATIONS
 SCRIPT CODED BY LENDOG 23/10/2019
 DISCORD: Lendog#9722
*/
$SERVER = 'SEVERURL'; //URL TO YOUR ICECAST SERVER WIHTOUT TRAILING '/' http://www.example.com:8080
$STATS_FILE = '/status-json.xsl'; //PATH TO JSON DUMP OF STREAM, LEAVE THE SAME UNLESS YOU KNOW IT HAS CHANGED!!!

$MULTI_SOURCE_STREAM = False; // DOES THE SERVER HAVE MUITPLE STREAMS?
$SOURCE_NUM = 0;// IF SO WHAT SOURCE DO YOU WANT THIS CODE TO LOOK AT, (P.S COMPUTER COUNTING LOGIC)

$DEBUG = true; // FOR TESTING - LENDOG

///////////////////// END OF CONFIGURATION --- DO NOT EDIT BELOW THIS LINE \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

//GRAB JSON FILE AND DECODE USING PHP DECODER
$json = file_get_contents($SERVER . $STATS_FILE);
$decoded_json = json_decode($json, true);

// MULTI SOURCE FUNCTION - I HATE ICECAST GRRR
if($MULTI_SOURCE_STREAM == FALSE && $SOURCE_NUM == 0)
{
    $SOURCE_NUM = "source";
}

if($DEBUG == TRUE)
{
    //$decoded_json['icestats']["$SOURCE_NUM"]['yp_currently_playing'] = "Meet Me Halfway";
    //$decoded_json['icestats']["$SOURCE_NUM"]['artist'] = "Black Eyed Peas";
}

//SET VALUES FROM STREAM
if(!empty($decoded_json['icestats']["$SOURCE_NUM"]['listeners'] && is_numeric($decoded_json['icestats']["$SOURCE_NUM"]['listeners']))) // ALSO ADDED NUMERIC CHECK TO STOP NON NUMERIC VALUES
    { $radio_info['listeners'] = $decoded_json['icestats']["$SOURCE_NUM"]['listeners']; } else $radio_info['listeners'] = "0";

if(!empty($decoded_json['icestats']["$SOURCE_NUM"]['listener_peak'] && is_numeric($decoded_json['icestats']["$SOURCE_NUM"]['listener_peak']))) // ALSO ADDED NUMERIC CHECK TO STOP NON NUMERIC VALUES
    { $radio_info['most_listeners'] = $decoded_json['icestats']["$SOURCE_NUM"]['listener_peak']; } else $radio_info['most_listeners'] = "0";

if(!empty($decoded_json['icestats']["$SOURCE_NUM"]['genre']))
    { $radio_info['genre'] = $decoded_json['icestats']["$SOURCE_NUM"]['genre']; } else $radio_info['genre'] = "Unknown Genre";

if(!empty($decoded_json['icestats']["$SOURCE_NUM"]['artist']))
    { $radio_info['artist'] = $decoded_json['icestats']["$SOURCE_NUM"]['artist']; } else $radio_info['artist'] = "Unknown Artist";

if(!empty($decoded_json['icestats']["$SOURCE_NUM"]['yp_currently_playing']))
    { 
        $radio_info['currently_playing'] = $decoded_json['icestats']["$SOURCE_NUM"]['yp_currently_playing']; // SET CURRENT SONG VAR!

        $cleanSONG = formatSongName($radio_info['artist'] . " - " . $radio_info['currently_playing']); // CLEANING SONG NAME TO MAKE SURE ITUNES CAN READ SONG!

        $itunes_url = "https://itunes.apple.com/search?term=" . $cleanSONG . "&entity=song"; // SEARCH ITUNES SONG LIB FOR SONG!
            
        $itunes_file = file_get_contents($itunes_url); // GRAB THE DATA DUMPED FROM ITUNES!
            
        $itunes = json_decode($itunes_file, true); // DECODE THE DUMP AND ALLOW US TO USE IMAGES!

        if(!empty($itunes['results'][0]['artworkUrl100']))
        {
            $radio_info['artwork_url'] = $itunes['results'][0]['artworkUrl100']; // CHANGE THE SIZE OF IMAGE GIVEN, RESEARCH ITUNES API FOR IMAGE SIZES!

            $radio_info['artwork_url'] = str_replace('100x100', "300x300", $radio_info['artwork_url']); // ENLARGE THE IMAGE SIZE TO ALLOW FOR RESIZING WHEN CHANGED ON WEBSITE - SAVES QUAILTY!
        } 
    } else $radio_info['currently_playing'] = "Unknown Song";

if (empty($radio_info['artwork_url'])) {
        $radio_info['artwork_url'] = "Artwork cannot be found!"; // THIS CAN BE CHANGED TO IMAGE LINK IF YOU WANT TO DEFINE A DEFAULT IMAGE!
    }

// FUNCTION FOR DEBUG TESTING DURING CODING - LENDOG
if($DEBUG == TRUE)
{
    echo "Radio Listeners:" . $radio_info['listeners'];
    echo '<br>';
    echo "Radio Peak:" . $radio_info['most_listeners'];
    echo '<br>';
    echo "Radio Genre:" . $radio_info['genre'];
    echo '<br>';
    echo "Radio Artist:" . $radio_info['artist']; 
    echo '<br>';
    echo "Radio CP:" . $radio_info['currently_playing'];
    echo '<br>';
    echo "Artwork:" . $radio_info['artwork_url']; 
}

function formatSongName($str, $sep='-')
{
    $res = strtolower($str);
    $res = preg_replace('/[^[:alnum:]]/', ' ', $res);
    $res = preg_replace('/[[:space:]]+/', $sep, $res);
    return trim($res, $sep);
}
?>
