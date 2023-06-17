<?php
$list_content_sql = mysqli_query($con,"SELECT *, GROUP_CONCAT(contentid) as ids FROM lists_content WHERE list_id IN (SELECT id FROM lists WHERE id='".$_REQUEST["id"]."' AND (status='public' OR user_id='".$user_same["id"]."')) GROUP BY type ORDER BY date DESC");
while($row_list_content = mysqli_fetch_array($list_content_sql)){
    $list_content_ifResults = true;
	switch ($row_list_content["type"]) {
        case "user":
            $select_posts_algorithm[] = " user_id IN(".$row_list_content["ids"].") ";
            break;
        case "post":
            $select_posts_algorithm[] = " id IN(".$row_list_content["ids"].") ";
            break;
    }
}

if(!$list_content_ifResults){
    $select_posts_algorithm[] = " id IS NULL ";
}
?>