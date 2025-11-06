<?php
/**
 * Report Controller
 */
class ReportController {
    /**
     * Report card (bulletin)
     */
    public function reportCard() {
        Auth::requireRole(['ADMIN', 'TEACHER', 'VIEWER']);

        $student_id = $_GET['student_id'] ?? 0;
        $term = $_GET['term'] ?? 'T1';

        if (!$student_id) {
            flash('error', 'Veuillez sélectionner un élève.');
            redirect('students');
        }

        $student = Student::find($student_id);
        if (!$student) {
            flash('error', 'Élève introuvable.');
            redirect('students');
        }

        [$subject_averages, $general_average] = Grade::calculateAverages($student_id, $term);

        // Get class ranking
        $class_averages = Grade::classAverages($student['class_id'], $term);
        $rank = null;
        foreach ($class_averages as $avg) {
            if ($avg['student_id'] == $student_id) {
                $rank = $avg['rank'];
                break;
            }
        }

        $school_name = setting('school_name');
        $school_logo = setting('school_logo');

        return View::render('reports/report_card', [
            'title' => 'Bulletin - ' . $student['first_name'] . ' ' . $student['last_name'],
            'student' => $student,
            'term' => $term,
            'subject_averages' => $subject_averages,
            'general_average' => $general_average,
            'rank' => $rank,
            'total_students' => count($class_averages),
            'school_name' => $school_name,
            'school_logo' => $school_logo
        ], null);
    }

    /**
     * Arrears report (impayés)
     */
    public function arrears() {
        Auth::requireRole(['ADMIN', 'CASHIER']);

        $sql = '
            SELECT s.id, s.first_name, s.last_name, s.matricule, c.name as class_name,
                   SUM(i.final_total) as total_billed,
                   COALESCE(SUM(p.amount), 0) as total_paid,
                   (SUM(i.final_total) - COALESCE(SUM(p.amount), 0)) as balance
            FROM students s
            LEFT JOIN classes c ON c.id = s.class_id
            LEFT JOIN invoices i ON i.student_id = s.id
            LEFT JOIN payments p ON p.invoice_id = i.id
            WHERE s.status = "ACTIVE"
            GROUP BY s.id
            HAVING balance > 0
            ORDER BY balance DESC
        ';

        $arrears = DB::query($sql);

        return View::render('reports/arrears', [
            'title' => 'État des impayés',
            'arrears' => $arrears
        ]);
    }

    /**
     * Student ledger (situation de paiement)
     */
    public function studentLedger() {
        Auth::requireRole(['ADMIN', 'CASHIER', 'VIEWER']);

        $student_id = $_GET['student_id'] ?? 0;

        if (!$student_id) {
            flash('error', 'Veuillez sélectionner un élève.');
            redirect('students');
        }

        $student = Student::find($student_id);
        if (!$student) {
            flash('error', 'Élève introuvable.');
            redirect('students');
        }

        $invoices = Invoice::byStudent($student_id);

        $total_billed = 0;
        $total_paid = 0;
        foreach ($invoices as $invoice) {
            $total_billed += $invoice['final_total'];
            $total_paid += $invoice['paid_amount'];
        }
        $balance = $total_billed - $total_paid;

        return View::render('reports/student_ledger', [
            'title' => 'Situation de paiement - ' . $student['first_name'] . ' ' . $student['last_name'],
            'student' => $student,
            'invoices' => $invoices,
            'total_billed' => $total_billed,
            'total_paid' => $total_paid,
            'balance' => $balance
        ]);
    }

    /**
     * Class list
     */
    public function classList() {
        Auth::requireRole(['ADMIN', 'TEACHER', 'VIEWER']);

        $class_id = $_GET['class_id'] ?? 0;

        if (!$class_id) {
            $classes = ClassRoom::all();
            return View::render('reports/class_list_select', [
                'title' => 'Liste de classe',
                'classes' => $classes
            ]);
        }

        $class = ClassRoom::find($class_id);
        if (!$class) {
            flash('error', 'Classe introuvable.');
            redirect('reports.class_list');
        }

        $students = Student::byClass($class_id);

        return View::render('reports/class_list', [
            'title' => 'Liste de classe - ' . $class['name'],
            'class' => $class,
            'students' => $students
        ], null);
    }
}
