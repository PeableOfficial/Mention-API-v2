<?php
function extractUsernames($text) {
    $usernames = [];
    $usersData = [];
    $modifiedText = $text;
    $htmlModifiedText = $text;

    // Match all occurrences of @username123
    preg_match_all('/@(\w+)/', $text, $matches);

    foreach ($matches[1] as $match) {
        $userData_match = userData($match, array(
            "type" => "username"
        ));
        
        if(str_replace("@", "", $text) == $match){
        	$usersData[] = $userData_match;
            $htmlModifiedText = str_replace('@'.$match, "", $htmlModifiedText);
            $modifiedText = str_replace('@'.$match, "", $modifiedText);
        } elseif($userData_match["id"]){
            $usernames[] = $match;
        	if ($userData_match["name"]["first"]) {
            	$htmlModifiedText = str_replace('@'.$match, "<a href='/@".$userData_match['username']."'>".$userData_match["name"]["first"]."</a>", $htmlModifiedText);
        	} else {
            	$htmlModifiedText = str_replace('@'.$match, "<a href='/@".$userData_match['username']."'>@".$userData_match["username"]."</a>", $htmlModifiedText);
        	}
        }
    }

    return [
        'usernames' => $usernames,
        'usersData' => $usersData,
        'modifiedText' => $modifiedText,
        'htmlModifiedText' => $htmlModifiedText
    ];
}
?>