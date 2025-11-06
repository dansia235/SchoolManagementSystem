<?php
/**
 * Cash Controller
 */
class CashController {
    /**
     * Cashbook index
     */
    public function index() {
        Auth::requireRole(['ADMIN', 'CASHIER']);

        $page = $_GET['p'] ?? 1;
        $kind = $_GET['kind'] ?? '';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';

        $filters = [];
        if ($kind) $filters['kind'] = $kind;
        if ($start_date && $end_date) {
            $filters['start_date'] = $start_date;
            $filters['end_date'] = $end_date;
        }

        $transactions = Cashbook::all($page, null, $filters);
        $total = Cashbook::count($filters);
        $pagination = paginate($total, $page);

        $balance = Cashbook::balance($start_date ?: null, $end_date ?: null);

        return View::render('cash/index', [
            'title' => 'Gestion de caisse',
            'transactions' => $transactions,
            'pagination' => $pagination,
            'balance' => $balance,
            'filters' => $filters
        ]);
    }

    /**
     * New income form
     */
    public function newIncome() {
        Auth::requireRole(['ADMIN', 'CASHIER']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                Cashbook::addIncome(
                    $_POST['category'],
                    $_POST['description'],
                    $_POST['amount'],
                    $_POST['method'] ?? 'CASH',
                    $_POST['reference'] ?? null
                );

                flash('success', 'Entrée enregistrée avec succès.');
                redirect('cash');
            } catch (Exception $e) {
                flash('error', 'Erreur: ' . $e->getMessage());
            }
        }

        return View::render('cash/new_income', [
            'title' => 'Nouvelle entrée'
        ]);
    }

    /**
     * New expense form
     */
    public function newExpense() {
        Auth::requireRole(['ADMIN', 'CASHIER']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                Cashbook::addExpense(
                    $_POST['category'],
                    $_POST['description'],
                    $_POST['amount'],
                    $_POST['method'] ?? 'CASH',
                    $_POST['reference'] ?? null
                );

                flash('success', 'Dépense enregistrée avec succès.');
                redirect('cash');
            } catch (Exception $e) {
                flash('error', 'Erreur: ' . $e->getMessage());
            }
        }

        return View::render('cash/new_expense', [
            'title' => 'Nouvelle dépense'
        ]);
    }

    /**
     * Export cashbook to CSV
     */
    public function export() {
        Auth::requireRole(['ADMIN', 'CASHIER']);

        $start_date = $_GET['start_date'] ?? null;
        $end_date = $_GET['end_date'] ?? null;

        try {
            $filename = Cashbook::exportCSV($start_date, $end_date);

            flash('success', 'Export réussi. Fichier: ' . $filename);
            redirect('cash');
        } catch (Exception $e) {
            flash('error', 'Erreur lors de l\'export: ' . $e->getMessage());
            redirect('cash');
        }
    }
}
