<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Events\NewUserWasRegistered;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator as ValidatorFacade;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected string $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function validator(array $data): Validator
    {
        $rules = [
            'name'                 => ['required', 'string', 'max:255'],
            'email'                => ['required', 'email', 'max:255', 'unique:users'],
            'password'             => ['required', 'confirmed', 'min:6'],
            'g-recaptcha-response' => ['required', 'captcha'],
            'allow_register'       => ['required', 'accepted'],
        ];

        if (app()->environment('local') || app()->environment('testing')) {
            unset($rules['g-recaptcha-response']);
        }

        $data['allow_register'] = config('root.app.allow_register', true);

        $messages = [
            'allow_register.accepted' => trans('app.allow_register'),
        ];

        return ValidatorFacade::make($data, $rules, $messages);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function create(array $data): User
    {
        $user = User::create([
            'username' => md5("{$data['name']}/{$data['email']}"),
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        event(new NewUserWasRegistered($user));

        return $user;
    }
}
