<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UserChangeInfosRequest.
 *
 * Class UserChangeInfosRequest.
 * @property mixed email
 * @property mixed antispoiler
 * @property mixed twitter
 * @property mixed facebook
 * @property mixed website
 * @property mixed edito
 */
class ChangeInfoRequest extends FormRequest
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
            'email' => 'required|email|max:255|unique:users,email,'.$this->get('id'),
            'antispoiler' => 'boolean',
            'website' => 'max:255',
            'twitter' => 'max:255',
            'facebook' => 'max:255',
        ];
    }
}
