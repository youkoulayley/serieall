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
 * Class SeasonsListController.
 */
class SeasonsListController extends Controller
{
    use Helpers;

    protected $seasons;

    /**
     * SeasonsListController constructor.
     */
    public function __construct(Season $season)
    {
        $this->seasons = $season;
    }

    public function index(): Response
    {
        $seasons = $this->seasons::select('id', 'name', 'show_id');

        $seasons = ApiHandler::parseMultiple($seasons, ['name', 'show_id'])->getResult();

        return $this->response->collection($seasons, new SeasonsListTransformer());
    }
}
