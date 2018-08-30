# yii2-notifications
Notification system for Yii 2+

# Usage

Default storage is your database. For that you need to insert migrations from `src/migrations` folder.

To insert those migrations you need to run migration command:

```bash
yii migrate --migrationPath=@vendor/dvamigos/yii2-notifications/src/migrations
```

This will create `notifications` table in your database. If you don't plan to store your notifications in database then this step is not necessary.

Add notification component configuration in your application config:

```php
[
    'components' => [
        'notifications' => [
            'class' => '\dvamigos\Yii2\Notifications\NotificationManager',
            'types' => [
                'my_notification' => 'This is my notification'
            ]
        ]
    ]
]

```

Then in your code you can push notifications directly using:

```php
Yii::$app->notifications->push('my_notification');
```

This will save the notification for current logged in user.

## Notification types

You can define arbitrary number of types for your notification to store as much as data per notification as needed.

Below is an example of a notification having a `title` and a `message`.

```php
[
    'components' => [
        'notifications' => [
            'class' => '\dvamigos\Yii2\Notifications\NotificationManager',
            'types' => [
                'new_user' => [
                    'title' => 'New user created!',
                    'message' => 'New user {username} is created.'
                ]
            ]
        ]
    ]
]

```

Field `{username}` will be replaced from data passed to notification on its creation. To pass data just use:

```php
Yii::$app->notifications->push('new_user', [
    'username' => 'JohnDoe94'
]);
```

## Using in models/controllers/components

You can use `PushNotification` or `ReplaceNotification` classes inside your every component which has `events()` function.

For example to set it inside a model you can define following:

```php
public function events()
{
    self::EVENT_AFTER_INSERT => [
        new PushNotification([
            'type' => 'my_notification',
            'data' => ['my_data' => 1]
        ])
    ]
}
```

Types can be resolved later using:
```php
public function events()
{
    self::EVENT_AFTER_INSERT => [
        new PushNotification([
            'type' => function(PushNotification $n) {
                return 'my_type';
            },
            'data' => function(PushNotification $n) {
                return ['my_key' => $this->getPrimaryKey()];
            }
        ])
    ]
}
```