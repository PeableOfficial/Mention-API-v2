<?php
$select_posts_algorithm[] = " user_id IN((SELECT p.user_id
FROM posts p
JOIN users u ON p.user_id = u.id
WHERE u.category_id = '".$_REQUEST["category"]."' GROUP BY p.user_id)) ";
?>