<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Transformers\ShowsAbandonnedTransformer;
use App\Models\Show;
use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller;
use Youkoulayley\ApiHandler\Facades\ApiHandler;

/**
 * Class ShowsListController.
 */
class ShowsAbandonnedController extends Controller
{
    use Helpers;

    protected $shows;

    /**
     * ShowsListController constructor.
     */
    public function __construct(Show $show)
    {
        $this->shows = $show;
    }

    /**
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        $shows = $this->shows->select('id', 'name', 'name_fr')
            ->where('encours', '=', 0)
        ->orderBy('name');

        $shows = ApiHandler::parseMultiple($shows, ['name', 'name_fr'])->getResult();

        return $this->response->collection($shows, new ShowsAbandonnedTransformer());
    }
}
