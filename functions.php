<?php
function capitalizeWords($string)
{
    return ucwords($string);
}

// to show 1st letter of word after every space
function initials($str)
{
    $ret = '';
    foreach (explode(' ', $str) as $word)
        $ret .= strtoupper($word[0]);
    return $ret;
}

// to get only first word from username of wordpress users
function userFirstname($string)
{
    $delimiter = " ";
    $firstWord = strtok($string, $delimiter);
    return $firstWord;
}

// format date 
function dateFormat($date){
    $newDate = new DateTime($date);
    $formattedDate = $newDate->format('M d, Y @ h:i A');
    return ($formattedDate);
}

// format date for 1 to 1 details modal box
function dateFormatfor1to1($date){
    $newDate = new DateTime($date);
    $formattedDate = $newDate->format('l, M d, Y');
    return ($formattedDate);
}


