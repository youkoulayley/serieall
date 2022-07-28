<?php

namespace App\Jobs;

use App\Repositories\UserRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class UserUpdate.
 */
class UserUpdate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $inputs;

    /**
     * Create a new job instance.
     *
     * @param $inputs
     *
     * @internal param $id
     * @internal param $userID
     */
    public function __construct($inputs)
    {
        $this->inputs = $inputs;
    }

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function handle(UserRepository $userRepository)
    {
        /*
        |--------------------------------------------------------------------------
        | Initialisation du job
        |--------------------------------------------------------------------------
        */
        $idLog = initJob($this->inputs['user_id'], 'Edition', 'User', $this->inputs['id']);

        $user = $userRepository->getByID($this->inputs['id']);

        $message = 'Username : '.$this->inputs['username'];
        saveLogMessage($idLog, $message);
        $user->username = $this->inputs['username'];

        $message = 'Rôle : '.$this->inputs['role'];
        saveLogMessage($idLog, $message);
        $user->role = $this->inputs['role'];

        if (! empty($this->inputs['password'])) {
            $user->password = bcrypt($this->inputs['password']);
        }

//        $message = 'E-Mail : ' . $this->inputs['email'];
//        saveLogMessage($idLog, $message);
//        $user->email = $this->inputs['email'];

        $message = 'Edito : '.$this->inputs['edito'];
        saveLogMessage($idLog, $message);
        $user->edito = $this->inputs['edito'];

        $message = 'Antispoiler : '.$this->inputs['antispoiler'];
        saveLogMessage($idLog, $message);
        $user->antispoiler = $this->inputs['antispoiler'];

        $message = 'Site Internet : '.$this->inputs['website'];
        saveLogMessage($idLog, $message);
        $user->website = $this->inputs['website'];

        $message = 'Twitter : '.$this->inputs['twitter'];
        saveLogMessage($idLog, $message);
        $user->twitter = $this->inputs['twitter'];

        $message = 'Facebook : '.$this->inputs['facebook'];
        saveLogMessage($idLog, $message);
        $user->facebook = $this->inputs['facebook'];

        $user->save();

        endJob($idLog);
    }
}
