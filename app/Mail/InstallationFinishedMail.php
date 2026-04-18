<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InstallationFinishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $frontendUrl;
    public string $adminUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(string $frontendUrl, string $adminUrl)
    {
        $this->frontendUrl = $frontendUrl;
        $this->adminUrl = $adminUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'SCL-ECOM-SHOP Installation Completed',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.installation_finished',
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
