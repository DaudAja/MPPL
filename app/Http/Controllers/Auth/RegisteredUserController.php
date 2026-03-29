<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // dd($request->file('photo_profile'));
        // dd([
        //     'Data Teks' => $request->all(),
        //     'Data File (PHP Asli)' => $_FILES
        // ]);

        // 1. Validasi Input
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'photo_profile' => ['nullable', 'image', 'max:1024'], // Batas maksimal 1 MB
        ]);

        // 2. Proses Upload Foto (Jika ada)
        $photoPath = null;
        if ($request->hasFile('photo_profile')) {
            $photoPath = $request->file('photo_profile')->store('photos', 'public');
        }


        // 3. Simpan Data User ke Database
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'photo_profile' => $photoPath, // <-- Bagian penting yang sebelumnya terlewat
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
