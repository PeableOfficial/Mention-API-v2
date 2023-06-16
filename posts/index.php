<?php
require_once __DIR__.'/../config.php';

// Initialize response variable
$response = [];

// GET /posts - Retrieve posts with optional filters and pagination
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['clientKey']) || $_GET['clientKey'] !== $clientKey) {
        http_response_code(401);
        $response['error'] = 'Unauthorized';
    } else {
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 15;
        $order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'date';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;

        $offset = ($page - 1) * $limit;

        $columns = isset($_GET['columns']) ? $_GET['columns'] : '*';

        $query = "SELECT $columns FROM posts";
        $params = [];
        $where = [];

        // Apply filters if provided
        if (isset($_GET['user_id'])) {
            $where[] = "user_id = ?";
            $params[] = $_GET['user_id'];
        }
        if (isset($_GET['language'])) {
            $where[] = "language = ?";
            $params[] = $_GET['language'];
        }
        if (isset($_GET['exclude_user_id'])) {
            $exclude_user_ids = explode(',', $_GET['exclude_user_id']);
            $exclude_user_ids_placeholders = implode(',', array_fill(0, count($exclude_user_ids), '?'));
            $where[] = "user_id NOT IN ($exclude_user_ids_placeholders)";
            $params = array_merge($params, $exclude_user_ids);
        }
        if (isset($_GET['include_contentid'])) {
            $include_content_ids = explode(',', $_GET['include_contentid']);
            $include_content_ids_placeholders = implode(',', array_fill(0, count($include_content_ids), '?'));
            $where[] = "contentid IN ($include_content_ids_placeholders)";
            $params = array_merge($params, $include_content_ids);
        }

        $where[] = "status!='deleted'";
        $where[] = "status!='draft-temporal'";

        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }

        $query .= " ORDER BY $order_by DESC LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $limit;

        $stmt = mysqli_prepare($con, $query);
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $posts = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $post = [
                'id' => $row['id'],
                'user_id' => $row['user_id'],
                'status' => $row['status'],
                'text' => $row['text'],
                'date' => $row['date'],
                'contentid' => $row['contentid'],
                'reply' => $row['reply'],
                'language' => $row['language']
            ];
            $posts[] = $post;
        }

        $response['data'] = $posts;
    }
}

// POST /posts - Create a new post
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['clientKey']) || $_POST['clientKey'] !== $clientKey) {
        http_response_code(401);
        $response['error'] = 'Unauthorized';
    } else {
        // Extract required parameters
        $user_id = $_POST['user_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $text = $_POST['text'] ?? null;
        $date = $_POST['date'] ?? null;
        $contentid = $_POST['contentid'] ?? null;
        $reply = $_POST['reply'] ?? null;
        $language = $_POST['language'] ?? null;

        // Validate required parameters
        if (empty($user_id) || empty($status) || empty($text) || empty($date)) {
            http_response_code(400);
            $response['error'] = 'Missing required parameters';
        } else {
            // Insert new post into the database
            $query = "INSERT INTO posts (user_id, status, text, date, contentid, reply, language) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, 'issssii', $user_id, $status, $text, $date, $contentid, $reply, $language);

            if (mysqli_stmt_execute($stmt)) {
                $inserted_id = mysqli_stmt_insert_id($stmt);
                $post = [
                    'id' => $inserted_id,
                    'user_id' => $user_id,
                    'status' => $status,
                    'text' => $text,
                    'date' => $date,
                    'contentid' => $contentid,
                    'reply' => $reply,
                    'language' => $language
                ];
                $response['post'] = $post;
            } else {
                http_response_code(500);
                $response['error'] = 'Failed to create post';
            }

            mysqli_stmt_close($stmt);
        }
    }
}

// DELETE /posts/:id - Delete a post
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_GET['clientKey']) || $_GET['clientKey'] !== $clientKey) {
        http_response_code(401);
        $response['error'] = 'Unauthorized';
    } else {
        $id = $_GET['id'] ?? null;

        // Validate post ID
        if (empty($id)) {
            http_response_code(400);
            $response['error'] = 'Missing post ID';
        } else {
            // Delete post from the database
            $query = "DELETE FROM posts WHERE id = ?";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, 'i', $id);

            if (mysqli_stmt_execute($stmt)) {
                $response['success'] = true;
            } else {
                http_response_code(500);
                $response['error'] = 'Failed to delete post';
            }

            mysqli_stmt_close($stmt);
        }
    }
}

// Send response as JSON
echo json_encode($response, JSON_PRETTY_PRINT);
?>
