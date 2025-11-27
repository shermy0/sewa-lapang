<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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
    protected $redirectTo = '/home';

    /**
     * Redirect user after login based on role.
     */
    protected function redirectTo()
    {
        $user = auth()->user();

        if (!$user) {
            return '/';
        }

        switch ($user->role) {
            case 'admin':
                return route('dashboard.admin');
            case 'pemilik':
                return route('dashboard.pemilik');
            case 'penyewa':
                return route('penyewa.beranda');
            case 'petugas':
                return route('petugas.index');
            default:
                return '/';
        }
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}
