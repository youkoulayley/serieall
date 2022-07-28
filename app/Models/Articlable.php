<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Articlable.
 *
 * @property int    $article_id
 * @property int    $articlable_id
 * @property string $articlable_type
 * @method static Builder|Articlable newModelQuery()
 * @method static Builder|Articlable newQuery()
 * @method static Builder|Articlable query()
 * @method static Builder|Articlable whereArticlableId($value)
 * @method static Builder|Articlable whereArticlableType($value)
 * @method static Builder|Articlable whereArticleId($value)
 * @mixin Eloquent
 */
class Articlable extends Model
{
    protected $table = 'articlables';

    public $timestamps = false;

    protected $fillable = [
        'article_id',
        'articlable_id',
        'articlable_type',
    ];
}
