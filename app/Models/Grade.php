<?php
/**
 * Grade Model
 */
class Grade {
    /**
     * Store a grade
     */
    public static function store($student_id, $subject_id, $term, $assessment, $score, $out_of, $weight = 1.0) {
        $sql = '
            INSERT INTO grades (student_id, subject_id, term, academic_year, assessment, score, out_of, weight, entered_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ';

        DB::execute($sql, [
            $student_id,
            $subject_id,
            $term,
            academic_year(),
            $assessment,
            $score,
            $out_of,
            $weight,
            Auth::id()
        ]);

        return DB::lastInsertId();
    }

    /**
     * Update a grade
     */
    public static function update($id, $data) {
        $sql = 'UPDATE grades SET assessment = ?, score = ?, out_of = ?, weight = ?, notes = ? WHERE id = ?';

        return DB::execute($sql, [
            $data['assessment'],
            $data['score'],
            $data['out_of'],
            $data['weight'] ?? 1.0,
            $data['notes'] ?? null,
            $id
        ]);
    }

    /**
     * Delete a grade
     */
    public static function delete($id) {
        return DB::execute('DELETE FROM grades WHERE id = ?', [$id]);
    }

    /**
     * Get grades by student and term
     */
    public static function byStudentTerm($student_id, $term, $academic_year = null) {
        if ($academic_year === null) {
            $academic_year = academic_year();
        }

        $sql = '
            SELECT g.*, s.name AS subject, s.code as subject_code, cs.coefficient
            FROM grades g
            JOIN subjects s ON s.id = g.subject_id
            JOIN students st ON st.id = g.student_id
            JOIN class_subjects cs ON cs.subject_id = g.subject_id AND cs.class_id = st.class_id
            WHERE g.student_id = ? AND g.term = ? AND g.academic_year = ?
            ORDER BY s.name, g.created_at
        ';

        return DB::query($sql, [$student_id, $term, $academic_year]);
    }

    /**
     * Get all grades for a student across all terms
     */
    public static function byStudent($student_id, $academic_year = null) {
        if ($academic_year === null) {
            $academic_year = academic_year();
        }

        $sql = '
            SELECT g.*, s.name AS subject, s.code as subject_code
            FROM grades g
            JOIN subjects s ON s.id = g.subject_id
            WHERE g.student_id = ? AND g.academic_year = ?
            ORDER BY g.term, s.name, g.created_at
        ';

        return DB::query($sql, [$student_id, $academic_year]);
    }

    /**
     * Calculate weighted averages for a student in a term
     *
     * Returns: [subject_averages, general_average]
     */
    public static function calculateAverages($student_id, $term, $academic_year = null) {
        if ($academic_year === null) {
            $academic_year = academic_year();
        }

        $rows = self::byStudentTerm($student_id, $term, $academic_year);

        if (empty($rows)) {
            return [[], null];
        }

        // Group grades by subject
        $perSubject = [];
        foreach ($rows as $r) {
            $subjectName = $r['subject'];
            $percentage = ($r['score'] / max(1, $r['out_of'])) * 20; // Normalize to /20
            $weight = (float)$r['weight'];

            if (!isset($perSubject[$subjectName])) {
                $perSubject[$subjectName] = [
                    'grades' => [],
                    'coefficient' => (float)$r['coefficient']
                ];
            }

            $perSubject[$subjectName]['grades'][] = [
                'percentage' => $percentage,
                'weight' => $weight
            ];
        }

        // Calculate average per subject
        $subjectAvgs = [];
        $totalCoef = 0;
        $totalWeighted = 0;

        foreach ($perSubject as $subjectName => $data) {
            // Calculate weighted average of all grades in this subject
            $sumWeighted = 0;
            $sumWeights = 0;

            foreach ($data['grades'] as $grade) {
                $sumWeighted += $grade['percentage'] * $grade['weight'];
                $sumWeights += $grade['weight'];
            }

            $subjectAvg = $sumWeights > 0 ? $sumWeighted / $sumWeights : 0;
            $coef = $data['coefficient'];

            $subjectAvgs[$subjectName] = [
                'average' => $subjectAvg,
                'coefficient' => $coef,
                'weighted' => $subjectAvg * $coef
            ];

            $totalCoef += $coef;
            $totalWeighted += $subjectAvg * $coef;
        }

        // Calculate general average
        $generalAvg = $totalCoef > 0 ? $totalWeighted / $totalCoef : null;

        return [$subjectAvgs, $generalAvg];
    }

    /**
     * Get class averages for a term
     */
    public static function classAverages($class_id, $term, $academic_year = null) {
        if ($academic_year === null) {
            $academic_year = academic_year();
        }

        $students = Student::byClass($class_id);
        $averages = [];

        foreach ($students as $student) {
            [, $avg] = self::calculateAverages($student['id'], $term, $academic_year);

            if ($avg !== null) {
                $averages[] = [
                    'student_id' => $student['id'],
                    'student_name' => $student['first_name'] . ' ' . $student['last_name'],
                    'average' => $avg
                ];
            }
        }

        // Sort by average (descending)
        usort($averages, function($a, $b) {
            return $b['average'] <=> $a['average'];
        });

        // Add rank
        foreach ($averages as $index => &$avg) {
            $avg['rank'] = $index + 1;
        }

        return $averages;
    }

    /**
     * Get grades for a class and subject
     */
    public static function byClassSubject($class_id, $subject_id, $term, $academic_year = null) {
        if ($academic_year === null) {
            $academic_year = academic_year();
        }

        $sql = '
            SELECT g.*, s.first_name, s.last_name, s.matricule
            FROM grades g
            JOIN students s ON s.id = g.student_id
            WHERE s.class_id = ? AND g.subject_id = ? AND g.term = ? AND g.academic_year = ?
            ORDER BY s.last_name, s.first_name, g.created_at
        ';

        return DB::query($sql, [$class_id, $subject_id, $term, $academic_year]);
    }
}
