-- EduChad Database Schema
-- Charset & engine
SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- Drop existing tables if they exist (for clean migration)
DROP TABLE IF EXISTS cashbook;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS invoice_items;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS fees;
DROP TABLE IF EXISTS grades;
DROP TABLE IF EXISTS class_subjects;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS themes;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;

-- Users table with role-based access control
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('ADMIN','CASHIER','TEACHER','VIEWER') NOT NULL DEFAULT 'VIEWER',
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_username (username),
  INDEX idx_email (email),
  INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Application settings (key-value store)
CREATE TABLE settings (
  k VARCHAR(100) PRIMARY KEY,
  v TEXT NOT NULL,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO settings (k, v) VALUES
 ('school_name', 'EduChad - École Secondaire'),
 ('school_logo', ''),
 ('school_address', ''),
 ('school_phone', ''),
 ('school_email', ''),
 ('theme', 'default'),
 ('currency', 'FCFA'),
 ('academic_year', '2024-2025'),
 ('license_key', ''),
 ('license_until', ''),
 ('app_version', '1.0.0');

-- Classes (grades/levels)
CREATE TABLE classes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  level INT NOT NULL DEFAULT 1,
  capacity INT DEFAULT NULL,
  description TEXT,
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_level (level),
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Students
CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  matricule VARCHAR(50) UNIQUE NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  sex ENUM('M','F') NOT NULL,
  birthdate DATE,
  birthplace VARCHAR(120),
  class_id INT NOT NULL,
  parent_name VARCHAR(120),
  parent_phone VARCHAR(50),
  parent_email VARCHAR(120),
  address VARCHAR(190),
  photo VARCHAR(255),
  status ENUM('ACTIVE','LEFT','SUSPENDED','GRADUATED') DEFAULT 'ACTIVE',
  enrollment_date DATE,
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE RESTRICT,
  INDEX idx_matricule (matricule),
  INDEX idx_class (class_id),
  INDEX idx_status (status),
  INDEX idx_name (last_name, first_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subjects
CREATE TABLE subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  code VARCHAR(20),
  description TEXT,
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Class-Subject mapping with coefficients
CREATE TABLE class_subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  class_id INT NOT NULL,
  subject_id INT NOT NULL,
  coefficient DECIMAL(5,2) NOT NULL DEFAULT 1.0,
  teacher_id INT,
  academic_year VARCHAR(20),
  UNIQUE KEY uk_cs (class_id, subject_id),
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
  FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_class (class_id),
  INDEX idx_subject (subject_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Grades/Scores
CREATE TABLE grades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  subject_id INT NOT NULL,
  term ENUM('T1','T2','T3') NOT NULL,
  academic_year VARCHAR(20),
  assessment VARCHAR(50) DEFAULT 'Examen',
  score DECIMAL(6,2) NOT NULL,
  out_of DECIMAL(6,2) NOT NULL DEFAULT 20,
  weight DECIMAL(5,2) DEFAULT 1.0,
  entered_by INT,
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
  FOREIGN KEY (entered_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_student (student_id),
  INDEX idx_subject (subject_id),
  INDEX idx_term (term),
  INDEX idx_student_term (student_id, term)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fee types
CREATE TABLE fees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  description TEXT,
  amount DECIMAL(10,2) NOT NULL,
  is_recurring TINYINT(1) DEFAULT 1,
  frequency ENUM('ONE_TIME','MONTHLY','QUARTERLY','YEARLY') DEFAULT 'YEARLY',
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student invoices
CREATE TABLE invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_number VARCHAR(50) UNIQUE NOT NULL,
  student_id INT NOT NULL,
  issue_date DATE NOT NULL,
  due_date DATE NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  discount DECIMAL(10,2) DEFAULT 0,
  final_total DECIMAL(10,2) NOT NULL,
  status ENUM('DUE','PARTIAL','PAID','CANCELLED') NOT NULL DEFAULT 'DUE',
  notes TEXT,
  created_by INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_student (student_id),
  INDEX idx_status (status),
  INDEX idx_dates (issue_date, due_date),
  INDEX idx_invoice_number (invoice_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invoice line items
CREATE TABLE invoice_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT NOT NULL,
  fee_id INT,
  description VARCHAR(190) NOT NULL,
  qty DECIMAL(10,2) NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL,
  line_total DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
  FOREIGN KEY (fee_id) REFERENCES fees(id) ON DELETE SET NULL,
  INDEX idx_invoice (invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments
CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  payment_number VARCHAR(50) UNIQUE NOT NULL,
  invoice_id INT NOT NULL,
  paid_at DATETIME NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  method ENUM('CASH','MOBILE','BANK','CHEQUE','OTHER') DEFAULT 'CASH',
  reference VARCHAR(120),
  notes TEXT,
  received_by INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
  FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_invoice (invoice_id),
  INDEX idx_date (paid_at),
  INDEX idx_payment_number (payment_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cashbook (income and expenses)
CREATE TABLE cashbook (
  id INT AUTO_INCREMENT PRIMARY KEY,
  transaction_number VARCHAR(50) UNIQUE,
  kind ENUM('INCOME','EXPENSE') NOT NULL,
  category VARCHAR(120) NOT NULL,
  description VARCHAR(190),
  amount DECIMAL(10,2) NOT NULL,
  payment_method ENUM('CASH','MOBILE','BANK','CHEQUE','OTHER') DEFAULT 'CASH',
  reference VARCHAR(120),
  at DATETIME NOT NULL,
  user_id INT,
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_kind (kind),
  INDEX idx_date (at),
  INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Themes for customization
CREATE TABLE themes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) UNIQUE NOT NULL,
  display_name VARCHAR(100),
  css_vars TEXT NOT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default themes
INSERT INTO themes(name, display_name, css_vars, is_active) VALUES
('default', 'Défaut (Clair)', '{"--bg":"#ffffff","--bg-secondary":"#f3f4f6","--primary":"#14532d","--primary-hover":"#15803d","--text":"#0a0a0a","--text-secondary":"#4b5563","--border":"#d1d5db","--success":"#16a34a","--warning":"#f59e0b","--danger":"#dc2626"}', 1),
('dark', 'Sombre', '{"--bg":"#0b1220","--bg-secondary":"#1e293b","--primary":"#3b82f6","--primary-hover":"#2563eb","--text":"#e5e7eb","--text-secondary":"#9ca3af","--border":"#374151","--success":"#10b981","--warning":"#f59e0b","--danger":"#ef4444"}', 1),
('green', 'Vert Nature', '{"--bg":"#f0fdf4","--bg-secondary":"#dcfce7","--primary":"#16a34a","--primary-hover":"#15803d","--text":"#14532d","--text-secondary":"#166534","--border":"#bbf7d0","--success":"#16a34a","--warning":"#f59e0b","--danger":"#dc2626"}', 1),
('blue', 'Bleu Professionnel', '{"--bg":"#f0f9ff","--bg-secondary":"#e0f2fe","--primary":"#0284c7","--primary-hover":"#0369a1","--text":"#0c4a6e","--text-secondary":"#075985","--border":"#bae6fd","--success":"#16a34a","--warning":"#f59e0b","--danger":"#dc2626"}', 1);

-- Activity log (optional, for audit trail)
CREATE TABLE activity_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  action VARCHAR(100) NOT NULL,
  entity_type VARCHAR(50),
  entity_id INT,
  description TEXT,
  ip_address VARCHAR(45),
  user_agent VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_user (user_id),
  INDEX idx_entity (entity_type, entity_id),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
