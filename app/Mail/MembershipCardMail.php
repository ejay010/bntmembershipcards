<?php

namespace App\Mail;

use App\Models\Member;
use App\Services\MembershipCardService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MembershipCardMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $googleWalletUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(public Member $member)
    {
        $this->googleWalletUrl = app(MembershipCardService::class)->generateGoogleWalletUrl($member);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Bahamas National Trust Membership Card',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.card',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $service = app(MembershipCardService::class);
        $passPath = $service->generateApplePass($this->member);

        return [
            Attachment::fromPath($passPath)
                ->as("{$this->member->membership_number}.pkpass")
                ->withMime('application/vnd.apple.pkpass'),
        ];
    }
}
