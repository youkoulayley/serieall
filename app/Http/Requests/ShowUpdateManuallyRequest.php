<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ShowUpdateManuallyRequest.
 */
class ShowUpdateManuallyRequest extends FormRequest
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
            'tmdb_id' => 'required|numeric|unique:shows,tmdb_id,'.$this->get('id'),
            'name_fr' => 'max:255',
            'creators' => ['regex:/^[A-Za-z0-9-éèàùç%+. ]{1,255}?(,[A-Za-z0-9-éèàùç%+. ]{1,255})*$/'],
            'nationalities' => ['regex:/^[A-Za-z0-9-éèàùç%+. ]{1,255}?(,[A-Za-z0-9-éèàùç%+. ]{1,255})*$/'],
            'channels' => ['regex:/^[A-Za-z0-9-éèàùç%+(). ]{1,255}?(,[A-Za-z0-9-éèàùç%+(). ]{1,255})*$/'],
            'genres' => ['regex:/^[A-Za-z0-9-éèàùç%+(). ]{1,255}?(,[A-Za-z0-9-éèàùç%+(). ]{1,255})*$/'],
            'diffusion_us' => 'date',
            'diffusion_fr' => 'date',
            'particularite' => 'max:255',
            'poster' => 'image|max:2000',
            'taux_erectile' => 'numeric|between:0,100', ];
    }
}
