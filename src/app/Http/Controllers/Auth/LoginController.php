<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\RequestAPI;
use Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

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

    use AuthenticatesUsers, RequestAPI;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('pages.auth.login');
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];
        $token = $this->loginAIServer($request);

        if(Auth::attempt($credentials)) {
            session(['ai_token' => $token]);
            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    protected function loginAIServer(Request $request)
    {
        $data = $request->only(['email', 'password']);

        $response = $this->sendPOSTRequest(config('app.ai_server') . '/users/login', [], [
            'X-API-KEY' => config('app.ai_api_key'),
            'Authorization' => 'Basic ' . base64_encode($data['email'] . ':' . $data['password'])
        ]);

        if (!$response->status) {
            return $this->sendFailedLoginResponse($request);
        }

        return $response->body->token;
    }
}
