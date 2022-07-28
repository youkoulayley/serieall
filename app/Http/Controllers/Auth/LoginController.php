<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Packages\Hashing\YourHasher;
use App\Services\ActivationService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class LoginController.
 */
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    protected $activationService;

    protected $hashingProvider;

    /**
     * LoginController constructor.
     */
    public function __construct(ActivationService $activationService, YourHasher $hashingProvider)
    {
        $this->middleware('guest', ['except' => 'logout']);
        $this->activationService = $activationService;
        $this->hashingProvider = $hashingProvider;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @return RedirectResponse|JsonResponse
     *
     * @throws \RuntimeException
     */
    protected function sendLoginResponse(Request $request)
    {
        // Si le mot de passe n'est pas hashé avec Bcrypt
        if ($this->hashingProvider->needsRehash(Auth::user()->password)) {
            // On regénre un mot de passe avec Bcrypt
            Auth::user()->password = $this->hashingProvider->make($request->password);
            // On sauvegarde l'utilisateur
            Auth::user()->save();
        }

        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        return $this->authenticated($this->guard()->user())
            ?: redirect()->intended($this->redirectPath());
    }

    /**
     * Get the login username to be used by the controller.
     */
    public function username(): string
    {
        return 'username';
    }

    /**
     * @param $user
     *
     * @return \Illuminate\Http\JsonResponse|RedirectResponse
     */
    public function authenticated($user)
    {
        if (! $user->activated) {
            $this->activationService->sendActivationMail($user);
            auth()->logout();

            return response()->json(['suspended' => false, 'activated' => false]);
        }

        if ($user->suspended) {
            auth()->logout();

            return response()->json(['suspended' => true, 'activated' => true]);
        }

        return response()->json(['suspended' => false, 'activated' => true]);
    }

    /**
     * @param $token
     */
    public function activateUser($token): RedirectResponse
    {
        $this->activationService->activateUser($token) ? redirect()->route('login')->with('success', 'Votre adresse E-Mail a été validée. Vous pouvez maintenant vous connecter.') : redirect()->route('login')->with('error', 'Erreur lors de la validation de votre adresse mail. Vous l\'avez peut être déjà validée. En cas de problèmes, n\'hésitez pas à nous contacter à l\'adresse : serieall.fr@gmail.com');

        return redirect()->intended($this->redirectPath());
    }
}
