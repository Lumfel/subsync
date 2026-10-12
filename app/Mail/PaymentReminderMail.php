<?php

namespace App\Mail;

use App\Models\Resident;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Resident $resident, public float $balance) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'HOA Dues Reminder — SubSync');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment-reminder');
    }
}
