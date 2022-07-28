<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Article_user.
 *
 * @property int $article_id
 * @property int $user_id
 * @method static Builder|Article_user newModelQuery()
 * @method static Builder|Article_user newQuery()
 * @method static Builder|Article_user query()
 * @method static Builder|Article_user whereArticleId($value)
 * @method static Builder|Article_user whereUserId($value)
 * @mixin Eloquent
 */
class Article_user extends Model
{
    protected $table = 'article_user';

    public $timestamps = false;

    protected $fillable = [
        'article_id',
        'user_id',
    ];
}
