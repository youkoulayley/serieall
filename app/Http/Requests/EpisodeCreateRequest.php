<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class EpisodeCreateRequest.
 */
class EpisodeCreateRequest extends FormRequest
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
            'episodes.*.numero' => 'required|numeric',
            'episodes.*.name' => 'max:255',
            'episodes.*.name_fr' => 'max:255',
            'episodes.*.diffusion_us' => 'date',
            'episodes.*.diffusion_fr' => 'date',
            'episodes.*.directors' => ['regex:/^[A-Za-z0-9-éèàùç%+. ]{1,255}?(,[A-Za-z0-9-éèàùç%+. ]{1,255})*$/'],
            'episodes.*.writers' => ['regex:/^[A-Za-z0-9-éèàùç%+. ]{1,255}?(,[A-Za-z0-9-éèàùç%+. ]{1,255})*$/'],
            'episodes.*.guests' => ['regex:/^[A-Za-z0-9-éèàùç%+. ]{1,255}?(,[A-Za-z0-9-éèàùç%+. ]{1,255})*$/'], ];
    }
}
