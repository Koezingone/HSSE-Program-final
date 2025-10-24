<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller; // <-- BARIS INI PENTING
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller // <-- 'extends Controller' INI SANGAT PENTING
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
    protected $redirectTo = '/home'; // URL ini akan ditangkap oleh routes/web.php kita

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Baris inilah yang error.
        // Sekarang akan bekerja karena class ini sudah 'extends Controller'
        $this->middleware('guest')->except('logout');

        // Ini adalah baris lain dari error Anda sebelumnya
        $this->middleware('auth')->only('logout');
    }
}
