<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class EpisodeUpdateRequest.
 */
class EpisodeUpdateRequest extends FormRequest
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
            'tmdb_id' => 'numeric|unique:episodes,tmdb_id,'.$this->get('id'),
            'numero' => 'required|numeric',
            'season_id' => 'required|numeric',
            'name' => 'max:255',
            'name_fr' => 'max:255',
            'diffusion_us' => 'date',
            'diffusion_fr' => 'date',
            'picture' => 'max:255',
            'directors' => ['regex:/^[A-Za-z0-9-.éèàùç%+ ]{1,255}?(,[A-Za-z0-9-.éèàùç%+ ]{1,255})*$/'],
            'writers' => ['regex:/^[A-Za-z0-9-.éèàùç%+ ]{1,255}?(,[A-Za-z0-9-.éèàùç%+ ]{1,255})*$/'],
            'guests' => ['regex:/^[A-Za-z0-9-.éèàùç%+ ]{1,255}?(,[A-Za-z0-9-.éèàùç%+ ]{1,255})*$/'], ];
    }
}
