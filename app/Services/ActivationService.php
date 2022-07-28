<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Notifications\ActivationUserNotification;
use App\Repositories\ActivationRepository;
use Illuminate\Mail\Mailer;

/**
 * Class ActivationService.
 */
class ActivationService
{
    protected $mailer;

    protected $activationRepo;

    private $resendAfter = 24;

    /**
     * ActivationService constructor.
     */
    public function __construct(Mailer $mailer, ActivationRepository $activationRepo)
    {
        $this->mailer = $mailer;
        $this->activationRepo = $activationRepo;
    }

    /**
     * Envoi du mail d'activation.
     */
    public function sendActivationMail(User $user)
    {
        if ($user->activated || ! $this->shouldSend($user)) {
            return;
        }

        $token = $this->activationRepo->createActivation($user);

        $user->notify(new ActivationUserNotification($token));
    }

    /**
     * Activation d'un utilisateur.
     *
     * @param $token
     */
    public function activateUser($token): User
    {
        $activation = $this->activationRepo->getActivationByToken($token);

        if (null === $activation) {
            $return = null;
        } else {
            $user = User::find($activation->user_id);

            $user->activated = true;

            $user->save();

            $this->activationRepo->deleteActivation($token);
            $return = $user;
        }

        return $return;
    }

    /**
     * Vérification d'un renvoi du mail d'activation.
     *
     * @param $user
     */
    private function shouldSend($user): bool
    {
        $activation = $this->activationRepo->getActivation($user);

        return null === $activation || strtotime($activation->created_at) + 60 * 60 * $this->resendAfter < time();
    }
}
