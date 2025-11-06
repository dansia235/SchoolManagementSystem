<?php
/**
 * Grade Controller
 */
class GradeController {
    /**
     * Grade management index
     */
    public function index() {
        Auth::requireRole(['ADMIN', 'TEACHER']);

        $classes = ClassRoom::all();
        $subjects = Subject::all();

        return View::render('grades/index', [
            'title' => 'Gestion des notes',
            'classes' => $classes,
            'subjects' => $subjects
        ]);
    }

    /**
     * Grade entry form
     */
    public function entry() {
        Auth::requireRole(['ADMIN', 'TEACHER']);

        $class_id = $_GET['class_id'] ?? 0;
        $subject_id = $_GET['subject_id'] ?? 0;
        $term = $_GET['term'] ?? 'T1';

        if (!$class_id || !$subject_id) {
            flash('error', 'Veuillez sélectionner une classe et une matière.');
            redirect('grades');
        }

        $class = ClassRoom::find($class_id);
        $subject = Subject::find($subject_id);
        $students = Student::byClass($class_id);

        // Get existing grades
        $existing_grades = Grade::byClassSubject($class_id, $subject_id, $term);
        $grades_by_student = [];
        foreach ($existing_grades as $grade) {
            $grades_by_student[$grade['student_id']][] = $grade;
        }

        return View::render('grades/entry', [
            'title' => 'Saisie des notes',
            'class' => $class,
            'subject' => $subject,
            'term' => $term,
            'students' => $students,
            'grades_by_student' => $grades_by_student
        ]);
    }

    /**
     * Store grades
     */
    public function store() {
        Auth::requireRole(['ADMIN', 'TEACHER']);

        try {
            $subject_id = $_POST['subject_id'];
            $term = $_POST['term'];
            $grades = $_POST['grades'] ?? [];

            foreach ($grades as $grade_data) {
                if (!empty($grade_data['score'])) {
                    Grade::store(
                        $grade_data['student_id'],
                        $subject_id,
                        $term,
                        $grade_data['assessment'] ?? 'Examen',
                        $grade_data['score'],
                        $grade_data['out_of'] ?? 20,
                        $grade_data['weight'] ?? 1.0
                    );
                }
            }

            flash('success', 'Notes enregistrées avec succès.');
            redirect('grades');
        } catch (Exception $e) {
            flash('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage());
            redirect('grades');
        }
    }

    /**
     * Subject coefficients management
     */
    public function coefficients() {
        Auth::requireRole(['ADMIN']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $class_id = $_POST['class_id'];
                $coefficients = $_POST['coefficients'] ?? [];

                foreach ($coefficients as $class_subject_id => $coef_data) {
                    ClassRoom::updateSubjectCoefficient(
                        $class_subject_id,
                        $coef_data['coefficient'],
                        $coef_data['teacher_id'] ?? null
                    );
                }

                flash('success', 'Coefficients mis à jour avec succès.');
                redirect('grades.coefficients', ['class_id' => $class_id]);
            } catch (Exception $e) {
                flash('error', 'Erreur: ' . $e->getMessage());
            }
        }

        $class_id = $_GET['class_id'] ?? 0;
        $classes = ClassRoom::all();
        $class_subjects = $class_id ? ClassRoom::subjects($class_id) : [];
        $teachers = User::teachers();

        return View::render('grades/coefficients', [
            'title' => 'Gestion des coefficients',
            'classes' => $classes,
            'class_id' => $class_id,
            'class_subjects' => $class_subjects,
            'teachers' => $teachers
        ]);
    }
}
