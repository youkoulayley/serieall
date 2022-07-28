<?php

declare(strict_types=1);

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Connection;
use Illuminate\Support\Str;

/**
 * Class ActivationRepository.
 */
class ActivationRepository
{
    protected $db;

    protected $table = 'user_activations';

    /**
     * ActivationRepository constructor.
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * On génére un token en fonction de la clé de l'application.
     */
    protected function getToken(): string
    {
        return hash_hmac('sha256', Str::random(40), config('app.key'));
    }

    /**
     * On crée l'activation en fonction de l'utilisateur.
     *
     * @param $user
     */
    public function createActivation($user): string
    {
        $activation = $this->getActivation($user);

        if ($activation) {
            $return = $this->regenerateToken($user);
        } else {
            $return = $this->createToken($user);
        }

        return $return;
    }

    /**
     * On regénère le token pour un utilisateur.
     *
     * @param $user
     */
    private function regenerateToken($user): string
    {
        $token = $this->getToken();
        $this->db->table($this->table)->where('user_id', $user->id)->update([
            'token' => $token,
            'created_at' => new Carbon(),
        ]);

        return $token;
    }

    /**
     * On crée un token pour un utilisateur.
     *
     * @param $user
     */
    private function createToken($user): string
    {
        $token = $this->getToken();
        $this->db->table($this->table)->insert([
            'user_id' => $user->id,
            'token' => $token,
            'created_at' => new Carbon(),
        ]);

        return $token;
    }

    /**
     * On récupère l'activation d'un utilisateur.
     *
     * @param $user
     *
     * @return mixed
     */
    public function getActivation($user)
    {
        return $this->db->table($this->table)->where('user_id', $user->id)->first();
    }

    /**
     * On récupère une activation en fonction du token.
     *
     * @param $token
     *
     * @return mixed
     */
    public function getActivationByToken($token)
    {
        return $this->db->table($this->table)->where('token', $token)->first();
    }

    /**
     * On supprime une activation.
     *
     * @param $token
     */
    public function deleteActivation($token)
    {
        $this->db->table($this->table)->where('token', $token)->delete();
    }
}
