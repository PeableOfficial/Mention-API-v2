<?php
$select_posts_algorithm[] = "(LOWER(CONCAT(' ', text, ' ')) LIKE LOWER(CONCAT('%#', REPLACE('".$_REQUEST["hashtag"]."', ' ', ''), ' %')))";
?>