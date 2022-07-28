<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Transformers\ArticlesListTransformer;
use App\Models\Article;
use Dingo\Api\Http\Response;
use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Request;

/**
 * Class ShowsListController.
 */
class ArticlesListController extends Controller
{
    use Helpers;

    protected $shows;

    /**
     * @var Article
     */
    private $articles;

    /**
     * ShowsListController constructor.
     */
    public function __construct(Article $article)
    {
        $this->articles = $article;
    }

    /**
     * _limit : add limitation to request result.
     */
    public function index(): Response
    {
        //Retrieve search request and split in array of word
        $this->searchQueryWordArray = explode(' ', Request::input('name-lk'));

        //Refactoring search
        $articles = $this->articles->select('id', 'name')
                ->where(function ($query) {
                    foreach ($this->searchQueryWordArray  as $searchQueryWord) {
                        $query->where('name', 'like', '%'.$searchQueryWord.'%');
                    }
                })
                ->orderBy('published_at', 'desc');

        //Limit result, if needed
        if (Request::has('_limit')) {
            $limit = intval(Request::input('_limit'));
            if ($limit > 0) {
                $articles = $articles->limit($limit);
            }
        }

        return $this->response->collection($articles->get(), new ArticlesListTransformer());
    }
}
