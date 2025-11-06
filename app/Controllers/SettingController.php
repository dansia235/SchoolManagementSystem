<?php
/**
 * Setting Controller
 */
class SettingController {
    /**
     * General settings
     */
    public function general() {
        Auth::requireRole(['ADMIN']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Handle logo upload
                if (!empty($_FILES['school_logo']) && $_FILES['school_logo']['error'] === UPLOAD_ERR_OK) {
                    $old_logo = setting('school_logo');
                    if ($old_logo) {
                        delete_file($old_logo);
                    }
                    $logo_path = upload_file($_FILES['school_logo'], 'uploads/logos');
                    update_setting('school_logo', $logo_path);
                }

                $settings = [
                    'school_name' => $_POST['school_name'],
                    'school_address' => $_POST['school_address'] ?? '',
                    'school_phone' => $_POST['school_phone'] ?? '',
                    'school_email' => $_POST['school_email'] ?? '',
                    'currency' => $_POST['currency'] ?? 'FCFA',
                    'academic_year' => $_POST['academic_year'] ?? academic_year(),
                    'theme' => $_POST['theme'] ?? 'default'
                ];

                Setting::updateMany($settings);

                flash('success', 'Paramètres mis à jour avec succès.');
                redirect('settings.general');
            } catch (Exception $e) {
                flash('error', 'Erreur: ' . $e->getMessage());
            }
        }

        $settings = Setting::all();
        $themes = Setting::getThemes();

        return View::render('settings/general', [
            'title' => 'Paramètres généraux',
            'settings' => $settings,
            'themes' => $themes
        ]);
    }

    /**
     * License management
     */
    public function license() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::requireRole(['ADMIN']);

            try {
                $license_key = trim($_POST['license_key']);
                $license_until = $_POST['license_until'];

                License::set($license_key, $license_until);

                if (License::valid()) {
                    flash('success', 'Licence activée avec succès !');
                } else {
                    flash('error', 'La licence est invalide. Veuillez vérifier la clé et la date.');
                }

                redirect('settings.license');
            } catch (Exception $e) {
                flash('error', 'Erreur: ' . $e->getMessage());
            }
        }

        $license_status = License::status();

        return View::render('settings/license', [
            'title' => 'Gestion de la licence',
            'license_status' => $license_status
        ], 'layout');
    }

    /**
     * User management
     */
    public function users() {
        Auth::requireRole(['ADMIN']);

        $users = User::all();

        return View::render('settings/users', [
            'title' => 'Gestion des utilisateurs',
            'users' => $users
        ]);
    }

    /**
     * Create user
     */
    public function createUser() {
        Auth::requireRole(['ADMIN']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'password' => $_POST['password'],
                    'role' => $_POST['role'],
                    'is_active' => 1
                ];

                $user_id = User::create($data);

                if ($user_id) {
                    flash('success', 'Utilisateur créé avec succès.');
                } else {
                    flash('error', 'Erreur lors de la création.');
                }

                redirect('settings.users');
            } catch (Exception $e) {
                flash('error', 'Erreur: ' . $e->getMessage());
            }
        }

        return View::render('settings/create_user', [
            'title' => 'Nouvel utilisateur'
        ]);
    }

    /**
     * Edit user
     */
    public function editUser() {
        Auth::requireRole(['ADMIN']);

        $id = $_GET['id'] ?? 0;
        $user = User::find($id);

        if (!$user) {
            flash('error', 'Utilisateur introuvable.');
            redirect('settings.users');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'role' => $_POST['role'],
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ];

                User::update($id, $data);

                // Update password if provided
                if (!empty($_POST['password'])) {
                    User::updatePassword($id, $_POST['password']);
                }

                flash('success', 'Utilisateur mis à jour avec succès.');
                redirect('settings.users');
            } catch (Exception $e) {
                flash('error', 'Erreur: ' . $e->getMessage());
            }
        }

        return View::render('settings/edit_user', [
            'title' => 'Modifier l\'utilisateur',
            'user' => $user
        ]);
    }

    /**
     * Delete user
     */
    public function deleteUser() {
        Auth::requireRole(['ADMIN']);

        $id = $_POST['id'] ?? 0;

        try {
            User::delete($id);
            flash('success', 'Utilisateur supprimé avec succès.');
        } catch (Exception $e) {
            flash('error', 'Erreur: ' . $e->getMessage());
        }

        redirect('settings.users');
    }

    /**
     * Profile (current user)
     */
    public function profile() {
        Auth::requireAuth();

        $user = Auth::user();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Update name and email
                $data = [
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'role' => $user['role'], // Keep current role
                    'is_active' => $user['is_active']
                ];

                User::update($user['id'], $data);

                // Update password if provided
                if (!empty($_POST['new_password'])) {
                    if (empty($_POST['current_password'])) {
                        flash('error', 'Veuillez saisir votre mot de passe actuel.');
                    } elseif (!password_verify($_POST['current_password'], $user['password_hash'])) {
                        flash('error', 'Mot de passe actuel incorrect.');
                    } else {
                        User::updatePassword($user['id'], $_POST['new_password']);
                        flash('success', 'Profil et mot de passe mis à jour avec succès.');
                        redirect('settings.profile');
                    }
                } else {
                    flash('success', 'Profil mis à jour avec succès.');
                    redirect('settings.profile');
                }
            } catch (Exception $e) {
                flash('error', 'Erreur: ' . $e->getMessage());
            }
        }

        return View::render('settings/profile', [
            'title' => 'Mon profil',
            'user' => Auth::user() // Reload user data
        ]);
    }
}
