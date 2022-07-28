<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\User_activation.
 *
 * @property int    $user_id
 * @property string $token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|User_activation newModelQuery()
 * @method static Builder|User_activation newQuery()
 * @method static Builder|User_activation query()
 * @method static Builder|User_activation whereCreatedAt($value)
 * @method static Builder|User_activation whereToken($value)
 * @method static Builder|User_activation whereUpdatedAt($value)
 * @method static Builder|User_activation whereUserId($value)
 * @mixin Eloquent
 */
class User_activation extends Model
{
    protected $table = 'user_activations';

    public $timestamps = true;
}
