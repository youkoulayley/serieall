<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CommentCreateRequest.
 */
class CommentUpdateRequest extends FormRequest
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
            'thumb' => 'numeric|between:1,3',
            'avis' => 'required|min:20',
            'show' => 'numeric',
            'season' => 'numeric',
            'episode' => 'numeric',
            'article' => 'numeric',
        ];
    }
}
