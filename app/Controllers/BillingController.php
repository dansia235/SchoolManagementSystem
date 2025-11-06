<?php
/**
 * Billing Controller
 */
class BillingController {
    /**
     * List invoices
     */
    public function index() {
        Auth::requireRole(['ADMIN', 'CASHIER']);

        $page = $_GET['p'] ?? 1;
        $invoices = Invoice::all($page);
        $total = Invoice::count();
        $pagination = paginate($total, $page);

        return View::render('billing/index', [
            'title' => 'Facturation',
            'invoices' => $invoices,
            'pagination' => $pagination
        ]);
    }

    /**
     * Show invoice
     */
    public function show() {
        Auth::requireRole(['ADMIN', 'CASHIER', 'VIEWER']);

        $id = $_GET['id'] ?? 0;
        $invoice = Invoice::find($id);

        if (!$invoice) {
            flash('error', 'Facture introuvable.');
            redirect('billing');
        }

        $items = Invoice::items($id);
        $payments = Invoice::payments($id);

        return View::render('billing/show', [
            'title' => 'Facture ' . $invoice['invoice_number'],
            'invoice' => $invoice,
            'items' => $items,
            'payments' => $payments
        ]);
    }

    /**
     * Create invoice form
     */
    public function create() {
        Auth::requireRole(['ADMIN', 'CASHIER']);

        $student_id = $_GET['student_id'] ?? 0;
        $student = $student_id ? Student::find($student_id) : null;
        $fees = Fee::all();

        return View::render('billing/create', [
            'title' => 'Nouvelle facture',
            'student' => $student,
            'fees' => $fees,
            'invoice_number' => generate_invoice_number()
        ]);
    }

    /**
     * Store invoice
     */
    public function store() {
        Auth::requireRole(['ADMIN', 'CASHIER']);

        try {
            $items = [];
            foreach ($_POST['items'] as $item_data) {
                if (!empty($item_data['description']) && !empty($item_data['unit_price'])) {
                    $items[] = [
                        'fee_id' => $item_data['fee_id'] ?? null,
                        'description' => $item_data['description'],
                        'qty' => $item_data['qty'] ?? 1,
                        'unit_price' => $item_data['unit_price']
                    ];
                }
            }

            if (empty($items)) {
                flash('error', 'Veuillez ajouter au moins un article.');
                redirect('billing.create');
            }

            $invoice_data = [
                'invoice_number' => $_POST['invoice_number'] ?? generate_invoice_number(),
                'student_id' => $_POST['student_id'],
                'issue_date' => $_POST['issue_date'],
                'due_date' => $_POST['due_date'],
                'discount' => $_POST['discount'] ?? 0,
                'notes' => $_POST['notes'] ?? null
            ];

            $invoice_id = Invoice::create($invoice_data, $items);

            flash('success', 'Facture créée avec succès.');
            redirect('billing.show', ['id' => $invoice_id]);
        } catch (Exception $e) {
            flash('error', 'Erreur: ' . $e->getMessage());
            redirect('billing.create');
        }
    }

    /**
     * Record payment
     */
    public function payment() {
        Auth::requireRole(['ADMIN', 'CASHIER']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $payment_data = [
                    'invoice_id' => $_POST['invoice_id'],
                    'amount' => $_POST['amount'],
                    'method' => $_POST['method'],
                    'reference' => $_POST['reference'] ?? null,
                    'notes' => $_POST['notes'] ?? null,
                    'paid_at' => $_POST['paid_at'] ?? date('Y-m-d H:i:s')
                ];

                Payment::create($payment_data);

                flash('success', 'Paiement enregistré avec succès.');
                redirect('billing.show', ['id' => $_POST['invoice_id']]);
            } catch (Exception $e) {
                flash('error', 'Erreur: ' . $e->getMessage());
                redirect('billing.show', ['id' => $_POST['invoice_id'] ?? 0]);
            }
        }
    }
}
