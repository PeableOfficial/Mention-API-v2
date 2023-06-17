<?php

include_once(__DIR__."/extractors/usernames.php");

function functionFeedPosts($row_posts)
{
    global $con;
    global $user_same;
    global $user;
    
    $link_info = null;
    if ($_REQUEST['p'] != "profile") {
        include(__DIR__ . '/algorithm.php');
    }
    $feed_item_userdata = userData($row_posts["user_id"]);
    
    if ($_REQUEST["p"] == "profile") {
        $post_weight = 0;
    }

    $extractUsernames = extractUsernames($row_posts["text"]);
    $row_posts["text"] = $extractUsernames["modifiedText"];
    
    $this_rowData = array(
        "item" => "post",
        "weight" => $post_weight,
        "url" => "https://mention.earth/p/" . $row_posts["id"],
        "text_html" => processtext_post($row_posts["text"]),
        "author" => array(
            "id" => $feed_item_userdata["id"],
            "username" => $feed_item_userdata["username"],
            "title" => $feed_item_userdata["name"]["full"],
            "avatar" => $feed_item_userdata["avatar"],
            "verified" => $feed_item_userdata["verified"],
            "color" => $feed_item_userdata["color"],
            "same" => ($feed_item_userdata["id"] === $user_same["id"])
        )
    );
    
    if ($_REQUEST["post"] != $row_posts["id"]) {
        $replies     = array();
        $replies_sql = mysqli_query($con, "SELECT *, COUNT(*) as totalReplies FROM posts WHERE (contentid IS NOT NULL) AND status='public' AND type!='repost' AND (reply IN('" . $row_posts["id"] . "')) GROUP BY contentid LIMIT 1");
        if ($row_replies = mysqli_fetch_assoc($replies_sql)) {
            $this_rowData = array_merge($this_rowData, array(
                "replies" => array(
                    "total" => $row_replies["totalReplies"]
                )
            ));
        }
    }
    
    if ($_REQUEST["post"] == $row_posts["id"]) {
        $this_rowData = array_merge($this_rowData, array(
            "replies" => array(
                "allowed" => true
            )
        ));
    }
    
    //CONTENT
    $post_content = array();
    
    if ($row_posts["type"] == "repost" and $row_posts["contentid"]) {
        $repost_sql = mysqli_query($con, "SELECT * FROM posts WHERE id='" . $row_posts["contentid"] . "' LIMIT 1");
        while ($row_repost = mysqli_fetch_assoc($repost_sql)) {
            $post_content["repost"] = functionFeedPosts($row_repost);
        }
    }
    
    if ($row_posts["type"] == "poll" and $row_posts["contentid"]) {
        $polls_sql = mysqli_query($con, "SELECT id, title FROM polls WHERE id='" . $row_posts["contentid"] . "' AND user_id='" . $user_same["id"] . "'");
        while ($row_polls = mysqli_fetch_assoc($polls_sql)) {
            $poll_options      = array();
            $polls_options_sql = mysqli_query($con, "SELECT * FROM polls_options WHERE poll_id='" . $row_polls["id"] . "'");
            while ($row_polls_options = mysqli_fetch_assoc($polls_options_sql)) {
                $poll_options[] = array(
                    "id" => $row_polls_options["id"],
                    "title" => $row_polls_options["title"]
                );
            }
            $post_content["poll"] = array(
                "id" => $row_polls["id"],
                "title" => $row_polls["title"],
                "options" => $poll_options
            );
        }
    }
    
    $feed_item_contentid_array = kFiles("info", $row_posts["contentid"]);
    foreach ($feed_item_contentid_array["files"] as &$file) {
        switch (strtok($file["mime"], '/')) {
            case "image":
                $post_content["images"][] = array(
                    "id" => $file["fileid"],
                    "mime" => $file["mime"]
                );
                break;
            case "video":
                $post_content["videos"][] = array(
                    "id" => $file["fileid"],
                    "mime" => $file["mime"]
                );
                break;
            case "audio":
                $post_content["audios"][] = array(
                    "id" => $file["fileid"],
                    "mime" => $file["mime"]
                );
                break;
        }
    }
    
    if ($post_content) {
        $this_rowData = array_merge($this_rowData, $post_content);
    }
    
    //LINKS
    $match_link_p = array();
    preg_match_all('/https?\:\/\/[^\" ]+/i', $row_posts["text"] . " " . $row_posts["contentid"], $match_link_p);
    
    if ($match_link_p[0][0]) {
        $link_info_spider = false;
        if ($_REQUEST["post"]) {
            $link_info_spider = true;
        }
        $link_info = kUrlInfo($match_link_p[0][0], $link_info_spider);
        if ($row_posts["text"] == $link_info["url"]) {
            $this_rowData["text"]      = null;
            $this_rowData["text_html"] = null;
        } elseif (end(explode(" ", preg_replace('/\s\s+/', ' ', $row_posts["text"]))) == $link_info["url"]) {
            $this_rowData["text"]      = substr($row_posts["text"], 0, strrpos($row_posts["text"], ' '));
            $this_rowData["text_html"] = processtext_post(substr($row_posts["text"], 0, strrpos($row_posts["text"], ' ')));
        }
    }
    if ($link_info) {
        $this_rowData = array_merge($this_rowData, array(
            "links" => array(
                $link_info
            )
        ));
    }

    if($extractUsernames["usersData"]){
        $this_rowData = array_merge($this_rowData, array(
            "profiles" => $extractUsernames["usersData"]
        ));
    }
    
    return array_merge($row_posts, $this_rowData);
}

include_once(__DIR__ . '/select.php');


$feed_posts = array();
$posts      = mysqli_query($con, $select_posts);
while ($row_posts = mysqli_fetch_assoc($posts)) {
    $feed_insert_json[] = functionFeedPosts($row_posts);
}
?>