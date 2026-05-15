<?php

namespace App\Http\Controllers;

use App\Services\MongoDBService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected MongoDBService $mongo;

    public function __construct(MongoDBService $mongo)
    {
        $this->mongo = $mongo;
    }

    /**
     * Show the admin login form.
     */
    public function showLogin()
    {
        if (session('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /**
     * Handle login form submission.
     * Validates against auth_admin collection in MongoDB.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $collection = $this->mongo->collection('auth_admin');

        $admin = $collection->findOne([
            'username' => $request->input('username'),
        ]);

        if (!$admin) {
            return back()->with('error', 'Username tidak ditemukan.')->withInput();
        }

        // Support both hashed and plain text passwords
        $storedPassword = (string) $admin['password'];
        $inputPassword = $request->input('password');

        $passwordValid = false;

        // Check if stored password is a bcrypt hash
        if (str_starts_with($storedPassword, '$2y$') || str_starts_with($storedPassword, '$2a$') || str_starts_with($storedPassword, '$2b$')) {
            $passwordValid = password_verify($inputPassword, $storedPassword);
        } else {
            // Plain text comparison (for simple setups)
            $passwordValid = ($storedPassword === $inputPassword);
        }

        if (!$passwordValid) {
            return back()->with('error', 'Password salah.')->withInput();
        }

        session([
            'admin_logged_in' => true,
            'admin_username' => (string) $admin['username'],
            'admin_name' => (string) ($admin['name'] ?? $admin['username']),
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Login berhasil!');
    }

    /**
     * Log out the admin and destroy the session.
     */
    public function logout()
    {
        session()->forget(['admin_logged_in', 'admin_username', 'admin_name']);

        return redirect()->route('admin.login')->with('success', 'Berhasil logout.');
    }
}
