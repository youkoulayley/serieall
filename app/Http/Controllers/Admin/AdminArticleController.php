<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleCreateRequest;
use App\Models\Article;

use App\Repositories\ArticleRepository;
use App\Repositories\EpisodeRepository;
use App\Repositories\SeasonRepository;
use App\Repositories\ShowRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Input;

/**
 * Class AdminArticleController
 * @package App\Http\Controllers\Admin
 */
class AdminArticleController extends Controller
{

    protected $articleRepository;
    protected $userRepository;
    protected $episodeRepository;
    protected $seasonRepository;
    protected $showRepository;

    /**
     * AdminArticleController constructor.
     *
     * @param ArticleRepository $articleRepository
     * @param EpisodeRepository $episodeRepository
     * @param SeasonRepository $seasonRepository
     * @param ShowRepository $showRepository
     * @param UserRepository $userRepository
     */
    public function __construct(ArticleRepository $articleRepository,
                                EpisodeRepository $episodeRepository,
                                SeasonRepository $seasonRepository,
                                ShowRepository $showRepository,
                                UserRepository $userRepository) {
        $this->articleRepository = $articleRepository;
        $this->userRepository = $userRepository;
        $this->showRepository = $showRepository;
        $this->seasonRepository = $seasonRepository;
        $this->episodeRepository = $episodeRepository;
    }

    /**
     * Print vue admin/articles/index
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index() {
        $articles = $this->articleRepository->getAllArticlesWithAutorsCategory();

        return view('admin/articles/index', compact('articles'));
    }

    /**
     * Print vue admin/articles/create
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create() {

        return view('admin/articles/create');
    }

    /**
     * Save a new article in database
     *
     * @param ArticleCreateRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function store(ArticleCreateRequest $request) {
        // On stocke la requête dans une variable
        $inputs = $request->all();

        // ON initialise l'article
        $article = new Article();

        // On renseigne les champs
        $article->name = $inputs['name'];
        $article->article_url = str_slug($inputs['name']) . '-' . uniqid('article', true);
        $article->intro = $inputs['intro'];
        $article->content = $inputs['article'];

        // Si publié n'est pas présent, on le met à 1 sinon, c'est 0
        if (isset($inputs['published'])) {
            $article->state = 1;
            $article->published_at = Carbon::now();
        }
        else
        {
            $article->state = 0;
        }

        // Si une n'est pas présent, on le met à 1 sinon, c'est 0
        if (isset($inputs['une'])) {
            $article->frontpage = 1;
        }
        else{
            $article->frontpage = 0;
        }

        if($inputs['one'] == 1) {
            // We fetch the show and initiate image
            $show = $this->showRepository->getShowByID($inputs['show']);
            $article->image = config('directories.shows') . $show->show_url . '.jpg';
        }

        # Add the image
        if (Input::hasfile('image') && Input::file('image')->isValid()) {
            $destinationPath = public_path() . config('directories.articles');
            $extension = 'jpg';
            $fileName = $article->article_url . '.' . $extension;

            $article->image = config('directories.articles') . $fileName;

            Input::file('image')->move($destinationPath, $fileName);
        }

        // On lie les catégories et on sauvegarde l'article
        $article->category()->associate($inputs['category']);
        $article->save();

        // On lie les rédacteurs
        $redacs = $inputs['users'];
        $redacs = explode(',', $redacs);

        # Pour chaque rédacteur
        foreach ($redacs as $redac) {
            # On lie le rédacteur à l'article
            $listRedacs[] = $redac;
            $article->users()->sync($listRedacs);
        }

        // Si le champ one est à 1 c'est qu'on lie qu'une seule série
        if($inputs['one'] == 1) {
            // Si episode est renseigné, on lie à l'épisode
            if(!empty($inputs['episode'])) {
                $episode = $this->episodeRepository->getEpisodeByIDWithSeasonIDAndShowID($inputs['episode']);

                $article->episodes()->attach($episode->id);
                $article->seasons()->attach($episode->season->id);
                $article->shows()->attach($episode->show->id);
            }
            // Si season est renseigné, on lie à la saison
            elseif(empty($inputs['season'])) {
                $article->shows()->attach($inputs['show']);
            }
            // Sinon, on lie à la série
            else {
                $season = $this->seasonRepository->getSeasonWithShowByID($inputs['season']);
                $article->seasons()->attach($season->id);
                $article->shows()->attach($season->show->id);
            }
        }
        else {
            // On gère l'ajout de plusieurs séries
            $shows = $inputs['shows'];
            $shows = explode(',', $shows);

            # Pour chaque rédacteur
            foreach ($shows as $show) {
                # On lie la série à l'article
                $listShows[] = $show;
                $article->shows()->sync($listShows);
            }
        }

        // On redirige l'utilisateur
        return redirect(route('admin.articles.index'))
            ->with('status_header', 'Ajout d\'un article')
            ->with('status', 'Votre article a été ajouté.');
    }

    /**
     * Delete an article
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id) {
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

}