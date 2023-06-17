<?php
$response = array();

$feed_insert_json = array();

//ORDER BY WEIGHT*/
function orderByWeight($a, $b){
    if ($a["weight"] == $b["weight"] or $a["type"] != "smart-card") {
        return 0;
    }
    return ($a["weight"] > $b["weight"]) ? -1 : 1;
}

//include_once(__DIR__ . '/feeds/smart-cards/feed.php');

include_once(__DIR__ . '/feed.php');

//if(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) === "news.kaana.io" 
//or parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) === "games.kaana.io" 
//or (($pagina == 'home' or isset($topic) or isset($q)) and $user_same["id"] and parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) === "platform.kaana.io" and !$_REQUEST["post"])){
//    include_once(__DIR__ . '/feeds/news/feed.php');
//}

//if (parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) === "jokes.kaana.io") {
//    include_once(__DIR__ . '/feeds/jokes/feed.php');
//}

//if($pagina == "home"){
//    include_once(__DIR__.'/feeds/socialnetworks/feed.php');
//}
if($feed_insert_json){
    $response = array("blocks" => array(array(
        "type" => "posts",
        "feed" => $feed_insert_json)));
}

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
?>