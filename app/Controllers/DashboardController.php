<?php
/**
 * Dashboard Controller
 */
class DashboardController {
    /**
     * Dashboard index
     */
    public function index() {
        Auth::requireAuth();

        // Get statistics
        $student_stats = Student::statistics();
        $invoice_stats = Invoice::statistics();
        $cashbook_balance = Cashbook::balance();

        // Get recent activities
        $recent_payments = Payment::all(1, 5);
        $recent_students = DB::query('
            SELECT * FROM students
            ORDER BY created_at DESC
            LIMIT 5
        ');

        // Get overdue invoices count
        $overdue_count = count(Invoice::overdue());

        // License status
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
