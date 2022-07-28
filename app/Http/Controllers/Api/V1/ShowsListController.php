<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Transformers\ShowsListTransformer;
use App\Models\Show;
use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller;
use Youkoulayley\ApiHandler\Facades\ApiHandler;

/**
 * Class ShowsListController.
 */
class ShowsListController extends Controller
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
        $shows = $this->shows::with(['genres' => function ($q) {
            $q->select('name');
        }])
        ->select('id', 'name', 'name_fr')
        ->orderBy('name');

        $shows = ApiHandler::parseMultiple($shows, ['name', 'name_fr'])->getResult();

        return $this->response->collection($shows, new ShowsListTransformer());
    }
}
