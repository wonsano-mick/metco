<?php

namespace App\Livewire\Fee\Processing;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Eloquent\AccountFee;
use App\Services\Transaction\AutomatedFeeService;
use Carbon\Carbon;

class FeeProcessingIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $dateFrom;
    public $dateTo;
    public $processingDate;
    public $showProcessingModal = false;
    public $processingResults = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
        $this->processingDate = now()->format('Y-m-d');
    }

    public function generatePending()
    {
        $this->validate([
            'processingDate' => 'required|date',
        ]);

        $feeService = app(AutomatedFeeService::class);
        $this->processingResults = $feeService->generatePendingFees(
            Carbon::parse($this->processingDate)
        );

        $this->showProcessingModal = true;
    }

    public function processPending()
    {
        $this->validate([
            'processingDate' => 'required|date',
        ]);

        $feeService = app(AutomatedFeeService::class);
        $this->processingResults = $feeService->processPendingFees(
            Carbon::parse($this->processingDate)
        );

        $this->showProcessingModal = true;
    }

    #[Layout('layouts.main')]
    public function render()
    {
        $fees = AccountFee::query()
            ->with(['account', 'feeConfiguration'])
            ->when($this->search, function ($query) {
                $query->whereHas('account', function ($q) {
                    $q->where('account_number', 'like', '%' . $this->search . '%');
                })->orWhereHas('feeConfiguration', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->dateFrom, function ($query) {
                $query->where('charge_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->where('charge_date', '<=', $this->dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.fee.processing.index', [
            'fees' => $fees,
            'stats' => [
                'pending' => AccountFee::where('status', 'pending')->count(),
                'processed' => AccountFee::where('status', 'processed')->count(),
                'failed' => AccountFee::where('status', 'failed')->count(),
                'total_pending_amount' => AccountFee::where('status', 'pending')->sum('amount'),
            ],
        ]);
    }
}
