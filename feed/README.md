# Mention API Documentation

The Mention API allows you to interact with the social network platform by performing various operations on posts.

## API Base URL
`https://mention.earth/api/v2/`

## Authentication
To access the Mention API, you need to include the `clientKey` parameter in your requests. Make sure to replace `YOUR_CLIENT_KEY` with your actual client key.

## Retrieve Posts
Retrieve posts with optional filters and pagination.

**Endpoint:** `GET /posts`

**Parameters:**
- `clientKey`: Your client key (required)
- `limit`: Number of posts to retrieve per page (default: 15)
- `order_by`: Sort order of posts (default: date)
- `page`: Page number for pagination (default: 1)
- `columns`: Comma-separated list of columns to retrieve (default: all)
- `user_id`: Filter posts by user ID
- `language`: Filter posts by language
- `exclude_user_id`: Exclude posts from specific user IDs (comma-separated)
- `include_contentid`: Include posts with specific content IDs (comma-separated)

## Create a Post
Create a new post.

**Endpoint:** `POST /posts?method=create`

**Parameters:**
- `clientKey`: Your client key (required)
- Request body: JSON data containing post details:
  - `user_id`: User ID of the post creator (required)
  - `status`: Status of the post (required)
  - `text`: Text content of the post (required)
  - `date`: Date of the post (required)
  - `contentid`: Content ID of the post
  - `reply`: ID of the post being replied to
  - `language`: Language of the post

## Update a Post
Update an existing post.

**Endpoint:** `POST /posts?method=update`

**Parameters:**
- `clientKey`: Your client key (required)
- Request body: JSON data containing post details:
  - `id`: ID of the post to update (required)
  - `user_id`: Updated user ID
  - `status`: Updated status
  - `text`: Updated text content
  - `date`: Updated date
  - `contentid`: Updated content ID
  - `reply`: Updated post reply ID
  - `language`: Updated language

## Retrieve a Specific Post
Retrieve a specific post by its ID.

**Endpoint:** `GET /posts/:id`

**Parameters:**
- `clientKey`: Your client key (required)
- `:id`: ID of the post to retrieve

## Delete a Post
Delete a specific post by its ID.

**Endpoint:** `GET /posts/:id/delete`

**Parameters:**
- `clientKey`: Your client key (required)
- `:id`: ID of the post to delete
- `delete`: Set to `true` to confirm deletion

