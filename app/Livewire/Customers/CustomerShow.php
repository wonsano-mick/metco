<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use App\Models\Eloquent\Customer;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CustomerShow extends Component
{
    public Customer $customer;
    public $showRejectModal;
    public $kycRejectionReason;

    public function mount(Customer $customer)
    {
        if (!Gate::allows('view customers')) {
            abort(403, 'Unauthorized access.');
        }

        $this->customer = $customer->load(['branch', 'relationshipManager', 'accounts.accountType']);
    }

    public function deleteCustomer()
    {
        if (!Gate::allows('delete customers')) {
            abort(403, 'Unauthorized access.');
        }

        // Check if customer has accounts
        if ($this->customer->accounts()->count() > 0) {

            session()->flash('error', 'Cannot delete customer with existing accounts.');
            return redirect()->route('customers.show', $this->customer->id);
        }

        try {
            $customerNumber = $this->customer->customer_number;
            $customerName = $this->customer->full_name;

            $this->customer->update([
                'status' => 'inactive',
                'deleted_at' => now()
            ]);
            session()->flash('success', 'Customer deleted successfully.');
            // Redirect to customer show page
            return redirect()->route('customers.index');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update status' . $e->getMessage());
            return redirect()->route('customers.show', $this->customer->id);
        }
    }

    public function toggleKycStatus()
    {
        if (!Gate::allows('edit customers')) {
            abort(403, 'Unauthorized access.');
        }

        try {
            $newStatus = $this->customer->kyc_status === 'verified' ? 'pending' : 'verified';
            $verifiedAt = $newStatus === 'verified' ? now() : null;

            $this->customer->update([
                'kyc_status' => $newStatus,
                'verified_at' => $verifiedAt,
            ]);

            $this->customer->refresh();

            session()->flash('success', 'KYC status updated to ' . ucfirst($newStatus));
            // Redirect to customer show page
            return redirect()->route('customers.show', $this->customer->id);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update status' . $e->getMessage());
            return redirect()->route('customers.show', $this->customer->id);
        }
    }

    public function toggleStatus()
    {
        if (!Gate::allows('update customers')) {
            abort(403, 'Unauthorized access.');
        }

        try {
            $newStatus = $this->customer->status === 'active' ? 'inactive' : 'active';

            $this->customer->update([
                'status' => $newStatus,
            ]);

            $this->customer->refresh();

            session()->flash('success', 'KYC status updated to '.ucfirst($newStatus));
            // Redirect to customer show page
            return redirect()->route('customers.show', $this->customer->id);

        } catch (\Exception $e) {

            session()->flash('error', 'Failed to update status'.$e->getMessage());
            return redirect()->route('customers.show', $this->customer->id);
        }
    }

    public function verifyKyc()
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\Eloquent\User) {
            return;
        }

        if (!$user->can('verify kyc')) {
            abort(403, 'Unauthorized access.');
        }

        $this->customer->verifyKyc();

        session()->flash('success', 'Customer KYC verified successfully.');
        // Redirect to customer show page
        return redirect()->route('customers.show', $this->customer->id);
    }

    public function openRejectModal()
    {
        $this->showRejectModal = true;
    }

    public function closeRejectModal()
    {
        $this->showRejectModal = false;
        $this->kycRejectionReason = '';
    }

    public function rejectKyc()
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\Eloquent\User) {
            return;
        }
        if (!$user->can('verify kyc')) {
            abort(403, 'Unauthorized access.');
        }

        $this->validate([
            'kycRejectionReason' => 'required|string|min:10|max:500',
        ]);

        $this->customer->rejectKyc($this->kycRejectionReason);
        $this->closeRejectModal();

        session()->flash('info', 'Customer KYC rejected.');
        // Redirect to customer show page
        return redirect()->route('customers.show', $this->customer->id);
    }

    public function markKycPending()
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\Eloquent\User) {
            return;
        }

        if (!$user->can('verify kyc')) {
            abort(403, 'Unauthorized access.');
        }

        $this->customer->pendingKyc();

        session()->flash('info', 'Customer KYC status set to pending');
        // Redirect to customer show page
        return redirect()->route('customers.show', $this->customer->id);
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.customers.customer-show');
    }
}
