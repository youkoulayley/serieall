<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\ArticleFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Models\Article.
 *
 * @property Carbon $published_at
 * @property string $name
 * @property string $article_url
 * @property string $intro
 * @property string $content
 * @property string $image
 * @property string $source
 * @property bool   $state
 * @property bool   $frontpage
 * @property int    $category_id
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $podcast
 * @property-read Collection|Artist[] $artists
 * @property-read int|null $artists_count
 * @property-read Category $category
 * @property-read Collection|Channel[] $channels
 * @property-read int|null $channels_count
 * @property-read Collection|Comment[] $comments
 * @property-read int|null $comments_count
 * @property-read Collection|Episode[] $episodes
 * @property-read int|null $episodes_count
 * @property-read Collection|Season[] $seasons
 * @property-read int|null $seasons_count
 * @property-read Collection|Show[] $shows
 * @property-read int|null $shows_count
 * @property-read Collection|User[] $users
 * @property-read int|null $users_count
 * @method static ArticleFactory factory(...$parameters)
 * @method static Builder|Article newModelQuery()
 * @method static Builder|Article newQuery()
 * @method static Builder|Article query()
 * @method static Builder|Article whereArticleUrl($value)
 * @method static Builder|Article whereCategoryId($value)
 * @method static Builder|Article whereContent($value)
 * @method static Builder|Article whereCreatedAt($value)
 * @method static Builder|Article whereFrontpage($value)
 * @method static Builder|Article whereId($value)
 * @method static Builder|Article whereImage($value)
 * @method static Builder|Article whereIntro($value)
 * @method static Builder|Article whereName($value)
 * @method static Builder|Article wherePodcast($value)
 * @method static Builder|Article wherePublishedAt($value)
 * @method static Builder|Article whereSource($value)
 * @method static Builder|Article whereState($value)
 * @method static Builder|Article whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Article extends Model
{
    use HasFactory;

    protected $table = 'articles';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'article_url',
        'intro',
        'content',
        'image',
        'source',
        'state',
        'frontpage',
        'category_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User');
    }

    public function episodes(): MorphToMany
    {
        return $this->morphedByMany('App\Models\Episode', 'articlable');
    }

    public function seasons(): MorphToMany
    {
        return $this->morphedByMany('App\Models\Season', 'articlable');
    }

    public function shows(): MorphToMany
    {
        return $this->morphedByMany('App\Models\Show', 'articlable');
    }

    public function artists(): MorphToMany
    {
        return $this->morphedByMany('App\Models\Artist', 'articlable');
    }

    public function channels(): MorphToMany
    {
        return $this->morphedByMany('App\Models\Channel', 'articlable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany('App\Models\Comment', 'commentable');
    }
}
