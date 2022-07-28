<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class SeasonUpdateRequest.
 */
class SeasonUpdateRequest extends FormRequest
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
            'tmdb_id' => 'numeric|unique:seasons,tmdb_id,'.$this->get('id'),
            'name' => 'required|numeric', ];
    }
}
