<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function login(): View
    {
        return view('auth.login');
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function authenticate(LoginRequest $request): RedirectResponse
    {
        if (! Auth::attempt([...$request->only('email', 'password'), 'active' => true], $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'El correo o la contraseña no son correctos, o la cuenta no está activa.']);
        }

        $request->session()->regenerate();

        $request->session()->forget('url.intended');

        return $this->dashboard($request);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'active' => true,
                'is_demo' => false,
            ]);

            if ($user->role === 'candidate') {
                $user->candidateProfile()->create([]);
            } else {
                $user->company()->create([
                    'name' => $data['company_name'],
                    'slug' => Str::slug($data['company_name']).'-'.Str::lower(Str::random(8)),
                    'verified' => false,
                    'is_demo' => false,
                ]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return $this->dashboard($request)->with('success', 'Tu cuenta está lista. ¡Bienvenido a ChambaNet!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Cerraste tu sesión de forma segura.');
    }

    public function dashboard(Request $request): RedirectResponse
    {
        $route = match ($request->user()->role) {
            'admin' => 'admin.dashboard',
            'employer' => 'employer.dashboard',
            'candidate' => 'candidate.dashboard',
            default => abort(403),
        };

        return redirect()->route($route);
    }

    public function forgot(): View
    {
        return view('auth.forgot-password');
    }

    public function sendReset(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        Password::sendResetLink(['email' => mb_strtolower(trim((string) $request->input('email')))]);

        $message = 'Si el correo corresponde a una cuenta, recibirás las instrucciones para restablecer tu contraseña.';
        if (config('mail.default') === 'log') {
            $message .= ' Este entorno utiliza correo de desarrollo: el envío se registra en el servidor. Configura SMTP para recibir correos reales.';
        }

        return back()->with('status', $message);
    }

    public function reset(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email', '')]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function (User $user, string $password): void {
            $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });

        if ($status !== Password::PasswordReset) {
            return back()->withErrors(['email' => 'El enlace no es válido o ya venció. Solicita uno nuevo.'])->onlyInput('email');
        }

        return redirect()->route('login')->with('success', 'Contraseña actualizada. Ya puedes iniciar sesión.');
    }
}
