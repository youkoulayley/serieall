<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Transformers\UsersListTransformer;
use App\Models\User;
use Dingo\Api\Http\Response;
use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Request;
use Youkoulayley\ApiHandler\Facades\ApiHandler;

/**
 * Class UsersListController.
 */
class UsersListController extends Controller
{
    use Helpers;
    protected $users;

    /**
     * UsersListController constructor.
     */
    public function __construct(User $users)
    {
        $this->users = $users;
    }

    public function index(): Response
    {
        $users = $this->users::select('id', 'username')
            ->orderBy('username');

        //Limit result, if needed
        if (Request::has('_limit')) {
            $limit = intval(Request::input('_limit'));
            if ($limit > 0) {
                $users = $users->limit($limit);
            }
        }

        $users = ApiHandler::parseMultiple($users, ['username'])->getResult();

        return $this->response->collection($users, new UsersListTransformer());
    }
}
