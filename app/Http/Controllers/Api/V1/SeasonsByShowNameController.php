<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Transformers\SeasonsListTransformer;
use App\Models\Season;
use App\Repositories\ShowRepository;
use Dingo\Api\Http\Response;
use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller;
use Youkoulayley\ApiHandler\Facades\ApiHandler;

/**
 * Class SeasonsByShowIDController.
 *
 * @property ShowRepository showRepository
 */
class SeasonsByShowNameController extends Controller
{
    use Helpers;

    protected $season;

    /**
     * SeasonsByShowIDController constructor.
     */
    public function __construct(
        Season $season,
        ShowRepository $showRepository
    ) {
        $this->season = $season;
        $this->showRepository = $showRepository;
    }

    /**
     * @param $show_name
     */
    public function index($show_name): Response
    {
        $show = $this->showRepository->getByName($show_name);
        $seasons = $this->season::select('name', 'id')->where('show_id', '=', $show->id);

        $seasons = ApiHandler::parseMultiple($seasons, ['name'])->getResult();

        return $this->response->collection($seasons, new SeasonsListTransformer());
    }
}
