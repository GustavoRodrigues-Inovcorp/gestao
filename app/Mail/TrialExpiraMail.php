<?php
namespace App\Mail;

use App\Models\Subscricao;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialExpiraMail extends Mailable
{
    use SerializesModels;

    public int $tries = 1;

    public function __construct(
        public Subscricao $subscricao,
        public int $diasRestantes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'O teu trial termina em ' . $this->diasRestantes . ' dia(s)',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.trial_expira',
            with: [
                'subscricao' => $this->subscricao,
                'plano'      => $this->subscricao->plano,
                'dias'       => $this->diasRestantes,
            ],
        );
    }
}