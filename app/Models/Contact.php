<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Contact.
 *
 * @property string $name
 * @property string $email
 * @property string $objet
 * @property string $message
 * @property int    $admin_id
 * @property string $admin_message
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @method static Builder|Contact newModelQuery()
 * @method static Builder|Contact newQuery()
 * @method static Builder|Contact query()
 * @method static Builder|Contact whereAdminId($value)
 * @method static Builder|Contact whereAdminMessage($value)
 * @method static Builder|Contact whereCreatedAt($value)
 * @method static Builder|Contact whereEmail($value)
 * @method static Builder|Contact whereId($value)
 * @method static Builder|Contact whereMessage($value)
 * @method static Builder|Contact whereName($value)
 * @method static Builder|Contact whereObjet($value)
 * @method static Builder|Contact whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Contact extends Model
{
    protected $table = 'contacts';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'email',
        'objet',
        'message',
        'admin_id',
        'admin_message',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'admin_id');
    }
}
