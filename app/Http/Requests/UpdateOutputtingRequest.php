<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOutputtingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'sheetId' => 'required|string|max:255',
            'sheetName' => 'required|string|max:255',
            'append' => 'required|boolean',
        ];
    }
}
