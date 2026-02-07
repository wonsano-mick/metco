<?php

namespace App\Exports;

use App\Models\Eloquent\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    protected $transactions;
    protected $filters;

    public function __construct($transactions, $filters = [])
    {
        $this->transactions = $transactions;
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->transactions;
    }

    /**
     * Define the headings
     */
    public function headings(): array
    {
        return [
            'Transaction ID',
            'Reference Number',
            'Date',
            'Time',
            'Type',
            'Status',
            'Amount',
            'Currency',
            'Fee Amount',
            'Tax Amount',
            'Net Amount',
            'Description',
            'Source Account',
            'Source Customer',
            'Destination Account',
            'Destination Customer',
            'Initiator',
            'Completed By',
            'Approved By',
            'Cancelled By',
            'Initiated At',
            'Completed At',
            'Approved At',
            'Cancelled At',
            'Notes',
            'IP Address',
            'Failure Reason',
        ];
    }

    /**
     * Map the data for each row
     */
    public function map($transaction): array
    {
        // Get ledger entries details
        $sourceAccount = null;
        $destinationAccount = null;
        $sourceCustomer = null;
        $destinationCustomer = null;

        foreach ($transaction->ledgerEntries as $entry) {
            if ($entry->entry_type === 'debit' && $entry->account) {
                $sourceAccount = $entry->account->account_number ?? 'N/A';
                $sourceCustomer = $entry->account->customer->full_name ?? 'N/A';
            }
            if ($entry->entry_type === 'credit' && $entry->account) {
                $destinationAccount = $entry->account->account_number ?? 'N/A';
                $destinationCustomer = $entry->account->customer->full_name ?? 'N/A';
            }
        }

        // Format metadata
        $metadata = $transaction->metadata ? (is_array($transaction->metadata) ? $transaction->metadata : json_decode($transaction->metadata, true)) : [];
        $failureReason = $metadata['failure_reason'] ?? ($transaction->failure_reason ?? 'N/A');

        return [
            $transaction->id,
            $transaction->transaction_reference,
            $transaction->initiated_at->format('Y-m-d'),
            $transaction->initiated_at->format('H:i:s'),
            ucfirst($transaction->type),
            ucfirst($transaction->status),
            number_format($transaction->amount, 2),
            strtoupper($transaction->currency),
            number_format($transaction->fee_amount ?? 0, 2),
            number_format($transaction->tax_amount ?? 0, 2),
            number_format($transaction->net_amount ?? $transaction->amount, 2),
            $transaction->description ?? 'N/A',
            $sourceAccount,
            $sourceCustomer,
            $destinationAccount,
            $destinationCustomer,
            $transaction->initiator->first_name.' '. $transaction->initiator->last_name ?? 'System',
            $transaction->completer->first_name . ' ' . $transaction->completer->last_name ?? 'N/A',
            $transaction->approver->first_name . ' ' . $transaction->approver->last_name ?? 'N/A',
            $transaction->canceller->first_name . ' ' . $transaction->canceller->last_name ?? 'System',
            $transaction->initiated_at->format('Y-m-d H:i:s'),
            $transaction->completed_at?->format('Y-m-d H:i:s') ?? 'N/A',
            $transaction->approved_at?->format('Y-m-d H:i:s') ?? 'N/A',
            $transaction->cancelled_at?->format('Y-m-d H:i:s') ?? 'N/A',
            $transaction->notes ?? 'N/A',
            $transaction->ip_address ?? 'N/A',
            $failureReason,
        ];
    }

    /**
     * Set worksheet title
     */
    public function title(): string
    {
        return 'Transactions';
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Make first row bold
        $sheet->getStyle('A1:AA1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFE0E0E0',
                ]
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ],
            ],
        ]);

        // Auto-size columns
        foreach (range('A', 'Z') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        $sheet->getColumnDimension('AA')->setAutoSize(true);

        // Add some spacing
        $sheet->getStyle('A1:AA1')->getAlignment()->setVertical('center');
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Format amount columns as currency
        $amountColumns = ['G', 'I', 'J', 'K']; // Amount, Fee, Tax, Net
        foreach ($amountColumns as $column) {
            $sheet->getStyle($column . '2:' . $column . $sheet->getHighestRow())
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Register events
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Add filter info if available
                if (!empty($this->filters)) {
                    $row = $this->collection()->count() + 3;

                    $event->sheet->setCellValue('A' . $row, 'Export Information:');
                    $event->sheet->getStyle('A' . $row)->getFont()->setBold(true);

                    $row++;
                    $event->sheet->setCellValue('A' . $row, 'Generated on: ' . now()->format('Y-m-d H:i:s'));

                    $row++;
                    $event->sheet->setCellValue('A' . $row, 'Total Records: ' . $this->collection()->count());

                    // Add filter details
                    if (!empty($this->filters)) {
                        $row++;
                        $event->sheet->setCellValue('A' . $row, 'Filters Applied:');
                        $event->sheet->getStyle('A' . $row)->getFont()->setBold(true);

                        foreach ($this->filters as $key => $value) {
                            if ($value) {
                                $row++;
                                $event->sheet->setCellValue('A' . $row, ucfirst(str_replace('_', ' ', $key)) . ': ' . $value);
                            }
                        }
                    }

                    // Add summary statistics
                    if ($this->collection()->count() > 0) {
                        $row++;
                        $row++;
                        $event->sheet->setCellValue('A' . $row, 'Summary Statistics:');
                        $event->sheet->getStyle('A' . $row)->getFont()->setBold(true);

                        $row++;
                        $event->sheet->setCellValue('A' . $row, 'Total Amount:');
                        $event->sheet->setCellValue('B' . $row, number_format($this->collection()->sum('amount'), 2));

                        $row++;
                        $event->sheet->setCellValue('A' . $row, 'Completed Transactions:');
                        $event->sheet->setCellValue('B' . $row, $this->collection()->where('status', 'completed')->count());

                        $row++;
                        $event->sheet->setCellValue('A' . $row, 'Pending Transactions:');
                        $event->sheet->setCellValue('B' . $row, $this->collection()->where('status', 'pending')->count());

                        $row++;
                        $event->sheet->setCellValue('A' . $row, 'Failed Transactions:');
                        $event->sheet->setCellValue('B' . $row, $this->collection()->where('status', 'failed')->count());
                    }
                }

                // Freeze the first row
                $event->sheet->freezePane('A2');

                // Add auto-filter to headers
                $event->sheet->setAutoFilter('A1:AA1');
            },
        ];
    }
}
