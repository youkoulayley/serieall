<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CommentCreateRequest.
 */
class CommentCreateRequest extends FormRequest
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
            'object_id' => 'required|numeric',
            'object' => 'required|in:Show,Season,Episode,Article',
            'thumb' => 'required|numeric|between:1,3',
            'avis' => 'required|min:100', ];
    }
}
