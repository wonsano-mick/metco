<?php

namespace App\Livewire\Interest\Processing;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Eloquent\AccountInterest;
use App\Services\Transaction\AutomatedInterestService;
use Carbon\Carbon;

class InterestProcessingIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $dateFrom;
    public $dateTo;
    public $postingDate;
    public $showProcessingModal = false;
    public $processingResults = null;
    public $selectedInterest = null;

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
        $this->postingDate = now()->format('Y-m-d');
    }

    public function generatePending()
    {
        $this->validate([
            'postingDate' => 'required|date',
        ]);

        $interestService = app(AutomatedInterestService::class);
        $this->processingResults = $interestService->generatePendingInterest(
            Carbon::parse($this->postingDate)
        );

        $this->showProcessingModal = true;
    }

    public function processPending()
    {
        $this->validate([
            'postingDate' => 'required|date',
        ]);

        $interestService = app(AutomatedInterestService::class);
        $this->processingResults = $interestService->processPendingInterest(
            Carbon::parse($this->postingDate)
        );

        $this->showProcessingModal = true;
    }

    public function retryInterest($interestId)
    {
        $interest = AccountInterest::find($interestId);

        if ($interest && $interest->status === 'failed') {
            $interestService = app(AutomatedInterestService::class);

            // Reprocess just this interest
            $result = $interestService->processPendingInterest(
                Carbon::parse($interest->posting_date)
            );

            session()->flash('message', 'Interest reprocessed successfully.');
        }
    }

    public function viewDetails($interestId)
    {
        $this->selectedInterest = AccountInterest::with(['account', 'interestConfiguration'])
            ->find($interestId);
    }

    public function render()
    {
        $interests = AccountInterest::query()
            ->with(['account', 'interestConfiguration'])
            ->when($this->search, function ($query) {
                $query->whereHas('account', function ($q) {
                    $q->where('account_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->dateFrom, function ($query) {
                $query->where('posting_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->where('posting_date', '<=', $this->dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.interest.processing.index', [
            'interests' => $interests,
            'stats' => [
                'pending' => AccountInterest::where('status', 'pending')->count(),
                'processed' => AccountInterest::where('status', 'processed')->count(),
                'failed' => AccountInterest::where('status', 'failed')->count(),
                'total_pending_amount' => AccountInterest::where('status', 'pending')->sum('amount'),
            ],
        ]);
    }
}
