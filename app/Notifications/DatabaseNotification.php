<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Class ContactNotification.
 */
class DatabaseNotification extends Notification
{
    use Queueable;

    private $title;

    private $url_see_notification;

    private $user;

    /**
     * Create a new notification instance.
     *
     * @param $title
     * @param $url_see_notification
     * @param $user
     */
    public function __construct($title, $url_see_notification, $user)
    {
        $this->title = $title;
        $this->url_see_notification = $url_see_notification;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'title' => $this->title,
            'url' => $this->url_see_notification,
            'user_id' => $this->user,
        ];
    }
}
