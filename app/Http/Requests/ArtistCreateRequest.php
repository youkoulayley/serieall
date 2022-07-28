<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ArtistCreateRequest.
 */
class ArtistCreateRequest extends FormRequest
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
            'artists.*.image' => 'image',
            'artists.*.name' => 'required|max:255',
            'artists.*.role' => 'required|max:255', ];
    }
}
