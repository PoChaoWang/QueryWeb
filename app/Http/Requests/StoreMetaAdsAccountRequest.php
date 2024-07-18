<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMetaAdsAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => [
                'required',
                'regex:/^act_\d+$/',
                'unique:meta_ads_accounts,account_id',
            ],
            'account_name' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'account_id.regex' => 'The account ID must start with "act_" followed by exactly 10 digits.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'account_id' => $this->ensureActPrefix($this->account_id),
        ]);
    }

    private function ensureActPrefix($accountId)
    {
        if (!str_starts_with($accountId, 'act_')) {
            return 'act_' . $accountId;
        }
        return $accountId;
    }
}
