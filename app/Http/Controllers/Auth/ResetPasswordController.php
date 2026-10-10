<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
  public function create()
  {
    return view('auth.request-password');
  }

  public function email(Request $request)
  {
    $request->validate([
      'email' => 'required|email',
    ]);

    // We will send the password reset link to this user. Once we have attempted
    // to send the link, we will examine the response then see the message we
    // need to show to the user. Finally, we'll send out a proper response.
    $status = Password::sendResetLink(
      $request->only('email')
    );

    return $status == Password::RESET_LINK_SENT
      ? back()->with('success', __($status))
      : back()->withInput($request->only('email'))
      ->withErrors(['email' => __($status)]);
  }

  public function reset(Request $request)
  {
    return view('auth.reset-password', ['request' => $request]);
  }

  public function update(Request $request)
  {
    $request->validate([
      'token' => 'required',
      'email' => 'required|email',
      'password' => 'required|string|confirmed|min:8',
    ]);

    // Here we will attempt to reset the user's password. If it is successful we
    // will update the password on an actual user model and persist it to the
    // database. Otherwise we will parse the error and return the response.
    $status = Password::reset(
      $request->only('email', 'password', 'password_confirmation', 'token'),
      function ($user) use ($request) {
        $user->forceFill([
          'password' => Hash::make($request->password),
          'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));
      }
    );

    // If the password was successfully reset, we will redirect the user back to
    // the application's home authenticated view. If there is an error we can
    // redirect them back to where they came from with their error message.
    return $status == Password::PASSWORD_RESET
      ? redirect()->route('login')->with('success', __($status))
      : back()->withInput($request->only('email'))
      ->withErrors(['email' => __($status)]);
  }
}
