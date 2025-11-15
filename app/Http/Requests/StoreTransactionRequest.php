<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class StoreTransactionRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'receiver_id' => [
                'required',
                'integer',
                'exists:users,id',
                'different:'.$this->user()->id,
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999999999.99',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isEmpty()) {
                $amount = $this->input('amount');
                $commissionRate = config('wallet.commission_rate');
                $commission = $amount * $commissionRate;
                $totalRequired = $amount + $commission;
                $commissionPercentage = $commissionRate * 100;

                if ($this->user()->balance < $totalRequired) {
                    $validator->errors()->add(
                        'amount',
                        'Insufficient balance. You need '.number_format((float) $totalRequired, 2).' (including '.$commissionPercentage.'% commission) but only have '.number_format((float) $this->user()->balance, 2).'.'
                    );
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'receiver_id.required' => 'Please select a recipient.',
            'receiver_id.exists' => 'The selected recipient does not exist.',
            'receiver_id.different' => 'You cannot send money to yourself.',
            'amount.required' => 'Please enter an amount to send.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The minimum amount to send is 0.01.',
            'amount.max' => 'The amount is too large.',
        ];
    }
}
