<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Class ReplyContactNotification.
 */
class ReplyContactNotification extends Notification
{
    use Queueable;

    protected $contact;

    protected $message;

    /**
     * Create a new notification instance.
     *
     * @param $contact
     * @param $message
     */
    public function __construct($contact, $message)
    {
        $this->contact = $contact;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        return (new MailMessage())
            ->success()
            ->subject('Votre demande de contact')
            ->line($this->contact.' vous a répondu :')
            ->line($this->message);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray()
    {
        return [
        ];
    }
}
