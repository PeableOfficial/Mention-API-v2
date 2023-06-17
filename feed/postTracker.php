<?php

if (isset($_POST['postId']) && isset($_POST['timeSpent']) && isset($user_same['id'])) {
  // Sanitize and retrieve the data from the AJAX request
  $postId = mysqli_real_escape_string($con, $_POST['postId']);
  $timeSpent = mysqli_real_escape_string($con, $_POST['timeSpent']);
  $userId = mysqli_real_escape_string($con, $user_same['id']);

  // Update or insert the data into the post statistics table
  $query = "INSERT INTO posts_views (user_id, post_id, time_spent) 
            VALUES ('$userId', '$postId', '$timeSpent') 
            ON DUPLICATE KEY UPDATE time_spent = time_spent + '$timeSpent'";

  if (mysqli_query($con, $query)) {
    // Successful insertion or update
    $response = array('status' => 'success', 'message' => 'Time spent updated for post ' . $postId);
  } else {
    // Error occurred
    $response = array('status' => 'error', 'message' => 'Error updating time spent for post ' . $postId);
  }
} else {
  // Required data not received
  $response = array('status' => 'error', 'message' => 'Invalid request');
}

// Return the response in JSON format
header('Content-Type: application/json');
echo json_encode($response);

?>