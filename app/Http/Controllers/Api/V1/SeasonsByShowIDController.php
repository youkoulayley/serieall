<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Transformers\SeasonsListTransformer;
use App\Models\Season;
use Dingo\Api\Http\Response;
use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller;
use Youkoulayley\ApiHandler\Facades\ApiHandler;

/**
 * Class SeasonsByShowIDController.
 */
class SeasonsByShowIDController extends Controller
{
    use Helpers;

    protected $seasons;

    /**
     * SeasonsByShowIDController constructor.
     */
    public function __construct(Season $season)
    {
        $this->seasons = $season;
    }

    /**
     * @param $showID
     */
    public function index($showID): Response
    {
        $seasons = $this->seasons::select('id', 'name')->where('show_id', '=', $showID)->orderBy('name');

        $seasons = ApiHandler::parseMultiple($seasons, ['name'])->getResult();

        return $this->response->collection($seasons, new SeasonsListTransformer());
    }
}
