<?php

namespace App\Mail;

use App\Models\AdminInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly AdminInvitation $invitation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You are invited to Crave Bakery Admin',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->htmlBody(),
        );
    }

    private function htmlBody(): string
    {
        $url = route('admin-invitations.show', $this->invitation->token);
        $role = $this->invitation->role === 'super_admin' ? 'Super Admin' : 'Admin';
        $expires = $this->invitation->expires_at?->toDayDateTimeString() ?? '';

        return <<<HTML
        <div style="font-family: Inter, sans-serif; color: #1c1b1b; line-height: 1.6;">
            <h1 style="font-family: Georgia, serif; color: #3D1A0E;">Crave Bakery</h1>
            <p>You have been invited to join the admin panel as <strong>{$role}</strong>.</p>
            <p><a href="{$url}" style="display:inline-block;padding:12px 24px;background:#E8572A;color:#fff;border-radius:999px;text-decoration:none;font-weight:600;">Accept invitation</a></p>
            <p style="color:#6B6B6B;font-size:14px;">Or open this link: {$url}</p>
            <p style="color:#6B6B6B;font-size:14px;">This invitation expires on {$expires}.</p>
        </div>
        HTML;
    }
}
