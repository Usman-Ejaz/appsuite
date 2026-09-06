<?php

namespace Domains\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TokenRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            /**
             * The public identifier of the API key being authenticated.
             *
             * @example ak_51NfQ2example
             */
            'api_key' => ['required', 'string'],

            /**
             * The secret paired with `api_key`, used to verify the request.
             *
             * @example sk_51NfQ2exampleSecret
             */
            'api_secret' => ['required', 'string'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
