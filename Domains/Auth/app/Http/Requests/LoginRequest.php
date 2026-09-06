<?php

namespace Domains\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            /**
             * The email address used to sign in.
             *
             * @example jane@example.com
             */
            'email' => ['required', 'email'],

            /**
             * The account password.
             *
             * @example correct-horse-battery
             */
            'password' => ['required', 'string'],
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
