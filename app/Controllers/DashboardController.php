<?php
/**
 * Dashboard Controller
 */
class DashboardController {
    /**
     * Dashboard index - Routes to role-specific dashboard
     */
    public function index() {
        Auth::requireAuth();

        $role = Auth::role();

        // Route to role-specific dashboard
        switch ($role) {
            case 'ADMIN':
                return $this->adminDashboard();
            case 'TEACHER':
                return $this->teacherDashboard();
            case 'CASHIER':
                return $this->cashierDashboard();
            case 'VIEWER':
                return $this->viewerDashboard();
            default:
                return $this->defaultDashboard();
        }
    }

    /**
     * Admin Dashboard - Vue complète avec toutes les statistiques
     */
    private function adminDashboard() {
        // Statistiques des élèves
        $student_stats = Student::statistics();

        // Statistiques financières
        $invoice_stats = Invoice::statistics();

        // Solde de la caisse
        $cashbook_balance = Cashbook::balance();

        // Paiements récents
        $recent_payments = Payment::all(1, 5);

        // Nouveaux élèves
        $recent_students = DB::query('
            SELECT * FROM students
            ORDER BY created_at DESC
            LIMIT 5
        ');

        // Factures impayées
        $overdue_count = count(Invoice::overdue());

        // Activités récentes
        $recent_activities = DB::query('
            SELECT * FROM activity_log
            ORDER BY created_at DESC
            LIMIT 10
        ');

        // Statut de la licence
        $license_status = License::status();

        return View::render('dashboard/admin', [
            'title' => 'Tableau de bord - Administrateur',
            'student_stats' => $student_stats,
            'invoice_stats' => $invoice_stats,
            'cashbook_balance' => $cashbook_balance,
            'recent_payments' => $recent_payments,
            'recent_students' => $recent_students,
            'overdue_count' => $overdue_count,
            'recent_activities' => $recent_activities,
            'license_status' => $license_status
        ]);
    }

    /**
     * Teacher Dashboard - Focus sur les élèves et notes
     */
    private function teacherDashboard() {
        $classes = ClassRoom::all();

        // Statistiques des élèves
        $total_students = DB::queryValue('SELECT COUNT(*) FROM students WHERE status = "ACTIVE"');

        // Classes assignées à l'enseignant
        $my_classes = DB::query('
            SELECT DISTINCT c.*, COUNT(s.id) as student_count
            FROM class_subjects cs
            JOIN classes c ON c.id = cs.class_id
            LEFT JOIN students s ON s.class_id = c.id AND s.status = "ACTIVE"
            WHERE cs.teacher_id = ?
            GROUP BY c.id
        ', [Auth::id()]);

        // Matières assignées
        $my_subjects = DB::query('
            SELECT DISTINCT sub.*, COUNT(DISTINCT cs.class_id) as class_count
            FROM class_subjects cs
            JOIN subjects sub ON sub.id = cs.subject_id
            WHERE cs.teacher_id = ?
            GROUP BY sub.id
        ', [Auth::id()]);

        // Notes récemment saisies
        $recent_grades = DB::query('
            SELECT g.*, s.first_name, s.last_name, sub.name as subject_name, c.name as class_name
            FROM grades g
            JOIN students s ON s.id = g.student_id
            JOIN subjects sub ON sub.id = g.subject_id
            JOIN classes c ON c.id = s.class_id
            WHERE g.entered_by = ?
            ORDER BY g.created_at DESC
            LIMIT 10
        ', [Auth::id()]);

        return View::render('dashboard/teacher', [
            'title' => 'Tableau de bord - Enseignant',
            'total_students' => $total_students,
            'my_classes' => $my_classes,
            'my_subjects' => $my_subjects,
            'recent_grades' => $recent_grades,
            'all_classes' => $classes
        ]);
    }

    /**
     * Cashier Dashboard - Focus sur la facturation et paiements
     */
    private function cashierDashboard() {
        // Statistiques financières
        $invoice_stats = Invoice::statistics();

        // Solde de la caisse
        $cashbook_balance = Cashbook::balance();

        // Paiements du jour
        $today_payments = Payment::byDateRange(date('Y-m-d'), date('Y-m-d'));
        $today_total = array_sum(array_column($today_payments, 'amount'));

        // Paiements récents
        $recent_payments = Payment::all(1, 10);

        // Factures en attente
        $pending_invoices = DB::query('
            SELECT i.*, s.first_name, s.last_name, s.matricule,
                   (i.final_total - COALESCE(SUM(p.amount), 0)) as balance
            FROM invoices i
            JOIN students s ON s.id = i.student_id
            LEFT JOIN payments p ON p.invoice_id = i.id
            WHERE i.status IN ("DUE", "PARTIAL")
            GROUP BY i.id
            ORDER BY i.due_date
            LIMIT 10
        ');

        // Impayés
        $overdue_invoices = Invoice::overdue();

        // Transactions de la caisse du jour
        $today_cashbook = Cashbook::all(1, null, [
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d')
        ]);

        return View::render('dashboard/cashier', [
            'title' => 'Tableau de bord - Caissier',
            'invoice_stats' => $invoice_stats,
            'cashbook_balance' => $cashbook_balance,
            'today_payments' => $today_payments,
            'today_total' => $today_total,
            'recent_payments' => $recent_payments,
            'pending_invoices' => $pending_invoices,
            'overdue_invoices' => $overdue_invoices,
            'today_cashbook' => $today_cashbook
        ]);
    }

    /**
     * Viewer Dashboard - Consultation uniquement
     */
    private function viewerDashboard() {
        // Statistiques générales
        $total_students = DB::queryValue('SELECT COUNT(*) FROM students WHERE status = "ACTIVE"');
        $total_classes = DB::queryValue('SELECT COUNT(*) FROM classes WHERE is_active = 1');

        // Statistiques par classe
        $class_stats = DB::query('
            SELECT c.name, c.level, COUNT(s.id) as student_count
            FROM classes c
            LEFT JOIN students s ON s.class_id = c.id AND s.status = "ACTIVE"
            WHERE c.is_active = 1
            GROUP BY c.id
            ORDER BY c.level, c.name
        ');

        // Statistiques de genre
        $gender_stats = DB::query('
            SELECT sex, COUNT(*) as count
            FROM students
            WHERE status = "ACTIVE"
            GROUP BY sex
        ');

        return View::render('dashboard/viewer', [
            'title' => 'Tableau de bord - Observateur',
            'total_students' => $total_students,
            'total_classes' => $total_classes,
            'class_stats' => $class_stats,
            'gender_stats' => $gender_stats
        ]);
    }

    /**
     * Default Dashboard - Fallback
     */
    private function defaultDashboard() {
        $student_stats = Student::statistics();
        $invoice_stats = Invoice::statistics();
        $cashbook_balance = Cashbook::balance();
        $recent_payments = Payment::all(1, 5);
        $recent_students = DB::query('SELECT * FROM students ORDER BY created_at DESC LIMIT 5');
        $overdue_count = count(Invoice::overdue());
        $license_status = License::status();

        return View::render('dashboard/index', [
            'title' => 'Tableau de bord',
            'student_stats' => $student_stats,
            'invoice_stats' => $invoice_stats,
            'cashbook_balance' => $cashbook_balance,
            'recent_payments' => $recent_payments,
            'recent_students' => $recent_students,
            'overdue_count' => $overdue_count,
            'license_status' => $license_status
        ]);
    }
}
