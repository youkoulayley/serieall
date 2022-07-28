<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleCreateRequest;
use App\Http\Requests\ArticleUpdateRequest;
use App\Models\Article;
use App\Repositories\ArticleRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\EpisodeRepository;
use App\Repositories\SeasonRepository;
use App\Repositories\ShowRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View as View2;
use Illuminate\Support\Str;

/**
 * Class AdminArticleController.
 */
class AdminArticleController extends Controller
{
    protected $articleRepository;

    protected $userRepository;

    protected $episodeRepository;

    protected $seasonRepository;

    protected $showRepository;

    protected $categoryRepository;

    /**
     * AdminArticleController constructor.
     */
    public function __construct(
        ArticleRepository $articleRepository,
        EpisodeRepository $episodeRepository,
        SeasonRepository $seasonRepository,
        ShowRepository $showRepository,
        UserRepository $userRepository,
        CategoryRepository $categoryRepository
    ) {
        $this->articleRepository = $articleRepository;
        $this->userRepository = $userRepository;
        $this->showRepository = $showRepository;
        $this->seasonRepository = $seasonRepository;
        $this->episodeRepository = $episodeRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Print vue admin/articles/index.
     */
    public function index()
    {
        $articles = $this->articleRepository->getAllArticlesWithAutorsCategory();

        return view('admin/articles/index', compact('articles'));
    }

    /**
     * Return the list of articles in JSON.
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getArticle($id)
    {
        $article = $this->articleRepository->getArticleByID($id);

        if (empty($article)) {
            return Response::json();
        }

        return Response::json(View2::make('admin.articles.list_article', ['articles' => [$article]])->render());
    }

    /**
     * Print vue admin/articles/create.
     */
    public function create()
    {
        return view('admin/articles/create');
    }

    /**
     * Print vue admin/articles/edit.
     *
     * @param $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $article = $this->articleRepository->getArticleWithAllInformationsByID($id);

        // FIX: Remove key for Season (the dropdown will show shit)
        if (count($article->seasons) > 0) {
            foreach ($article->seasons as $season) {
                Session::forget($season->name);
            }
        }

        $shows = formatRequestInVariableNoSpace($article->shows);
        $users = formatRequestInVariableUsernameNoSpace($article->users);

        return view('admin/articles/edit', compact('article', 'shows', 'users'));
    }

    /**
     * Save a new article in database.
     *
     * @return RedirectResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function store(ArticleCreateRequest $request)
    {
        // On stocke la requête dans une variable
        $inputs = $request->all();

        // ON initialise l'article
        $article = new Article();

        // On renseigne les champs
        $article->name = $inputs['name'];
        $article->article_url = Str::slug($inputs['name']).'-'.uniqid('3q2bx', true);
        $article->intro = $inputs['intro'];
        $article->content = $inputs['article'];

        // publication
        if (isset($inputs['published'])) {
            $article->state = 1;
            $article->published_at = Carbon::now();
        } else {
            $article->state = 0;
        }

        if (isset($inputs['podcast'])) {
            $article->podcast = 1;
        } else {
            $article->podcast = 0;
        }

        if (1 == $inputs['one'] && ! empty($inputs['show'])) {
            // We fetch the show and initiate image
            $show = $this->showRepository->getShowByID($inputs['show']);
            $article->image = config('directories.original').$show->show_url.'.jpg';
        }

        $destinationPath = public_path().config('directories.original');
        $extension = 'jpg';
        $fileName = $article->article_url.'.'.$extension;

        // Add the image
        if (Request::hasfile('image') && Request::file('image')->isValid()) {
            $article->image = config('directories.original').'/'.$fileName;
            Request::file('image')->move($destinationPath, $fileName);
        } else {
            Log::error('Image'.$destinationPath.'/'.$fileName.' is too big');
        }

        // On lie les catégories et on sauvegarde l'article
        $article->category()->associate($inputs['category']);
        $article->save();

        // On lie les rédacteurs
        $redacs = $inputs['users'];
        $redacs = explode(',', $redacs);

        // Pour chaque rédacteur
        foreach ($redacs as $redac) {
            // On lie le rédacteur à l'article
            $user = $this->userRepository->getByUsername($redac);
            $listRedacs[] = $user->id;
            $article->users()->sync($listRedacs);
        }

        // Si le champ one est à 1 c'est qu'on lie qu'une seule série
        if (1 == $inputs['one']) {
            // Si episode est renseigné, on lie à l'épisode
            if (! empty($inputs['episode'])) {
                $episode = $this->episodeRepository->getEpisodeByIDWithSeasonIDAndShowID($inputs['episode']);

                $article->episodes()->attach($episode->id);
                $article->seasons()->attach($episode->season->id);
                $article->shows()->attach($episode->show->id);
            }
            // Si season est renseigné, on lie à la saison
            elseif (empty($inputs['season'])) {
                $article->shows()->attach($inputs['show']);
            }
            // Sinon, on lie à la série
            elseif (! empty($inputs['show'])) {
                $season = $this->seasonRepository->getSeasonWithShowByID($inputs['season']);
                $article->seasons()->attach($season->id);
                $article->shows()->attach($season->show->id);
            }
        } else {
            // On gère l'ajout de plusieurs séries
            $shows = $inputs['shows'];
            $shows = explode(',', $shows);

            // Pour chaque rédacteur
            foreach ($shows as $show) {
                // On lie la série à l'article
                $listShows[] = $show;
                $article->shows()->sync($listShows);
            }
        }

        //Clear cache
        Cache::delete(ArticleRepository::LAST_6_ARTICLES_CACHE_KEY);

        // On redirige l'utilisateur
        return redirect()->route('admin.articles.index')
            ->with('status_header', 'Ajout d\'un article')
            ->with('status', 'Votre article a été ajouté.');
    }

    /**
     * Delete an article.
     *
     * @param $id
     *
     * @return RedirectResponse
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $article = $this->articleRepository->getArticleByID($id);

        $article->category()->dissociate();
        $article->users()->detach();
        $article->shows()->detach();
        $article->seasons()->detach();
        $article->episodes()->detach();
        $article->delete();

        return redirect()->back()
            ->with('status_header', 'Suppression')
            ->with('status', 'L\'article a été supprimé.');
    }

    /**
     * @return RedirectResponse
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function update(ArticleUpdateRequest $request)
    {
        // On stocke la requête dans une variable
        $inputs = $request->all();

        // ON initialise l'article
        $article = $this->articleRepository->getArticleByID($inputs['id']);
        $category = $this->categoryRepository->getCategoryByName($inputs['category']);

        // On renseigne les champs
        $article->name = $inputs['name'];
        $article->intro = $inputs['intro'];
        $article->content = $inputs['article'];

        // Mise à jour de la plublication uniquement si pas déjà publié
        if (isset($inputs['published'])) {
            if (isset($inputs['alreadyPublished']) && 0 == $inputs['alreadyPublished']) {
                $article->state = 1;
                $article->published_at = Carbon::now();
            }
        } else {
            $article->state = 0;
            $article->published_at = null;
        }

        if (isset($inputs['podcast'])) {
            $article->podcast = 1;
        } else {
            $article->podcast = 0;
        }

        if (1 == $inputs['one'] && ! empty($inputs['show'])) {
            // We fetch the show and initiate image
            $show = $this->showRepository->getByName($inputs['show']);
            $article->image = config('directories.shows').$show->show_url.'.jpg';
        }

        // Add the image
        if (Request::hasfile('image') && Request::file('image')->isValid()) {
            $destinationPath = public_path().config('directories.articles');
            $extension = 'jpg';
            $fileName = $article->article_url.'.'.$extension;

            $article->image = config('directories.articles').$fileName;

            Request::file('image')->move($destinationPath, $fileName);
        }

        // On lie les catégories et on sauvegarde l'article
        $article->category()->associate($category->id);
        $article->save();

        // On lie les rédacteurs
        $redacs = $inputs['users'];
        $redacs = explode(',', $redacs);
        $listRedacs = [];

        // Pour chaque rédacteur
        foreach ($redacs as $redac) {
            // On lie le rédacteur à l'article
            $user = $this->userRepository->getByUsername($redac);
            $listRedacs[] = $user->id;
        }

        $article->users()->sync($listRedacs);

        // Si le champ one est à 1 c'est qu'on lie qu'une seule série
        if (1 == $inputs['one']) {
            // Si episode est renseigné, on lie à l'épisode
            if (! empty($inputs['episode'])) {
                $episode = $this->episodeRepository->getEpisodeByIDWithSeasonIDAndShowID($inputs['episode']);

                $article->episodes()->sync($episode->id);
                $article->seasons()->sync($episode->season->id);
                $article->shows()->sync($episode->show->id);
            }
            // Si season n'est renseigné, on lie à la série
            elseif (empty($inputs['season'])) {
                $article->shows()->sync($inputs['show']);
            }
            // Sinon, on lie à la série
            elseif (! empty($inputs['show'])) {
                $season = $this->seasonRepository->getSeasonWithShowByID($inputs['season']);
                $article->seasons()->attach($season->id);
                $article->shows()->attach($season->show->id);
            }
        } else {
            // On gère l'ajout de plusieurs séries
            $shows = $inputs['shows'];
            $shows = explode(',', $shows);
            $listShows = [];

            // Pour chaque série
            foreach ($shows as $show) {
                $show = $this->showRepository->getByName($show);
                // On lie la série à l'article
                $listShows[] = $show->id;
            }
            $article->shows()->sync($listShows);
        }

        //Clear cache
        Cache::delete(ArticleRepository::LAST_6_ARTICLES_CACHE_KEY);

        // On redirige l'utilisateur
        return redirect()->route('admin.articles.index')
            ->with('status_header', 'Modification d\'un article')
            ->with('status', 'Votre article a été modifié.');
    }
}
