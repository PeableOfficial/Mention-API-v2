<?php
//SEEING A POST BY ID
if($_REQUEST["post"]){
	$select_posts_algorithm[] = "(id='".$_REQUEST["post"]."' OR reply='".$_REQUEST["post"]."')";
} else {
	$select_posts_algorithm[] = "id='".$_REQUEST["post"]."'";
}
?>