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

// POST /posts - Create a new post or update an existing post
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['clientKey']) || $_POST['clientKey'] !== $clientKey) {
        http_response_code(401);
        $response['error'] = 'Unauthorized';
    } else {
        $request_body = file_get_contents('php://input');
        $data = json_decode($request_body, true);

        // Check if the request body is valid JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            $response['error'] = 'Invalid JSON';
        } else {
            $method = $_GET['method'] ?? '';

            if ($method === 'create') {
                // Extract required parameters
                $user_id = $data['user_id'] ?? null;
                $status = $data['status'] ?? null;
                $text = $data['text'] ?? null;
                $date = $data['date'] ?? null;
                $contentid = $data['contentid'] ?? null;
                $reply = $data['reply'] ?? null;
                $language = $data['language'] ?? null;

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
            } elseif ($method === 'update') {
                // Extract required parameters
                $id = $data['id'] ?? null;
                $user_id = $data['user_id'] ?? null;
                $status = $data['status'] ?? null;
                $text = $data['text'] ?? null;
                $date = $data['date'] ?? null;
                $contentid = $data['contentid'] ?? null;
                $reply = $data['reply'] ?? null;
                $language = $data['language'] ?? null;

                // Validate required parameters
                if (empty($id) || empty($user_id) || empty($status) || empty($text) || empty($date)) {
                    http_response_code(400);
                    $response['error'] = 'Missing required parameters';
                } else {
                    // Update post in the database
                    $query = "UPDATE posts SET user_id = ?, status = ?, text = ?, date = ?, contentid = ?, reply = ?, language = ? WHERE id = ?";
                    $stmt = mysqli_prepare($con, $query);
                    mysqli_stmt_bind_param($stmt, 'issssiii', $user_id, $status, $text, $date, $contentid, $reply, $language, $id);

                    if (mysqli_stmt_execute($stmt)) {
                        $response['success'] = true;
                    } else {
                        http_response_code(500);
                        $response['error'] = 'Failed to update post';
                    }

                    mysqli_stmt_close($stmt);
                }
            } else {
                http_response_code(400);
                $response['error'] = 'Invalid method';
            }
        }
    }
}

// GET /posts/:id - Retrieve a specific post
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    if (!isset($_GET['clientKey']) || $_GET['clientKey'] !== $clientKey) {
        http_response_code(401);
        $response['error'] = 'Unauthorized';
    } else {
        $id = $_GET['id'];

        // Retrieve the specific post from the database
        $query = "SELECT * FROM posts WHERE id = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
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
            $response['post'] = $post;
        } else {
            http_response_code(404);
            $response['error'] = 'Post not found';
        }

        mysqli_stmt_close($stmt);
    }
}

// GET /posts/:id/delete - Delete a post
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && isset($_GET['delete'])) {
    if (!isset($_GET['clientKey']) || $_GET['clientKey'] !== $clientKey) {
        http_response_code(401);
        $response['error'] = 'Unauthorized';
    } else {
        $id = $_GET['id'];

        // Delete the post from the database
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

mysqli_close($con);

// Send response as JSON
echo json_encode($response, JSON_PRETTY_PRINT);
?>
