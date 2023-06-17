<?php
  $post_weight = 0;
  
  //If the user is the same and the hour and day is the same plus weight
  if($row_posts["user_id"] === $user_same["id"] and date("Y-m-d H") === date("Y-m-d H",strtotime($row_posts["date"]))){
    $post_weight = $post_weight+5;
  }

  //If the user is not the same and the day is the same plus weight
  if(date("Y-m-d",strtotime($row_posts["date"])) === date("Y-m-d", strtotime($datetime))){
    $post_weight = $post_weight+1;
  }

  //Same day but different month and year
  if(date("d",strtotime($row_posts["date"])) === date("d", strtotime($datetime))){
    $post_weight = $post_weight+1;
  }


  if(date("Y-m-d H", strtotime($row_posts["date"])) == date("Y-m-d H", strtotime($datetime))){
	  $post_weight = $post_weight+2;
  }
  
  //FOLLOWING
  if(is_string($user_follows_users) and is_string($post_userid) and stripos($user_follows_users, $post_userid)){
    $post_weight = $post_weight+0.5;
  }
  
  //Have more content (Like: photos, video, etc...)
  if($row_posts["contentid"]){
    $post_weight = $post_weight+3;
  }

  //Have more content VIEWS
  if($row_posts["view_countValuable"] >= 150){
    $post_weight = $post_weight+300;
  }
  
  
  //MENTION
  if(preg_match("/@".$user_same['username']."/", $row_posts["text"])){
    $post_weight = $post_weight+5;
  }

  if(is_array($user_mismo_topics_array)){
    foreach($user_mismo_topics_array as $post_topic) {
      if(stripos($post_text, $post_topic)){
        $post_weight = $post_weight+1;
      }
    }
  }

foreach ($languages as &$language_item) {
  //kTranslate check if contains words like happy birthday and add weight
}

//TO DO
//If have special words like (happy birthday, name of an event, name of a city) add weight
?>