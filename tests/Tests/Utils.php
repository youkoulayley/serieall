<?php

namespace Tests;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertEqualsWithDelta;
use function PHPUnit\Framework\assertTrue;

/**
 * Utils functions.
 */
class Utils
{
    /**
     * assertUser asserts that the given user matches the wanted user and the wanted password
     * (with a check of the hash value).
     *
     * @param int $id
     * @param User|Model $wantUser
     * @param string $wantPassword
     */
    public function assertUser(int $id, User $wantUser, string $wantPassword)
    {
        $user = User::find($id);

        // Assert Password is ok
        assertTrue(Hash::check($wantPassword, $user->password));

        // Assert timestamps are in a range of 5 secondes delta.
        assertEqualsWithDelta($wantUser->created_at, $user->created_at, 5);
        assertEqualsWithDelta($wantUser->updated_at, $user->updated_at, 5);

        // Before asserting every other field, we set the values we already checked.
        $wantUser->password = $user->password;
        $wantUser->created_at = $user->created_at;
        $wantUser->updated_at = $user->updated_at;

        assertEquals($user->getAttributes(), $wantUser->getAttributes());
    }

    /**
     * assertNoUserInDatabase assert that a list on all users returns an empty Collection.
     */
    public function assertNoUserInDatabase()
    {
        $users = User::all();
        assertCount(0, $users);
    }
}
