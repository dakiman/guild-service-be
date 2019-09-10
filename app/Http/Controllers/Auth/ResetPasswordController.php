<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Response;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function passwordReset(Request $request)
    {
        $this->reset($request);
    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        return Response::json([
            'email' => $request->get('email'),
            'errors' => 'The password reset link failed to send.'
        ], 400);
    }

    protected function sendResetResponse(Request $request, $response)
    {
        return Response::json([
            'status' => 'Successfully reset password.'
        ], 200);
    }

}
