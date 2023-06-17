<?php
$select_posts_algorithm[] = "(user_id='".$user_same["id"]."' OR user_id IN (SELECT contentid FROM user_follows WHERE user_id='".$user["id"]."' AND type='user' ORDER BY demand DESC, date DESC))";
?>