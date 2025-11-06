<?php
/**
 * Authentication Controller
 */
class AuthController {
    /**
     * Show login form
     */
    public function login() {
        // If already authenticated, redirect to dashboard
        if (Auth::check()) {
            redirect('dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (Auth::attempt($email, $password)) {
                flash('success', 'Bienvenue !');
                redirect('dashboard');
            } else {
                flash('error', 'Email ou mot de passe incorrect.');
            }
        }

        return View::render('auth/login', [
            'title' => 'Connexion'
        ], null);
    }

    /**
     * Logout
     */
    public function logout() {
        Auth::logout();
        flash('success', 'Vous avez été déconnecté avec succès.');
        redirect('login');
    }
}
