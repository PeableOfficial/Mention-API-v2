<?php
$select_posts = "SELECT *, (
    SELECT COUNT(*) 
    FROM posts_views pv 
    WHERE pv.post_id = posts.id
    GROUP BY pv.post_id
) AS view_count, (
    SELECT SUM(time_spent) 
    FROM posts_views pv 
    WHERE pv.post_id = posts.id
    GROUP BY pv.post_id
) AS view_countValuable FROM posts WHERE status!='deleted' ";

if($subpage != "drafts"){
	$select_posts .= " AND status='public' ";
}

switch (true) {
    case $_REQUEST["post"]:
        include(__DIR__.'/select/post.php');
        break;
    case ($_REQUEST["p"] === 'profile' and $user["id"]):
        include(__DIR__.'/select/profile.php');
        break;
    case ($user_same["id"] and $subpage == "scheduled"):
        include(__DIR__.'/select/scheduled.php');
        break;
    case ($user_same["id"] and $subpage == "drafts"):
        include(__DIR__.'/select/drafts.php');
        break;
    case ($_REQUEST["p"] === 'list' and $_REQUEST["id"]):
        include(__DIR__.'/select/list.php');
        break;
    case ($_REQUEST["p"] === 'explore'):
        include(__DIR__.'/select/explore.php');
        break;
    case (isset($topic)):
        include(__DIR__.'/select/topics.php');
        break;
    case (isset($_REQUEST["hashtag"])):
        include(__DIR__.'/select/hashtags.php');
        break;
    case (isset($_REQUEST["mention"])):
        include(__DIR__.'/select/mentions.php');
        break;
    default:
    	include(__DIR__.'/select/default.php');
    	break;
}

if(isset($_REQUEST["category"])){
    include(__DIR__.'/select/filters/category.php');
}

$select_posts_algorithm[] = "((type IS NULL AND text IS NOT NULL) OR (type IS NOT NULL))";

$select_posts_algorithm[] = "(user_id NOT IN (SELECT user_id FROM user_block WHERE contentid='".$user_same["id"]."' AND type='user')) AND (user_id NOT IN (SELECT contentid FROM user_block WHERE user_id='".$user_same["id"]."' AND type='user'))";

if($_REQUEST["p"] !== 'post' and !$_REQUEST["post"]){
	$select_posts_algorithm[] = " reply IS NULL ";
}

if($type == "article"){
	$select_posts_algorithm[] = " (LENGTH(text) > 180 OR title IS NOT NULL) ";
} elseif(isset($type)){
	$select_posts_algorithm[] = " type='$type' ";
}

if($_REQUEST['direction'] === "up"){
	$select_posts_algorithm[] = " (date<='$datetime' AND date>='$initDate') ";
} elseif($subpage !== "schenduled") {
	$select_posts_algorithm[] = " (date<='$datetime' AND date<='$initDate') ";
}

if($select_posts_algorithm){
	$select_posts .= " AND (" . implode(" AND ", $select_posts_algorithm) . ") ";
}

if($subpage === "schenduled" or $_REQUEST["post"]){
	$select_posts_order = "ORDER BY date ASC";
} else {
	$select_posts_order = "ORDER BY date DESC, view_countValuable DESC, DAY(date) DESC, MONTH(date) DESC, YEAR(date) DESC";
}

$select_posts .= " $select_posts_order LIMIT $limit";
?>