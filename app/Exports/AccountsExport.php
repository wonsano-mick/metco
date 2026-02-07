<?php

namespace App\Exports;

use App\Models\Eloquent\Account;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class AccountsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    protected $accounts;
    protected $filters;

    public function __construct($accounts, $filters = [])
    {
        $this->accounts = $accounts;
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->accounts;
    }

    /**
     * Define the headings
     */
    public function headings(): array
    {
        return [
            'Name of Customer',
            'Account Number',
            'Current Balance',
            'Available Balance',
            'Status',
            'Date Opened',
            'Account Type',
            'Currency',
            'Ledger Balance',
            'Customer Email',
            'Customer Phone',
            'Branch',
            'Created Date',
            'Last Updated',
        ];
    }

    /**
     * Map the data for each row
     */
    public function map($account): array
    {
        return [
            $account->customer->full_name ?? 'N/A',
            $account->account_number,
            number_format($account->current_balance, 2),
            number_format($account->available_balance, 2),
            $account->status,
            $account->opened_at ? $account->opened_at->format('Y-m-d') : ($account->created_at->format('Y-m-d')),
            $account->accountType->name ?? 'N/A',
            strtoupper($account->currency),
            number_format($account->ledger_balance, 2),
            $account->customer->email ?? 'N/A',
            $account->customer->phone ?? 'N/A',
            $account->customer->branch->name ?? 'N/A',
            $account->created_at->format('Y-m-d H:i:s'),
            $account->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Set worksheet title
     */
    public function title(): string
    {
        return 'Accounts';
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Make first row bold
        $sheet->getStyle('A1:N1')->applyFromArray([
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
        foreach (range('A', 'N') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add some spacing
        $sheet->getStyle('A1:N1')->getAlignment()->setVertical('center');
        $sheet->getRowDimension(1)->setRowHeight(25);

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
                }

                // Freeze the first row
                $event->sheet->freezePane('A2');
            },
        ];
    }
}
