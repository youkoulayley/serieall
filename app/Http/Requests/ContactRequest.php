<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\NotAllowedDomain;

/**
 * Class ContactRequest.
 */
class ContactRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'email' => ['required', 'email', new NotAllowedDomain()],
            'objet' => 'required|between:10,100',
            'message' => 'required|min:20',
        ];
    }
}
