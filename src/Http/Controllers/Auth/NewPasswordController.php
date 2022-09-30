<?php

declare(strict_types=1);

namespace Canvas\Http\Controllers\Auth;

use Canvas\Http\Requests\NewPasswordRequest;
use Canvas\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        return view('canvas::auth.passwords.reset')
            ->with(['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @param  \Canvas\Http\Requests\NewPasswordRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(NewPasswordRequest $request)
    {
        $data = $request->validated();

        try {
            [$id, $token] = explode('|', decrypt($data['token']));

            $user = User::query()->findOrFail($id);

            // Here we will attempt to reset the user's password. If it is successful we
            // will update the password on an actual user model and persist it to the
            // database. Otherwise we will parse the error and return the response.
            $user->password = Hash::make($data['password']);

            $user->setRememberToken(Str::random(60));

            $user->save();

            Auth::guard('canvas')->login($user);
        } catch (Throwable $e) {
            return redirect()
                ->route('canvas.forgot-password.view')
                ->with('invalidResetToken', trans('canvas::app.this_password_reset_token_is_invalid', [], app()->getLocale()));
        }

        Cache::forget("password.reset.{$id}");

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return redirect()->route('canvas');
    }
}
