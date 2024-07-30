<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectInvitationNotification extends Notification
{
    use Queueable;

    protected $project;
    protected $token;

    public function __construct(Project $project, $token)
    {
        $this->project = $project;
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Project Invitation')
            ->greeting('Hello!')
            ->line('You have been invited to participate in the project: ' . $this->project->name)
            ->action('Accept Invitation', url('/accept-invitation/' . $this->token))
            ->line('Thank you for using our application!');
    }
}
