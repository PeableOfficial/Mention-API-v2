<?php

$id = $_GET['id'] ?? null;

// Validate post ID
if (empty($id)) {
    http_response_code(400);
    $response['error'] = 'Missing post ID';
} elseif ($_GET['permanent'] == false) {
    $deletePostQuery = "SELECT id FROM posts WHERE id='" . $_REQUEST['id'] . "' AND user_id='" . $user_same["id"] . "' LIMIT 1";
    $deletePostResult = mysqli_query($con, $deletePostQuery);

    if ($row_deletePost = mysqli_fetch_assoc($deletePostResult)) {
        $deleteQuery = "UPDATE posts SET status='deleted' WHERE id='" . $row_deletePost["id"] . "' OR reply='" . $row_deletePost["id"] . "'";
        if (mysqli_query($con, $deleteQuery)) {
            $response['success'] = true;
        } else {
            http_response_code(500);
            $response['error'] = 'Failed to update post status';
        }
    } else {
        http_response_code(400);
        $response['error'] = 'Post not found';
    }
} else {
    // Delete post from the database
    $query = "DELETE FROM posts WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $id, $user_same["id"]);

    if (mysqli_stmt_execute($stmt)) {
        $response['success'] = true;
    } else {
        http_response_code(500);
        $response['error'] = 'Failed to delete post';
    }

    mysqli_stmt_close($stmt);
}

?>
