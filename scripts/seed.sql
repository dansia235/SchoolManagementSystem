-- EduChad Seed Data
-- This file populates the database with initial data for testing and demonstration

-- Insert default users
-- Password for all: admin123 (simple and easy to remember)
INSERT INTO users(username, name, email, password_hash, role, is_active) VALUES
('admin', 'Administrateur Principal', 'admin@educhad.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN', 1),
('enseignant', 'Enseignant Principal', 'teacher@educhad.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'TEACHER', 1),
('caissier', 'Caissier Principal', 'cashier@educhad.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CASHIER', 1),
('observateur', 'Observateur', 'viewer@educhad.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'VIEWER', 1);

-- Insert classes (from 6ème to Terminale)
INSERT INTO classes(name, level, capacity, is_active) VALUES
('6ème A', 1, 40, 1),
('6ème B', 1, 40, 1),
('5ème A', 2, 40, 1),
('5ème B', 2, 40, 1),
('4ème A', 3, 40, 1),
('4ème B', 3, 40, 1),
('3ème A', 4, 40, 1),
('3ème B', 4, 40, 1),
('2nde C', 5, 35, 1),
('2nde D', 5, 35, 1),
('1ère C', 6, 35, 1),
('1ère D', 6, 35, 1),
('Terminale C', 7, 35, 1),
('Terminale D', 7, 35, 1);

-- Insert subjects
INSERT INTO subjects(name, code, is_active) VALUES
('Français', 'FR', 1),
('Mathématiques', 'MATH', 1),
('Physique-Chimie', 'PC', 1),
('Sciences de la Vie et de la Terre', 'SVT', 1),
('Histoire-Géographie', 'HG', 1),
('Anglais', 'ANG', 1),
('Éducation Physique et Sportive', 'EPS', 1),
('Philosophie', 'PHILO', 1),
('Informatique', 'INFO', 1),
('Arts Plastiques', 'ART', 1),
('Éducation Civique', 'EC', 1),
('Espagnol', 'ESP', 1),
('Arabe', 'AR', 1);

-- Assign subjects to classes with coefficients

-- 6ème (Niveau 1)
INSERT INTO class_subjects(class_id, subject_id, coefficient)
SELECT c.id, s.id,
  CASE s.name
    WHEN 'Mathématiques' THEN 4
    WHEN 'Français' THEN 4
    WHEN 'Histoire-Géographie' THEN 2
    WHEN 'Sciences de la Vie et de la Terre' THEN 2
    WHEN 'Anglais' THEN 2
    WHEN 'Éducation Physique et Sportive' THEN 1
    WHEN 'Arts Plastiques' THEN 1
    WHEN 'Éducation Civique' THEN 1
    ELSE 1
  END
FROM classes c
CROSS JOIN subjects s
WHERE c.name LIKE '6ème%'
AND s.name IN ('Français', 'Mathématiques', 'Histoire-Géographie', 'Sciences de la Vie et de la Terre', 'Anglais', 'Éducation Physique et Sportive', 'Arts Plastiques', 'Éducation Civique');

-- 5ème (Niveau 2)
INSERT INTO class_subjects(class_id, subject_id, coefficient)
SELECT c.id, s.id,
  CASE s.name
    WHEN 'Mathématiques' THEN 4
    WHEN 'Français' THEN 4
    WHEN 'Histoire-Géographie' THEN 2
    WHEN 'Sciences de la Vie et de la Terre' THEN 2
    WHEN 'Anglais' THEN 2
    WHEN 'Éducation Physique et Sportive' THEN 1
    WHEN 'Arts Plastiques' THEN 1
    WHEN 'Éducation Civique' THEN 1
    ELSE 1
  END
FROM classes c
CROSS JOIN subjects s
WHERE c.name LIKE '5ème%'
AND s.name IN ('Français', 'Mathématiques', 'Histoire-Géographie', 'Sciences de la Vie et de la Terre', 'Anglais', 'Éducation Physique et Sportive', 'Arts Plastiques', 'Éducation Civique');

-- 4ème (Niveau 3)
INSERT INTO class_subjects(class_id, subject_id, coefficient)
SELECT c.id, s.id,
  CASE s.name
    WHEN 'Mathématiques' THEN 4
    WHEN 'Français' THEN 4
    WHEN 'Physique-Chimie' THEN 3
    WHEN 'Histoire-Géographie' THEN 2
    WHEN 'Sciences de la Vie et de la Terre' THEN 2
    WHEN 'Anglais' THEN 2
    WHEN 'Éducation Physique et Sportive' THEN 1
    WHEN 'Éducation Civique' THEN 1
    ELSE 1
  END
FROM classes c
CROSS JOIN subjects s
WHERE c.name LIKE '4ème%'
AND s.name IN ('Français', 'Mathématiques', 'Physique-Chimie', 'Histoire-Géographie', 'Sciences de la Vie et de la Terre', 'Anglais', 'Éducation Physique et Sportive', 'Éducation Civique');

-- 3ème (Niveau 4)
INSERT INTO class_subjects(class_id, subject_id, coefficient)
SELECT c.id, s.id,
  CASE s.name
    WHEN 'Mathématiques' THEN 4
    WHEN 'Français' THEN 4
    WHEN 'Physique-Chimie' THEN 3
    WHEN 'Histoire-Géographie' THEN 2
    WHEN 'Sciences de la Vie et de la Terre' THEN 2
    WHEN 'Anglais' THEN 2
    WHEN 'Éducation Physique et Sportive' THEN 1
    WHEN 'Éducation Civique' THEN 1
    ELSE 1
  END
FROM classes c
CROSS JOIN subjects s
WHERE c.name LIKE '3ème%'
AND s.name IN ('Français', 'Mathématiques', 'Physique-Chimie', 'Histoire-Géographie', 'Sciences de la Vie et de la Terre', 'Anglais', 'Éducation Physique et Sportive', 'Éducation Civique');

-- 2nde C (Niveau 5)
INSERT INTO class_subjects(class_id, subject_id, coefficient)
SELECT c.id, s.id,
  CASE s.name
    WHEN 'Mathématiques' THEN 5
    WHEN 'Physique-Chimie' THEN 4
    WHEN 'Français' THEN 4
    WHEN 'Sciences de la Vie et de la Terre' THEN 3
    WHEN 'Anglais' THEN 3
    WHEN 'Histoire-Géographie' THEN 2
    WHEN 'Informatique' THEN 2
    WHEN 'Éducation Physique et Sportive' THEN 1
    ELSE 1
  END
FROM classes c
CROSS JOIN subjects s
WHERE c.name = '2nde C'
AND s.name IN ('Français', 'Mathématiques', 'Physique-Chimie', 'Sciences de la Vie et de la Terre', 'Anglais', 'Histoire-Géographie', 'Informatique', 'Éducation Physique et Sportive');

-- 2nde D (Niveau 5)
INSERT INTO class_subjects(class_id, subject_id, coefficient)
SELECT c.id, s.id,
  CASE s.name
    WHEN 'Mathématiques' THEN 5
    WHEN 'Physique-Chimie' THEN 4
    WHEN 'Sciences de la Vie et de la Terre' THEN 4
    WHEN 'Français' THEN 4
    WHEN 'Anglais' THEN 3
    WHEN 'Histoire-Géographie' THEN 2
    WHEN 'Informatique' THEN 2
    WHEN 'Éducation Physique et Sportive' THEN 1
    ELSE 1
  END
FROM classes c
CROSS JOIN subjects s
WHERE c.name = '2nde D'
AND s.name IN ('Français', 'Mathématiques', 'Physique-Chimie', 'Sciences de la Vie et de la Terre', 'Anglais', 'Histoire-Géographie', 'Informatique', 'Éducation Physique et Sportive');

-- 1ère C (Niveau 6)
INSERT INTO class_subjects(class_id, subject_id, coefficient)
SELECT c.id, s.id,
  CASE s.name
    WHEN 'Mathématiques' THEN 6
    WHEN 'Physique-Chimie' THEN 5
    WHEN 'Français' THEN 4
    WHEN 'Philosophie' THEN 3
    WHEN 'Anglais' THEN 3
    WHEN 'Histoire-Géographie' THEN 2
    WHEN 'Informatique' THEN 2
    WHEN 'Éducation Physique et Sportive' THEN 1
    ELSE 1
  END
FROM classes c
CROSS JOIN subjects s
WHERE c.name = '1ère C'
AND s.name IN ('Français', 'Mathématiques', 'Physique-Chimie', 'Philosophie', 'Anglais', 'Histoire-Géographie', 'Informatique', 'Éducation Physique et Sportive');

-- 1ère D (Niveau 6)
INSERT INTO class_subjects(class_id, subject_id, coefficient)
SELECT c.id, s.id,
  CASE s.name
    WHEN 'Mathématiques' THEN 6
    WHEN 'Sciences de la Vie et de la Terre' THEN 5
    WHEN 'Physique-Chimie' THEN 4
    WHEN 'Français' THEN 4
    WHEN 'Philosophie' THEN 3
    WHEN 'Anglais' THEN 3
    WHEN 'Histoire-Géographie' THEN 2
    WHEN 'Éducation Physique et Sportive' THEN 1
    ELSE 1
  END
FROM classes c
CROSS JOIN subjects s
WHERE c.name = '1ère D'
AND s.name IN ('Français', 'Mathématiques', 'Physique-Chimie', 'Sciences de la Vie et de la Terre', 'Philosophie', 'Anglais', 'Histoire-Géographie', 'Éducation Physique et Sportive');

-- Terminale C (Niveau 7)
INSERT INTO class_subjects(class_id, subject_id, coefficient)
SELECT c.id, s.id,
  CASE s.name
    WHEN 'Mathématiques' THEN 7
    WHEN 'Physique-Chimie' THEN 6
    WHEN 'Philosophie' THEN 4
    WHEN 'Français' THEN 3
    WHEN 'Anglais' THEN 3
    WHEN 'Histoire-Géographie' THEN 2
    WHEN 'Informatique' THEN 2
    WHEN 'Éducation Physique et Sportive' THEN 1
    ELSE 1
  END
FROM classes c
CROSS JOIN subjects s
WHERE c.name = 'Terminale C'
AND s.name IN ('Français', 'Mathématiques', 'Physique-Chimie', 'Philosophie', 'Anglais', 'Histoire-Géographie', 'Informatique', 'Éducation Physique et Sportive');

-- Terminale D (Niveau 7)
INSERT INTO class_subjects(class_id, subject_id, coefficient)
SELECT c.id, s.id,
  CASE s.name
    WHEN 'Mathématiques' THEN 6
    WHEN 'Sciences de la Vie et de la Terre' THEN 6
    WHEN 'Physique-Chimie' THEN 5
    WHEN 'Philosophie' THEN 4
    WHEN 'Français' THEN 3
    WHEN 'Anglais' THEN 3
    WHEN 'Histoire-Géographie' THEN 2
    WHEN 'Éducation Physique et Sportive' THEN 1
    ELSE 1
  END
FROM classes c
CROSS JOIN subjects s
WHERE c.name = 'Terminale D'
AND s.name IN ('Français', 'Mathématiques', 'Physique-Chimie', 'Sciences de la Vie et de la Terre', 'Philosophie', 'Anglais', 'Histoire-Géographie', 'Éducation Physique et Sportive');

-- Insert sample students
INSERT INTO students(matricule, first_name, last_name, sex, birthdate, birthplace, class_id, parent_name, parent_phone, address, status, enrollment_date) VALUES
('2024001', 'Mohamed', 'Abdallah', 'M', '2009-03-15', 'N''Djamena', 1, 'Abdallah Ibrahim', '+235 66 12 34 56', 'Quartier Chagoua', 'ACTIVE', '2024-09-01'),
('2024002', 'Fatima', 'Hassan', 'F', '2009-07-22', 'N''Djamena', 1, 'Hassan Mahamat', '+235 66 23 45 67', 'Quartier Moursal', 'ACTIVE', '2024-09-01'),
('2024003', 'Jean', 'Ngarbaye', 'M', '2009-01-10', 'Moundou', 1, 'Ngarbaye Paul', '+235 66 34 56 78', 'Quartier Walia', 'ACTIVE', '2024-09-01'),
('2024004', 'Sarah', 'Mahamat', 'F', '2008-11-30', 'N''Djamena', 2, 'Mahamat Ali', '+235 66 45 67 89', 'Quartier Diguel', 'ACTIVE', '2024-09-01'),
('2024005', 'Ibrahim', 'Saleh', 'M', '2008-05-18', 'Abéché', 2, 'Saleh Omar', '+235 66 56 78 90', 'Quartier Gardolé', 'ACTIVE', '2024-09-01'),
('2024006', 'Amina', 'Idriss', 'F', '2007-09-25', 'N''Djamena', 3, 'Idriss Deby', '+235 66 67 89 01', 'Quartier Ambatta', 'ACTIVE', '2024-09-01'),
('2024007', 'David', 'Nadji', 'M', '2007-04-12', 'Sarh', 3, 'Nadji Emmanuel', '+235 66 78 90 12', 'Quartier Bololo', 'ACTIVE', '2024-09-01'),
('2024008', 'Aïcha', 'Oumar', 'F', '2006-12-08', 'N''Djamena', 4, 'Oumar Youssouf', '+235 66 89 01 23', 'Quartier Gassi', 'ACTIVE', '2024-09-01'),
('2024009', 'Pierre', 'Djimrangar', 'M', '2006-08-20', 'N''Djamena', 5, 'Djimrangar Joseph', '+235 66 90 12 34', 'Quartier Sabangali', 'ACTIVE', '2024-09-01'),
('2024010', 'Khadija', 'Adam', 'F', '2006-02-14', 'N''Djamena', 5, 'Adam Mahamat', '+235 66 01 23 45', 'Quartier Paris Congo', 'ACTIVE', '2024-09-01'),
('2024011', 'Ahmed', 'Brahim', 'M', '2005-10-05', 'N''Djamena', 6, 'Brahim Hassan', '+235 66 12 34 56', 'Quartier Ndjari', 'ACTIVE', '2024-09-01'),
('2024012', 'Rachida', 'Salah', 'F', '2005-06-28', 'N''Djamena', 6, 'Salah Ahmed', '+235 66 23 45 67', 'Quartier Ridina', 'ACTIVE', '2024-09-01');

-- Insert fee types
INSERT INTO fees(name, description, amount, frequency, is_active) VALUES
('Scolarité Annuelle', 'Frais de scolarité pour l''année académique complète', 250000, 'YEARLY', 1),
('Inscription', 'Frais d''inscription unique', 50000, 'ONE_TIME', 1),
('Réinscription', 'Frais de réinscription annuelle', 30000, 'YEARLY', 1),
('Fournitures Scolaires', 'Kit de fournitures scolaires', 35000, 'YEARLY', 1),
('Uniforme', 'Uniforme scolaire complet', 25000, 'YEARLY', 1),
('Cantine Mensuelle', 'Frais de cantine par mois', 15000, 'MONTHLY', 1),
('Transport Mensuel', 'Frais de transport par mois', 20000, 'MONTHLY', 1),
('Bibliothèque', 'Accès à la bibliothèque', 5000, 'YEARLY', 1),
('Assurance Scolaire', 'Assurance accidents scolaires', 10000, 'YEARLY', 1),
('Examen Blanc', 'Frais d''examen blanc', 8000, 'ONE_TIME', 1);

-- Insert sample invoices for students
INSERT INTO invoices(invoice_number, student_id, issue_date, due_date, total, discount, final_total, status, created_by) VALUES
('INV-2024-001', 1, '2024-09-01', '2024-09-30', 360000, 0, 360000, 'PARTIAL', 1),
('INV-2024-002', 2, '2024-09-01', '2024-09-30', 360000, 10000, 350000, 'PAID', 1),
('INV-2024-003', 3, '2024-09-01', '2024-09-30', 360000, 0, 360000, 'DUE', 1),
('INV-2024-004', 4, '2024-09-01', '2024-09-30', 360000, 0, 360000, 'PARTIAL', 1);

-- Insert invoice items for first invoice
INSERT INTO invoice_items(invoice_id, fee_id, description, qty, unit_price, line_total) VALUES
(1, 1, 'Scolarité Annuelle', 1, 250000, 250000),
(1, 2, 'Inscription', 1, 50000, 50000),
(1, 4, 'Fournitures Scolaires', 1, 35000, 35000),
(1, 5, 'Uniforme', 1, 25000, 25000);

-- Invoice 2 items
INSERT INTO invoice_items(invoice_id, fee_id, description, qty, unit_price, line_total) VALUES
(2, 1, 'Scolarité Annuelle', 1, 250000, 250000),
(2, 2, 'Inscription', 1, 50000, 50000),
(2, 4, 'Fournitures Scolaires', 1, 35000, 35000),
(2, 5, 'Uniforme', 1, 25000, 25000);

-- Invoice 3 items
INSERT INTO invoice_items(invoice_id, fee_id, description, qty, unit_price, line_total) VALUES
(3, 1, 'Scolarité Annuelle', 1, 250000, 250000),
(3, 2, 'Inscription', 1, 50000, 50000),
(3, 4, 'Fournitures Scolaires', 1, 35000, 35000),
(3, 5, 'Uniforme', 1, 25000, 25000);

-- Invoice 4 items
INSERT INTO invoice_items(invoice_id, fee_id, description, qty, unit_price, line_total) VALUES
(4, 1, 'Scolarité Annuelle', 1, 250000, 250000),
(4, 2, 'Inscription', 1, 50000, 50000),
(4, 4, 'Fournitures Scolaires', 1, 35000, 35000),
(4, 5, 'Uniforme', 1, 25000, 25000);

-- Insert sample payments
INSERT INTO payments(payment_number, invoice_id, paid_at, amount, method, reference, received_by) VALUES
('PAY-2024-001', 1, '2024-09-01 10:30:00', 200000, 'CASH', NULL, 2),
('PAY-2024-002', 2, '2024-09-01 11:15:00', 350000, 'MOBILE', 'TM-123456789', 2),
('PAY-2024-003', 4, '2024-09-02 09:00:00', 150000, 'CASH', NULL, 2);

-- Insert sample grades for student 1
INSERT INTO grades(student_id, subject_id, term, academic_year, assessment, score, out_of, entered_by)
SELECT 1, s.id, 'T1', '2024-2025', 'Devoir 1',
  CASE s.name
    WHEN 'Français' THEN 14.5
    WHEN 'Mathématiques' THEN 16.0
    WHEN 'Histoire-Géographie' THEN 13.0
    WHEN 'Sciences de la Vie et de la Terre' THEN 15.5
    WHEN 'Anglais' THEN 12.0
    WHEN 'Éducation Physique et Sportive' THEN 17.0
    ELSE 13.0
  END, 20, 3
FROM subjects s
WHERE s.name IN ('Français', 'Mathématiques', 'Histoire-Géographie', 'Sciences de la Vie et de la Terre', 'Anglais', 'Éducation Physique et Sportive');

-- Insert sample cashbook entries
INSERT INTO cashbook(transaction_number, kind, category, description, amount, payment_method, at, user_id) VALUES
('CASH-2024-001', 'INCOME', 'Inscription', 'Paiement inscription Mohamed Abdallah', 200000, 'CASH', '2024-09-01 10:30:00', 2),
('CASH-2024-002', 'INCOME', 'Inscription', 'Paiement complet Fatima Hassan', 350000, 'MOBILE', '2024-09-01 11:15:00', 2),
('CASH-2024-003', 'INCOME', 'Inscription', 'Paiement partiel Aïcha Oumar', 150000, 'CASH', '2024-09-02 09:00:00', 2),
('CASH-2024-004', 'EXPENSE', 'Fournitures', 'Achat de fournitures de bureau', 45000, 'CASH', '2024-09-03 14:00:00', 1),
('CASH-2024-005', 'EXPENSE', 'Salaires', 'Avance sur salaire enseignant', 100000, 'CASH', '2024-09-05 16:00:00', 1),
('CASH-2024-006', 'EXPENSE', 'Électricité', 'Facture ENELCAM septembre', 75000, 'BANK', '2024-09-10 10:00:00', 1);
