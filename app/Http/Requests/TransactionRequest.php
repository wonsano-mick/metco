<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'from_account_id' => 'required_if:type,transfer|exists:accounts,id',
            'to_account_id' => 'required_if:type,transfer|exists:accounts,id|different:from_account_id',
            'account_id' => 'required_if:type,withdrawal,deposit|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:transfer,withdrawal,deposit',
            'pin' => 'required|string|size:4',
            'method' => 'required_if:type,withdrawal,deposit|in:cash,check,bank_transfer,wire',
            'external_reference' => 'nullable|string|max:100',
            'metadata' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'from_account_id.required_if' => 'Source account is required for transfers',
            'to_account_id.required_if' => 'Destination account is required for transfers',
            'to_account_id.different' => 'Source and destination accounts must be different',
            'account_id.required_if' => 'Account is required for this transaction type',
            'amount.min' => 'Amount must be at least 0.01',
            'pin.size' => 'Transaction PIN must be 4 digits',
            'method.required_if' => 'Transaction method is required',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->input('pin') !== Auth::user()->transaction_pin_hash) {
                $validator->errors()->add('pin', 'Invalid transaction PIN');
            }
        });
    }
}
