<?php

namespace App\Livewire\Loans;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Eloquent\Loan;
use App\Models\Eloquent\LoanCommitteeReview;
use Illuminate\Support\Facades\Auth;

class LoanReview extends Component
{
    public Loan $loan;
    public $loanId;
    public $review = [
        'decision' => 'approve',
        'score' => 7,
        'risk_level' => 'medium',
        'recommendation' => 'approve',
        'comments' => '',
        'conditions' => [],
    ];
    public $newCondition = '';
    public $isSubmitting = false;

    public function mount(Loan $loan)
    {
        $this->loanId = $loan->id;
        $this->loadLoan();
    }

    private function loadLoan()
    {
        $this->loan = Loan::with([
            'customer',
            'account',
            'loanOfficer',
            'committeeReviews.reviewer'
        ])->findOrFail($this->loanId);
    }

    public function addCondition()
    {
        if (!empty($this->newCondition)) {
            $this->review['conditions'][] = $this->newCondition;
            $this->newCondition = '';
        }
    }

    public function removeCondition($index)
    {
        unset($this->review['conditions'][$index]);
        $this->review['conditions'] = array_values($this->review['conditions']);
    }

    public function submitReview()
    {
        $this->validate([
            'review.decision' => 'required|in:approve,reject,refer,hold',
            'review.score' => 'required|integer|min:1|max:10',
            'review.risk_level' => 'required|in:low,medium,high,very_high',
            'review.recommendation' => 'required|string|min:5|max:500',
            'review.comments' => 'nullable|string|max:1000',
            'review.conditions' => 'array',
        ]);

        $this->isSubmitting = true;

        try {
            // Create committee review
            LoanCommitteeReview::create([
                'loan_id' => $this->loan->id,
                'reviewed_by' => Auth::id(),
                'decision' => $this->review['decision'],
                'score' => $this->review['score'],
                'risk_level' => $this->review['risk_level'],
                'recommendation' => $this->review['recommendation'],
                'comments' => $this->review['comments'],
                'conditions' => !empty($this->review['conditions']) ? $this->review['conditions'] : null,
                'reviewed_at' => now(),
            ]);

            // Update loan committee status
            $this->loan->update([
                'committee_status' => 'recommended',
                'status' => $this->review['decision'] === 'approve' ? 'pending' : 'under_review',
            ]);

            $this->dispatch(
                'showToast',
                message: 'Review submitted successfully!',
                type: 'success'
            );

            return redirect()->route('loans.show', $this->loan->id);
        } catch (\Exception $e) {
            $this->dispatch(
                'showToast',
                message: 'Error submitting review: ' . $e->getMessage(),
                type: 'error'
            );
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function getRiskScoreProperty()
    {
        $score = 0;

        // Customer factors
        if ($this->loan->customer->kyc_status === 'verified') $score += 2;
        if ($this->loan->customer->monthly_income >= ($this->loan->monthly_payment * 3)) $score += 3;

        // Loan factors
        if ($this->loan->collateral_value >= $this->loan->amount) $score += 3;
        if ($this->loan->term_months <= 12) $score += 1;
        if ($this->loan->interest_type === 'reducing') $score += 1;

        // Relationship factors
        if ($this->loan->customer->accounts->isNotEmpty()) $score += 2;
        if (!empty($this->loan->guarantors)) $score += 2;

        return min(10, $score);
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.loans.loan-review', [
            'riskScore' => $this->riskScore,
        ]);
    }
}