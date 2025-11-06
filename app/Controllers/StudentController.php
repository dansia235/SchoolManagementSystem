<?php
/**
 * Student Controller
 */
class StudentController {
    /**
     * List all students
     */
    public function index() {
        Auth::requireRole(['ADMIN', 'TEACHER', 'CASHIER', 'VIEWER']);

        $page = $_GET['p'] ?? 1;
        $search = $_GET['search'] ?? '';
        $class_id = $_GET['class_id'] ?? '';

        if (!empty($search)) {
            $students = Student::search($search, ['class_id' => $class_id]);
            $total = count($students);
            $pagination = null;
        } else {
            $filters = [];
            if (!empty($class_id)) {
                $filters['class_id'] = $class_id;
            }

            $total = Student::count($filters);
            $pagination = paginate($total, $page);
            $students = Student::all($page);
        }

        $classes = ClassRoom::all();

        return View::render('students/index', [
            'title' => 'Gestion des élèves',
            'students' => $students,
            'classes' => $classes,
            'pagination' => $pagination,
            'search' => $search,
            'class_id' => $class_id
        ]);
    }

    /**
     * Show create form
     */
    public function create() {
        Auth::requireRole(['ADMIN', 'TEACHER']);

        $classes = ClassRoom::all();

        return View::render('students/create', [
            'title' => 'Nouveau élève',
            'classes' => $classes,
            'matricule' => generate_matricule()
        ]);
    }

    /**
     * Store new student
     */
    public function store() {
        Auth::requireRole(['ADMIN', 'TEACHER']);

        try {
            // Handle photo upload
            $photo = null;
            if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photo = upload_file($_FILES['photo'], 'uploads/students');
            }

            $data = [
                'matricule' => $_POST['matricule'],
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'sex' => $_POST['sex'],
                'birthdate' => $_POST['birthdate'] ?? null,
                'birthplace' => $_POST['birthplace'] ?? null,
                'class_id' => $_POST['class_id'],
                'parent_name' => $_POST['parent_name'] ?? null,
                'parent_phone' => $_POST['parent_phone'] ?? null,
                'parent_email' => $_POST['parent_email'] ?? null,
                'address' => $_POST['address'] ?? null,
                'photo' => $photo,
                'status' => 'ACTIVE',
                'enrollment_date' => $_POST['enrollment_date'] ?? date('Y-m-d'),
                'notes' => $_POST['notes'] ?? null
            ];

            $student_id = Student::create($data);

            flash('success', 'Élève ajouté avec succès.');
            redirect('students.show', ['id' => $student_id]);
        } catch (Exception $e) {
            flash('error', 'Erreur lors de l\'ajout de l\'élève: ' . $e->getMessage());
            redirect('students.create');
        }
    }

    /**
     * Show student details
     */
    public function show() {
        Auth::requireRole(['ADMIN', 'TEACHER', 'CASHIER', 'VIEWER']);

        $id = $_GET['id'] ?? 0;
        $student = Student::find($id);

        if (!$student) {
            flash('error', 'Élève introuvable.');
            redirect('students');
        }

        // Get student grades
        $grades_by_term = [];
        foreach (['T1', 'T2', 'T3'] as $term) {
            $grades_by_term[$term] = Grade::byStudentTerm($id, $term);
        }

        // Get student invoices
        $invoices = Invoice::byStudent($id);

        return View::render('students/show', [
            'title' => $student['first_name'] . ' ' . $student['last_name'],
            'student' => $student,
            'grades_by_term' => $grades_by_term,
            'invoices' => $invoices
        ]);
    }

    /**
     * Show edit form
     */
    public function edit() {
        Auth::requireRole(['ADMIN', 'TEACHER']);

        $id = $_GET['id'] ?? 0;
        $student = Student::find($id);

        if (!$student) {
            flash('error', 'Élève introuvable.');
            redirect('students');
        }

        $classes = ClassRoom::all();

        return View::render('students/edit', [
            'title' => 'Modifier l\'élève',
            'student' => $student,
            'classes' => $classes
        ]);
    }

    /**
     * Update student
     */
    public function update() {
        Auth::requireRole(['ADMIN', 'TEACHER']);

        $id = $_POST['id'] ?? 0;

        try {
            // Handle photo upload
            $photo = $_POST['current_photo'] ?? null;
            if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                // Delete old photo
                if ($photo) {
                    delete_file($photo);
                }
                $photo = upload_file($_FILES['photo'], 'uploads/students');
            }

            $data = [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'sex' => $_POST['sex'],
                'birthdate' => $_POST['birthdate'] ?? null,
                'birthplace' => $_POST['birthplace'] ?? null,
                'class_id' => $_POST['class_id'],
                'parent_name' => $_POST['parent_name'] ?? null,
                'parent_phone' => $_POST['parent_phone'] ?? null,
                'parent_email' => $_POST['parent_email'] ?? null,
                'address' => $_POST['address'] ?? null,
                'photo' => $photo,
                'status' => $_POST['status'] ?? 'ACTIVE',
                'notes' => $_POST['notes'] ?? null
            ];

            Student::update($id, $data);

            flash('success', 'Élève modifié avec succès.');
            redirect('students.show', ['id' => $id]);
        } catch (Exception $e) {
            flash('error', 'Erreur lors de la modification: ' . $e->getMessage());
            redirect('students.edit', ['id' => $id]);
        }
    }

    /**
     * Delete student
     */
    public function delete() {
        Auth::requireRole(['ADMIN']);

        $id = $_POST['id'] ?? 0;

        try {
            Student::delete($id);
            flash('success', 'Élève supprimé avec succès.');
        } catch (Exception $e) {
            flash('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }

        redirect('students');
    }
}
