<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $emailSubject;
    public string $content;
    public ?string $unsubscribe_url;

    /**
     * Create a new message instance.
     */
    public function __construct(string $emailSubject, string $content, ?string $unsubscribe_url = null)
    {
        $this->emailSubject = $emailSubject;
        $this->content = $content;
        $this->unsubscribe_url = $unsubscribe_url;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $siteSettings = \App\Models\Setting::where('group', 'site')->pluck('value', 'key')->toArray();

        return new Content(
            view: 'emails.newsletter',
            with: [
                'subject' => $this->emailSubject,
                'content' => $this->content,
                'unsubscribe_url' => $this->unsubscribe_url,
                'site_settings' => $siteSettings
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
