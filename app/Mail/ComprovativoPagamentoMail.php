<?php
namespace App\Mail;

use App\Models\FaturaFornecedor;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class ComprovativoPagamentoMail extends Mailable
{
    use SerializesModels;

    public int $tries = 1;

    public function __construct(
        public FaturaFornecedor $fatura,
        public ?string $comprovativoPath = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Comprovativo de Pagamento - Fatura ' . $this->fatura->numero,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.comprovativo_pagamento',
            with: [
                'fatura'     => $this->fatura,
                'fornecedor' => $this->fatura->fornecedor,
            ],
        );
    }

    public function attachments(): array
    {
        if (empty($this->comprovativoPath)) {
            return [];
        }

        $full = storage_path('app/private/' . $this->comprovativoPath);
        if (!file_exists($full)) {
            return [];
        }

        return [
            Attachment::fromPath($full)
                ->as('comprovativo-' . $this->fatura->numero . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}