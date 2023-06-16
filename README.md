# Mention API V2 Documentation

## Base URL
All API endpoints are relative to the base URL: `https://mention.earth/api/v2/`

## Authentication
To access the API, you need to include the `clientKey` parameter in your requests. The `clientKey` should be provided as a query parameter.

## Endpoints

### Retrieve Posts [GET /posts]
Retrieves posts from the database.

**Parameters**
- `clientKey` (required): Your client key for authentication.
- `limit` (optional): The maximum number of posts to retrieve per page (default: 15).
- `order_by` (optional): The field to order the posts by (default: date).
- `page` (optional): The page number for pagination (default: 1).
- `columns` (optional): The specific columns to retrieve (default: all columns).
- `user_id` (optional): Filter posts by user ID.
- `language` (optional): Filter posts by language.
- `exclude_user_id` (optional): Exclude posts by user ID(s). Multiple values should be comma-separated.
- `include_contentid` (optional): Include posts by content ID(s). Multiple values should be comma-separated.

**Response**
- `data`: An array of post objects containing the retrieved posts.

### Create Post [POST /posts]
Creates a new post in the database.

**Parameters**
- `clientKey` (required): Your client key for authentication.
- `user_id` (required): The ID of the user creating the post.
- `status` (required): The status of the post.
- `text` (required): The text of the post.
- `date` (required): The date of the post.
- `contentid` (optional): The content ID of the post.
- `reply` (optional): The ID of the post being replied to.
- `language` (optional): The language of the post.

**Response**
- `post`: An object containing the details of the created post.

### Delete Post [DELETE /posts/:id]
Deletes a post from the database.

**Parameters**
- `clientKey` (required): Your client key for authentication.
- `id` (required): The ID of the post to delete.

**Response**
- `success`: Indicates whether the deletion was successful.

