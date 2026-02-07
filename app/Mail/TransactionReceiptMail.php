<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransactionReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $receiptData;

    /**
     * Create a new message instance.
     */
    public function __construct(array $receiptData)
    {
        $this->receiptData = $receiptData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Transaction Receipt - ' . $this->receiptData['transaction']['reference'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.transaction-receipt',
            with: [
                'receiptData' => $this->receiptData,
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
        // Generate PDF attachment
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('receipts.transaction-receipt', $this->receiptData);

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn() => $pdf->output(),
                'receipt_' . $this->receiptData['transaction']['reference'] . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
