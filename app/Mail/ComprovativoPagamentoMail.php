<?php
namespace App\Mail;

use App\Models\FaturaFornecedor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class ComprovativoPagamentoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public FaturaFornecedor $fatura,
        public string $comprativoPath,
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
        return [
            Attachment::fromStorageDisk('private', $this->comprativoPath)
                ->as('comprovativo-' . $this->fatura->numero . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}