# Inbox Module for Laravel 11

Social Media Inbox Management Module converted from CodeIgniter 4 to Laravel 11.

## Features

- **Multi-Platform Support**: Manage messages and comments from Facebook, Instagram, Twitter, LinkedIn, Pinterest, and Google Business
- **Unified Inbox**: View all social media interactions in one place
- **Filtering System**: Filter by brand, user, profile, tags, event type, date range, and status
- **Tag Management**: Create and assign tags to organize inbox items
- **User Assignment**: Assign inbox items to specific team members
- **Complete/Incomplete Status**: Mark items as complete or incomplete
- **Favourite Items**: Mark important items as favorites
- **Reply Functionality**: Reply to messages and comments directly from the inbox
- **Conversation Threading**: View full conversation threads for messages
- **Comment Hierarchy**: View parent and child comments with proper threading

## Installation

### 1. Copy Module to Project

Copy the Inbox module folder to your Laravel project's `Modules` directory:

```bash
cp -r InboxModule /path/to/your/laravel/project/Modules/Inbox
```

### 2. Register Service Provider

Add the service provider to `config/app.php`:

```php
'providers' => [
    // Other providers...
    Modules\Inbox\Providers\InboxServiceProvider::class,
],
```

Or if using Laravel 11's auto-discovery, the provider will be automatically registered.

### 3. Run Migrations

Run the migrations to create the required database tables:

```bash
php artisan migrate
```

This will create the following tables:
- `inbox` - Main inbox messages table
- `inbox_comments` - Comments and replies table
- `inbox_tags` - Tags for organizing inbox items
- `inbox_tags_manage` - Junction table for inbox-tag relationships
- `inbox_users_manage` - Junction table for inbox-user assignments

### 4. Publish Assets (Optional)

If you want to customize the views or config:

```bash
php artisan vendor:publish --tag=inbox-config
php artisan vendor:publish --tag=inbox-assets
```

## Database Structure

### Tables

#### inbox
Stores direct messages and messenger conversations.

Key fields:
- `conversation_id` - Unique identifier for conversation threads
- `media_type` - Social network (facebook, instagram, twitter, etc.)
- `inbox_type` - Type of message (Messenger, DirectMessage, etc.)
- `is_completed` - Completion status
- `is_favourite` - Favourite status

#### inbox_comments
Stores comments and comment replies from social media posts.

Key fields:
- `post_id` - ID of the post being commented on
- `parent_id` - ID of parent comment (for replies)
- `is_child` - Whether this is a reply to another comment
- `comment_count` - Number of replies to this comment

#### inbox_tags
Stores tags that can be assigned to inbox items.

#### inbox_tags_manage
Manages the many-to-many relationship between inbox items and tags.

#### inbox_users_manage
Manages user assignments for inbox items.

## Usage

### Basic Usage

Access the inbox at: `/inbox`

### API Endpoints

All routes are prefixed with `/inbox`:

- `GET /inbox` - Display inbox page
- `POST /inbox/ajax-list` - Get inbox list with filters
- `POST /inbox/ajax-list-detail` - Get conversation/comment details
- `POST /inbox/save-comment` - Send a reply
- `POST /inbox/delete-message` - Delete a single message
- `POST /inbox/delete-message-bulk` - Delete multiple messages
- `POST /inbox/make-post-complete-selected` - Mark selected as complete
- `POST /inbox/make-post-complete-all` - Mark all as complete
- `POST /inbox/make-post-incomplete-selected` - Mark selected as incomplete
- `POST /inbox/make-post-incomplete-all` - Mark all as incomplete
- `POST /inbox/add-tag` - Create new tag
- `POST /inbox/assign-tag` - Assign tag to inbox item
- `POST /inbox/set-favourite` - Set favourite status
- `POST /inbox/assign-user` - Assign user to inbox item

### Filtering

The inbox supports comprehensive filtering:

```javascript
// Example: Filter by brand and completed status
$.ajax({
    url: '/inbox/ajax-list',
    type: 'POST',
    data: {
        brand: [1, 2, 3],
        itemFilter: 'Completed',
        page: 1
    }
});
```

### Models

#### Inbox Model
```php
use Modules\Inbox\Models\Inbox;

// Get inbox list with filters
$inboxList = Inbox::getInboxList($wheres, $whereIn);

// Get conversation detail
$detail = Inbox::getInboxDetail($wheres, $whereIn, $fromId, $toId);

// Mark as reviewed
$inbox = Inbox::find($id);
$inbox->markAsReviewed($userId);
```

#### InboxComment Model
```php
use Modules\Inbox\Models\InboxComment;

// Get comments list
$comments = InboxComment::getInboxCommentsList($wheres, $whereIn);

// Get comment details
$detail = InboxComment::getInboxCommentsDetail($wheres, $whereIn);
```

### Tags

```php
use Modules\Inbox\Models\InboxTag;
use Modules\Inbox\Models\InboxTagManage;

// Create a tag
$tag = InboxTag::create([
    'tag_name' => 'Important',
    'added_user_id' => auth()->id(),
    'brand_id' => session('brand_id')
]);

// Assign tag to inbox item
InboxTagManage::updateOrCreate(
    [
        'inbox_id' => $inboxId,
        'table_name' => 'inbox'
    ],
    [
        'tag_ids' => '1,2,3',
        'added_user_id' => auth()->id(),
        'brand_id' => session('brand_id')
    ]
);
```

## Customization

### Views

All views are located in `resources/views/` and can be customized:

- `index.blade.php` - Main inbox page
- `ajax_list.blade.php` - Inbox list view
- `ajax_list_detail.blade.php` - Message conversation view
- `ajax_list_comment_detail.blade.php` - Comment thread view

### Configuration

Edit `config/config.php` to customize:

- Pagination settings
- Supported social networks
- Inbox types
- Filter options
- Network icons and colors

## Dependencies

- Laravel 11.x
- PHP 8.2+
- MySQL/MariaDB
- jQuery (for AJAX functionality)
- Bootstrap 5 (for UI components)
- Font Awesome (for icons)

## Converting from CodeIgniter 4

This module has been fully converted from CodeIgniter 4 to Laravel 11 with the following changes:

1. **Database Queries**: All CI4 Query Builder calls converted to Laravel Eloquent/Query Builder
2. **Models**: CI4 models converted to Laravel Eloquent models with proper relationships
3. **Controllers**: CI4 controllers converted to Laravel controllers with dependency injection
4. **Views**: PHP views converted to Blade templates
5. **Routes**: CI4 routes converted to Laravel route definitions
6. **Validation**: CI4 validation converted to Laravel validation
7. **Session**: CI4 session handling converted to Laravel session facade
8. **Helpers**: CI4 helpers need to be replaced with Laravel equivalents or custom helpers

## Notes

- The module assumes you have existing `brands`, `users`, and `accounts` tables
- Social media API integration methods (post_comment, post_message, etc.) need to be implemented based on your specific API credentials and requirements
- User authentication and authorization middleware should be added as needed
- The module uses raw SQL for some complex queries with FIND_IN_SET and GROUP_CONCAT which are MySQL-specific

## Support

For issues or questions, please refer to the original project documentation or contact the development team.

## License

[Your License Here]
