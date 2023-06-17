<?php
$select_posts_algorithm[] = "((LOWER(title) LIKE LOWER('%+$topic%') OR LOWER(text) LIKE LOWER('%+$topic%')) OR (LOWER(title) LIKE LOWER('%$topic%') OR LOWER(text) LIKE LOWER('%$topic%')))";
?>