<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Article;
use App\Repositories\ArticleRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\CommentRepository;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use wapmorgan\Mp3Info\Mp3Info;
use Youkoulayley\PodcastFeed\Facades\PodcastFeed;

/**
 * Class ArticleController.
 */
class ArticleController extends Controller
{
    protected ArticleRepository $articleRepository;

    protected CategoryRepository $categoryRepository;

    protected CommentRepository $commentRepository;

    /**
     * ArticleController constructor.
     *
     * @internal param ShowRepository $showRepository
     */
    public function __construct(
        ArticleRepository $articleRepository,
        CategoryRepository $categoryRepository,
        CommentRepository $commentRepository
    ) {
        $this->articleRepository = $articleRepository;
        $this->categoryRepository = $categoryRepository;
        $this->commentRepository = $commentRepository;
    }

    /**
     * Print the articles/index vue.
     * Liste complète des articles.
     *
     * @return View
     */
    public function index()
    {
        $articles = $this->articleRepository->getPublishedArticlesWithAutorsCommentsAndCategory();
        $category = 'all';
        $articles_count = count($articles);
        $categories = $this->categoryRepository->getAllCategories();

        return view('articles.index', compact('articles', 'category', 'articles_count', 'categories'));
    }

    /**
     * Print the articles/indexCategory vue.
     *
     * @param $categoryName name of category to display
     *
     * @return View
     */
    public function indexByCategory($categoryName)
    {
        $categories = $this->categoryRepository->getAllCategories();
        $categoryInDB = $this->categoryRepository->getCategoryByName($categoryName);

        if (! is_null($categoryInDB)) {
            $articles = $this->articleRepository->getPublishedArticlesByCategoriesWithAutorsCommentsAndCategory($categoryInDB->id);

            $articles_count = count($articles);
            $category = $categoryInDB->name;

            return view('articles.index', compact('categories', 'category', 'articles', 'articles_count'));
        } else {
            //No category found : 404
            abort(404);
        }
    }

    /**
     * Print the article by its URL.
     *
     * @param $articleURL
     *
     * @internal param $id
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function show($articleURL)
    {
        $user_id = getIDIfAuth();
        $article = $this->articleRepository->getArticleByURL($articleURL);
        $object = compileObjectInfos('Article', $article->id);
        $comments = $this->commentRepository->getCommentsForFiche($user_id, $object['fq_model'], $object['id']);

        if (1 == $article->shows_count) {
            if ($article->seasons_count >= 1) {
                $type_article = 'Season';
                if ($article->episodes_count >= 1) {
                    foreach ($article->seasons as $season) {
                        $articles_linked = $this->articleRepository->getPublishedArticleBySeasonID($article->id, $season->id);
                    }
                } else {
                    foreach ($article->seasons as $season) {
                        $articles_linked = $this->articleRepository->getPublishedArticleBySeasonID($article->id, $season->id);
                    }
                }
            } else {
                // C'est un article sur une série
                $type_article = 'Show';
                foreach ($article->shows as $show) {
                    $articles_linked = $this->articleRepository->getPublishedArticleByShowID($article->id, $show->id);
                }
            }
        } else {
            $type_article = '';
            $articles_linked = $this->articleRepository->getPublishedSimilaryArticles($article->id, $article->category_id);
        }

        if (Request::ajax()) {
            return Response::json(View::make('comments.comment_article', ['comments' => $comments])->render());
        } else {
            return view('articles.show', compact('article', 'comments', 'object', 'type_article', 'articles_linked'));
        }
    }

    public function RSSPodcast()
    {
        $podcasts = Article::where('podcast', '=', true)->where('state', '=', 1)->get();

        foreach ($podcasts as $podcast) {
            $filename = public_path('podcasts/').$podcast->article_url.'.mp3';

            $audio = new Mp3Info($filename, true);
            $duration = gmdate('H:i:s', intval($audio->duration));

            PodcastFeed::addMedia([
                'title' => $podcast->name,
                'description' => $podcast->intro,
                'publish_at' => $podcast->published_at,
                'guid' => route('article.show', $podcast->article_url),
                'url' => 'https://serieall.fr/podcasts/'.$podcast->article_url.'.mp3',
                'duration' => $duration,
                'type' => 'audio/mp3',
                'image' => 'https://serieall.fr'.$podcast->image,
            ]);
        }

        return Response::make(PodcastFeed::toString())
            ->header('Content-Type', 'text/xml');
    }
}
