# EduChad – Système de gestion d’école secondaire (offline)

> **Stack**: PHP 8+, MySQL 5.7+, Tailwind CSS, PDO (no framework), HTML/JS vanille. 100% offline.

> **Fonctionnalités clés**: gestion des élèves, classes, matières & coefficients, saisie des notes, calcul des moyennes, bulletins/rapports imprimables, facturation & paiements, suivi des impayés, caisse (entrées/dépenses), personnalisation (thèmes, logo, nom d’école), gestion d’utilisateurs/permissions, génération/validation de licence annuelle offline.

---

## 1) Arborescence du projet

```
/educhad
├─ /public
│  ├─ index.php              # Front-controller (router)
│  ├─ assets/
│  │  ├─ tailwind.css        # CSS compilé (ou CDN si vous préférez compiler offline)
│  │  ├─ app.js
│  │  └─ logos/
│  └─ uploads/               # Logos et exportations (protégé via .htaccess si Apache)
├─ /app
│  ├─ bootstrap.php          # Chargement config, session, sécurité, autoload
│  ├─ config.php             # Variables globales (chemins, versions)
│  ├─ .env.php               # Config sensitive (DB, SECRET) – à créer
│  ├─ helpers.php            # Fonctions utilitaires (csrf, flash, fmt)
│  ├─ /Core
│  │  ├─ DB.php              # Singleton PDO
│  │  ├─ Auth.php            # Authentification & RBAC
│  │  ├─ License.php         # Génération/validation de licence offline (HMAC)
│  │  └─ View.php            # Rendu de vues (layout + contenu)
│  ├─ /Models                # Modèles (PDO prepared statements)
│  │  ├─ Student.php
│  │  ├─ ClassRoom.php
│  │  ├─ Subject.php
│  │  ├─ Grade.php
│  │  ├─ Fee.php
│  │  ├─ Invoice.php
│  │  ├─ Payment.php
│  │  ├─ Cashbook.php
│  │  ├─ Setting.php
│  │  └─ Theme.php
│  ├─ /Controllers
│  │  ├─ DashboardController.php
│  │  ├─ StudentController.php
│  │  ├─ GradeController.php
│  │  ├─ ReportController.php  # Bulletins & états
│  │  ├─ BillingController.php # Factures, paiements
│  │  ├─ CashController.php    # Caisse, dépenses/entrées
│  │  └─ SettingController.php # Thèmes, logo, licence
│  └─ /Views
│     ├─ layout.php
│     ├─ partials/{topnav,sidebar,alerts}.php
│     ├─ dashboard/index.php
│     ├─ students/{index,create,show,edit}.php
│     ├─ grades/{index,entry,subject_coeffs}.php
│     ├─ reports/{report_card,arrears,invoices,student_ledger}.php
│     ├─ billing/{fees,invoice_show,invoice_list}.php
│     ├─ cash/{index,new_income,new_expense}.php
│     └─ settings/{general,branding,theme,license,users}.php
├─ /storage
│  ├─ logs/app.log
│  └─ exports/               # HTML/PDF/CSV générés
├─ /scripts
│  ├─ migrate.sql            # Schéma complet
│  ├─ seed.sql               # Données de départ (classes, matières, utilisateur admin)
│  ├─ generate_license.php   # Générateur de clés (utilisé par l’éditeur)
│  └─ tailwind.config.cjs    # Si vous compilez localement
└─ composer.json (optionnel si vous ajoutez dompdf)
```

---

## 2) Schéma MySQL (migrate.sql)

```sql
-- Charset & engine
SET NAMES utf8mb4; SET time_zone = '+00:00';

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('ADMIN','CASHIER','TEACHER','VIEWER') NOT NULL DEFAULT 'VIEWER',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE settings (
  k VARCHAR(100) PRIMARY KEY,
  v TEXT NOT NULL
) ENGINE=InnoDB;

INSERT INTO settings (k, v) VALUES
 ('school_name', 'Mon Lycée Privé'),
 ('school_logo', ''),
 ('theme', 'default'),
 ('license_key', ''),
 ('license_until', '');

CREATE TABLE classes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,      -- ex: 6ème, 1ère C, Terminale D
  level INT NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  matricule VARCHAR(50) UNIQUE,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  sex ENUM('M','F') NOT NULL,
  birthdate DATE,
  class_id INT NOT NULL,
  parent_name VARCHAR(120),
  parent_phone VARCHAR(50),
  address VARCHAR(190),
  status ENUM('ACTIVE','LEFT','SUSPENDED') DEFAULT 'ACTIVE',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (class_id) REFERENCES classes(id)
) ENGINE=InnoDB;

CREATE TABLE subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE class_subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  class_id INT NOT NULL,
  subject_id INT NOT NULL,
  coefficient DECIMAL(5,2) NOT NULL DEFAULT 1.0,
  UNIQUE KEY uk_cs (class_id, subject_id),
  FOREIGN KEY (class_id) REFERENCES classes(id),
  FOREIGN KEY (subject_id) REFERENCES subjects(id)
) ENGINE=InnoDB;

CREATE TABLE grades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  subject_id INT NOT NULL,
  term ENUM('T1','T2','T3') NOT NULL,
  assessment VARCHAR(50) DEFAULT 'Examen',  -- DS, Devoir, Examen
  score DECIMAL(6,2) NOT NULL,
  out_of DECIMAL(6,2) NOT NULL DEFAULT 20,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id),
  FOREIGN KEY (subject_id) REFERENCES subjects(id)
) ENGINE=InnoDB;

-- Frais et facturation
CREATE TABLE fees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,            -- ex: Scolarité annuelle
  amount DECIMAL(10,2) NOT NULL,
  is_recurring TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  issue_date DATE NOT NULL,
  due_date DATE NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  status ENUM('DUE','PARTIAL','PAID') NOT NULL DEFAULT 'DUE',
  FOREIGN KEY (student_id) REFERENCES students(id)
) ENGINE=InnoDB;

CREATE TABLE invoice_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT NOT NULL,
  fee_id INT,
  description VARCHAR(190) NOT NULL,
  qty DECIMAL(10,2) NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id),
  FOREIGN KEY (fee_id) REFERENCES fees(id)
) ENGINE=InnoDB;

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT NOT NULL,
  paid_at DATETIME NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  method ENUM('CASH','MOBILE','BANK') DEFAULT 'CASH',
  ref VARCHAR(120),
  FOREIGN KEY (invoice_id) REFERENCES invoices(id)
) ENGINE=InnoDB;

-- Caisse
CREATE TABLE cashbook (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kind ENUM('INCOME','EXPENSE') NOT NULL,
  source VARCHAR(120) NOT NULL,    -- Inscription, Vente, etc.
  description VARCHAR(190),
  amount DECIMAL(10,2) NOT NULL,
  at DATETIME NOT NULL,
  user_id INT,
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Thèmes simples
CREATE TABLE themes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) UNIQUE,
  css_vars TEXT NOT NULL              -- JSON: {"--bg":"#fff", "--primary":"#14532d", ...}
) ENGINE=InnoDB;
```

---

## 3) Configuration `.env.php` (exemple)

```php
<?php
return [
  'DB_HOST' => '127.0.0.1',
  'DB_NAME' => 'educhad',
  'DB_USER' => 'root',
  'DB_PASS' => '',
  'APP_SECRET' => 'change_me_to_a_long_random_secret',
  'APP_URL' => 'http://localhost:8000',
  'TIMEZONE' => 'Africa/Ndjamena'
];
```

---

## 4) Bootstrap & sécurité (extraits)

**/app/bootstrap.php**
```php
<?php
$env = require __DIR__.'/.env.php';
require __DIR__.'/helpers.php';
require __DIR__.'/Core/DB.php';
require __DIR__.'/Core/Auth.php';
require __DIR__.'/Core/License.php';
require __DIR__.'/Core/View.php';

date_default_timezone_set($env['TIMEZONE'] ?? 'UTC');
session_start();

// CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf'] ?? '')) {
    http_response_code(419); exit('CSRF token invalid');
  }
}
```

**/app/helpers.php**
```php
<?php
function csrf_token() {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}
function csrf_check($t) { return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t); }
function flash($k,$v=null){ if($v===null){$x=$_SESSION['flash'][$k]??null; unset($_SESSION['flash'][$k]); return $x;} $_SESSION['flash'][$k]=$v; }
function fmt_money($x){ return number_format((float)$x,2,'.',' '); }
```

**/app/Core/DB.php**
```php
<?php
class DB {
  private static $pdo;
  public static function pdo(){
    if(!self::$pdo){
      $env = require __DIR__.'/../.env.php';
      $dsn = 'mysql:host='.$env['DB_HOST'].';dbname='.$env['DB_NAME'].';charset=utf8mb4';
      self::$pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS'], [
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
      ]);
    }
    return self::$pdo;
  }
}
```

**/app/Core/Auth.php** (extrait)
```php
<?php
class Auth {
  static function attempt($email,$pass){
    $st=DB::pdo()->prepare('SELECT * FROM users WHERE email=?'); $st->execute([$email]);
    $u=$st->fetch();
    if($u && password_verify($pass,$u['password_hash'])){ $_SESSION['uid']=$u['id']; return true; }
    return false;
  }
  static function user(){ if(empty($_SESSION['uid'])) return null; $st=DB::pdo()->prepare('SELECT * FROM users WHERE id=?'); $st->execute([$_SESSION['uid']]); return $st->fetch(); }
  static function requireRole($roles){ $u=self::user(); if(!$u || !in_array($u['role'],(array)$roles)) { http_response_code(403); exit('Forbidden'); } }
}
```

**/app/Core/License.php** – *licence annuelle offline*
```php
<?php
class License {
  static function school(){
    $st=DB::pdo()->query("SELECT v FROM settings WHERE k='school_name'");
    return $st->fetchColumn() ?: '';
  }
  static function get(){
    $st=DB::pdo()->prepare("SELECT v FROM settings WHERE k IN ('license_key','license_until') ORDER BY k");
    $st->execute(); $rows=$st->fetchAll(PDO::FETCH_KEY_PAIR); return $rows;
  }
  static function set($key,$until){
    DB::pdo()->prepare('REPLACE INTO settings(k,v) VALUES ("license_key",?), ("license_until",?)')->execute([$key,$until]);
  }
  // Validation HMAC: key = base64url(year + '.' + hmacSHA256(school|year, APP_SECRET))
  static function valid(){
    $env = require __DIR__.'/../.env.php';
    $rows=self::get(); $key=$rows['license_key']??''; $until=$rows['license_until']??'';
    if(!$key||!$until) return false;
    if(new DateTime('now') > new DateTime($until)) return false;
    $expected = self::make(self::school(), (new DateTime($until))->format('Y'), $env['APP_SECRET']);
    return hash_equals($expected,$key);
  }
  static function make($school,$year,$secret){
    $msg = $school.'|'.$year; $mac = hash_hmac('sha256',$msg,$secret,true);
    return rtrim(strtr(base64_encode($year.'.'.bin2hex($mac)),'+/','-_'),'=');
  }
}
```

**/scripts/generate_license.php** (utilisé par l’éditeur pour fournir une clé)
```php
<?php
// CLI: php scripts/generate_license.php "Nom Ecole" 2026 "SECRET"
[$_, $school, $year, $secret] = $argv + [null,null,null,null];
if(!$school||!$year||!$secret){ echo "Usage: php scripts/generate_license.php \"Nom\" 2026 SECRET\n"; exit(1);}
$msg = $school.'|'.$year; $mac = hash_hmac('sha256',$msg,$secret,true);
$key = rtrim(strtr(base64_encode($year.'.'.bin2hex($mac)),'+/','-_'),'=');
print $key."\n";
```

---

## 5) Router minimal **/public/index.php**

```php
<?php
require __DIR__.'/../app/bootstrap.php';

$path = $_GET['page'] ?? 'dashboard';
$map = [
  'login' => ['AuthController','login'],
  'logout'=> ['AuthController','logout'],
  'dashboard'=> ['DashboardController','index'],
  'students'=> ['StudentController','index'],
  'students.create'=> ['StudentController','create'],
  'students.store'=> ['StudentController','store'],
  'students.show'=> ['StudentController','show'],
  'grades'=> ['GradeController','index'],
  'grades.entry'=> ['GradeController','entry'],
  'grades.store'=> ['GradeController','store'],
  'reports.card'=> ['ReportController','reportCard'],
  'reports.arrears'=> ['ReportController','arrears'],
  'billing.invoices'=> ['BillingController','index'],
  'billing.invoice.show'=> ['BillingController','show'],
  'cash'=> ['CashController','index'],
  'cash.new_income'=> ['CashController','newIncome'],
  'cash.new_expense'=> ['CashController','newExpense'],
  'settings.general'=> ['SettingController','general'],
  'settings.license'=> ['SettingController','license'],
];

if(!License::valid() && $path!=='settings.license' && $path!=='login'){
  $path='settings.license';
}

if (!isset($map[$path])) { http_response_code(404); exit('404'); }
[$ctrl,$meth] = $map[$path];
require __DIR__.'/../app/Controllers/'.$ctrl.'.php';
$instance = new $ctrl; echo $instance->$meth();
```

---

## 6) Modèles & calculs de notes (extraits)

**/app/Models/Grade.php**
```php
<?php
class Grade {
  static function store($student_id,$subject_id,$term,$assessment,$score,$out_of){
    $st=DB::pdo()->prepare('INSERT INTO grades(student_id,subject_id,term,assessment,score,out_of) VALUES(?,?,?,?,?,?)');
    $st->execute([$student_id,$subject_id,$term,$assessment,$score,$out_of]);
  }
  static function byStudentTerm($student_id,$term){
    $st=DB::pdo()->prepare('SELECT g.*, s.name AS subject, cs.coefficient FROM grades g JOIN subjects s ON s.id=g.subject_id JOIN class_subjects cs ON cs.subject_id=g.subject_id JOIN students st ON st.class_id=cs.class_id WHERE g.student_id=? AND g.term=?');
    $st->execute([$student_id,$term]); return $st->fetchAll();
  }
  static function weightedAverages($student_id,$term){
    $rows=self::byStudentTerm($student_id,$term);
    $perSubject=[]; $sumCoef=0; $sumWeighted=0;
    foreach($rows as $r){
      $percent = ($r['score'] / max(1,$r['out_of']))*20; // normalisé sur 20
      $perSubject[$r['subject']][]=$percent;
      $coef = (float)$r['coefficient'];
    }
    $subjectAvgs=[]; $totalCoef=0; $totalWeighted=0;
    foreach($perSubject as $sub=>$arr){
      $avg = array_sum($arr)/count($arr); // moyenne des évaluations
      // récupérer coefficient
      $st=DB::pdo()->prepare('SELECT cs.coefficient FROM class_subjects cs JOIN students st ON st.class_id=cs.class_id JOIN subjects s ON s.id=cs.subject_id WHERE st.id=? AND s.name=?');
      $st->execute([$student_id,$sub]); $coef=(float)$st->fetchColumn();
      $subjectAvgs[$sub]=['avg'=>$avg,'coef'=>$coef];
      $totalCoef += $coef; $totalWeighted += $avg*$coef;
    }
    $general = $totalCoef>0 ? $totalWeighted/$totalCoef : null;
    return [$subjectAvgs,$general];
  }
}
```

---

## 7) Saisie des notes & coefficients (vues/controller simplifiés)

**/app/Controllers/GradeController.php** (extrait)
```php
<?php
class GradeController {
  public function entry(){ Auth::requireRole(['ADMIN','TEACHER']);
    $class_id = $_GET['class_id'] ?? null; $subject_id=$_GET['subject_id'] ?? null; $term=$_GET['term'] ?? 'T1';
    // charger élèves de la classe, etc.
    ob_start(); include __DIR__.'/../Views/grades/entry.php'; return ob_get_clean();
  }
  public function store(){ Auth::requireRole(['ADMIN','TEACHER']);
    foreach($_POST['grades'] as $row){
      Grade::store($row['student_id'], $_POST['subject_id'], $_POST['term'], $row['assessment'], $row['score'], $row['out_of']);
    }
    flash('ok','Notes enregistrées'); header('Location: index.php?page=grades'); exit;
  }
}
```

**/app/Views/grades/entry.php** (extrait)
```php
<form method="post" class="space-y-4">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
  <input type="hidden" name="term" value="<?= htmlspecialchars($term) ?>">
  <input type="hidden" name="subject_id" value="<?= (int)$subject_id ?>">
  <table class="min-w-full border">
    <thead><tr><th>Élève</th><th>Type</th><th>Note</th><th>Sur</th></tr></thead>
    <tbody>
      <?php foreach($students as $s): ?>
      <tr>
        <td><?= htmlspecialchars($s['last_name'].' '.$s['first_name']) ?></td>
        <td>
          <select name="grades[][assessment]" class="border p-1">
            <option>Devoir</option><option>DS</option><option>Examen</option>
          </select>
        </td>
        <td><input type="number" step="0.01" name="grades[][score]" class="border p-1 w-24" required></td>
        <td><input type="number" step="0.01" name="grades[][out_of]" class="border p-1 w-24" value="20"></td>
        <input type="hidden" name="grades[][student_id]" value="<?= (int)$s['id'] ?>">
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <button class="px-4 py-2 bg-green-700 text-white rounded">Enregistrer</button>
</form>
```

---

## 8) Bulletins, impayés, états

**/app/Controllers/ReportController.php** (extraits)
```php
<?php
class ReportController {
  public function reportCard(){ Auth::requireRole(['ADMIN','TEACHER']);
    $student_id=(int)$_GET['student_id']; $term=$_GET['term']??'T1';
    [$subs,$general] = Grade::weightedAverages($student_id,$term);
    ob_start(); include __DIR__.'/../Views/reports/report_card.php'; return ob_get_clean();
  }
  public function arrears(){ Auth::requireRole(['ADMIN','CASHIER']);
    $st=DB::pdo()->query("SELECT s.id, s.first_name, s.last_name, SUM(i.total) - COALESCE(SUM(p.amount),0) AS balance
      FROM students s
      LEFT JOIN invoices i ON i.student_id=s.id
      LEFT JOIN payments p ON p.invoice_id=i.id
      GROUP BY s.id HAVING balance>0 ORDER BY balance DESC");
    $rows=$st->fetchAll();
    ob_start(); include __DIR__.'/../Views/reports/arrears.php'; return ob_get_clean();
  }
  public function studentLedger(){ Auth::requireRole(['ADMIN','CASHIER']);
    $student_id=(int)$_GET['student_id'];
    $inv=DB::pdo()->prepare('SELECT * FROM invoices WHERE student_id=? ORDER BY issue_date'); $inv->execute([$student_id]);
    $invoices=$inv->fetchAll();
    ob_start(); include __DIR__.'/../Views/reports/student_ledger.php'; return ob_get_clean();
  }
}
```

**/app/Views/reports/report_card.php** (extrait imprimable)
```php
<div class="p-6 print:p-0">
  <div class="flex items-center gap-4 mb-4">
    <img src="<?= $logo_url ?>" class="h-12"/>
    <div>
      <div class="text-xl font-bold"><?= htmlspecialchars($school_name) ?></div>
      <div class="text-sm opacity-70">Bulletin – Trimestre <?= htmlspecialchars($term) ?></div>
    </div>
  </div>
  <table class="w-full border">
    <thead><tr><th>Matière</th><th>Coef</th><th>Moyenne/20</th></tr></thead>
    <tbody>
      <?php foreach($subs as $sub=>$d): ?>
      <tr><td><?= htmlspecialchars($sub) ?></td><td><?= $d['coef'] ?></td><td><?= number_format($d['avg'],2) ?></td></tr>
      <?php endforeach; ?>
      <tr class="font-bold"><td>Total</td><td></td><td><?= number_format($general,2) ?></td></tr>
    </tbody>
  </table>
</div>
```

---

## 9) Facturation & caisse

**/app/Controllers/BillingController.php** (extraits)
```php
<?php
class BillingController {
  public function index(){ Auth::requireRole(['ADMIN','CASHIER']);
    $st=DB::pdo()->query('SELECT i.*, s.first_name, s.last_name FROM invoices i JOIN students s ON s.id=i.student_id ORDER BY i.id DESC');
    $invoices=$st->fetchAll(); ob_start(); include __DIR__.'/../Views/billing/invoice_list.php'; return ob_get_clean();
  }
  public function show(){ Auth::requireRole(['ADMIN','CASHIER']);
    $id=(int)$_GET['id'];
    $i=DB::pdo()->prepare('SELECT * FROM invoices WHERE id=?'); $i->execute([$id]); $invoice=$i->fetch();
    $items=DB::pdo()->prepare('SELECT * FROM invoice_items WHERE invoice_id=?'); $items->execute([$id]); $lines=$items->fetchAll();
    $p=DB::pdo()->prepare('SELECT * FROM payments WHERE invoice_id=?'); $p->execute([$id]); $pays=$p->fetchAll();
    ob_start(); include __DIR__.'/../Views/billing/invoice_show.php'; return ob_get_clean();
  }
}
```

**/app/Controllers/CashController.php** (extraits)
```php
<?php
class CashController {
  public function index(){ Auth::requireRole(['ADMIN','CASHIER']);
    $st=DB::pdo()->query("SELECT kind, SUM(amount) as total FROM cashbook GROUP BY kind");
    $sum=$st->fetchAll(PDO::FETCH_KEY_PAIR);
    ob_start(); include __DIR__.'/../Views/cash/index.php'; return ob_get_clean();
  }
  public function newIncome(){ Auth::requireRole(['ADMIN','CASHIER']);
    if($_SERVER['REQUEST_METHOD']==='POST'){
      DB::pdo()->prepare('INSERT INTO cashbook(kind,source,description,amount,at,user_id) VALUES("INCOME",?,?,?,?,?)')
        ->execute(['Inscription',$_POST['description'],$_POST['amount'],date('Y-m-d H:i:s'),Auth::user()['id']]);
      flash('ok','Entrée enregistrée'); header('Location: index.php?page=cash'); exit;
    }
    ob_start(); include __DIR__.'/../Views/cash/new_income.php'; return ob_get_clean();
  }
  public function newExpense(){ Auth::requireRole(['ADMIN','CASHIER']);
    if($_SERVER['REQUEST_METHOD']==='POST'){
      DB::pdo()->prepare('INSERT INTO cashbook(kind,source,description,amount,at,user_id) VALUES("EXPENSE",?,?,?, ?, ?)')
        ->execute(['Caisse',$_POST['description'],-abs($_POST['amount']),date('Y-m-d H:i:s'),Auth::user()['id']]);
      flash('ok','Dépense enregistrée'); header('Location: index.php?page=cash'); exit;
    }
    ob_start(); include __DIR__.'/../Views/cash/new_expense.php'; return ob_get_clean();
  }
}
```

---

## 10) Personnalisation (thèmes, logo, branding)

**/app/Controllers/SettingController.php** (extraits)
```php
<?php
class SettingController {
  public function general(){ Auth::requireRole(['ADMIN']); if($_SERVER['REQUEST_METHOD']==='POST'){
      DB::pdo()->prepare('REPLACE INTO settings(k,v) VALUES ("school_name",?), ("theme",?)')->execute([$_POST['school_name'], $_POST['theme']]);
      flash('ok','Paramètres enregistrés'); header('Location: index.php?page=settings.general'); exit;
    }
    // charger
    $name=DB::pdo()->query("SELECT v FROM settings WHERE k='school_name'")->fetchColumn();
    $theme=DB::pdo()->query("SELECT v FROM settings WHERE k='theme'")->fetchColumn();
    $themes=DB::pdo()->query('SELECT * FROM themes')->fetchAll();
    ob_start(); include __DIR__.'/../Views/settings/general.php'; return ob_get_clean();
  }
  public function license(){ Auth::requireRole(['ADMIN']); if($_SERVER['REQUEST_METHOD']==='POST'){
      License::set($_POST['license_key'], $_POST['license_until']);
      flash('ok','Licence mise à jour'); header('Location: index.php?page=settings.license'); exit;
    }
    $rows=License::get();
    ob_start(); include __DIR__.'/../Views/settings/license.php'; return ob_get_clean();
  }
}
```

**/app/Views/settings/general.php** (extrait)
```php
<form method="post" class="space-y-4">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
  <label class="block">Nom de l’école
    <input name="school_name" class="border p-2 w-full" value="<?= htmlspecialchars($name) ?>">
  </label>
  <label class="block">Thème
    <select name="theme" class="border p-2">
      <?php foreach($themes as $t): ?><option value="<?= htmlspecialchars($t['name']) ?>" <?= $t['name']===$theme?'selected':'' ?>><?= htmlspecialchars($t['name']) ?></option><?php endforeach; ?>
    </select>
  </label>
  <label class="block">Logo
    <input type="file" name="logo" accept="image/*" class="border p-2">
  </label>
  <button class="px-4 py-2 bg-green-700 text-white rounded">Enregistrer</button>
</form>
```

---

## 11) Tailwind & thèmes (approche simple)

Dans `layout.php`, injectez des variables CSS depuis `themes.css_vars` :
```php
<?php $themeVars = json_decode($currentTheme['css_vars'] ?? '{}', true); ?>
<style>
:root { <?php foreach($themeVars as $k=>$v) echo "$k:$v;"; ?> }
</style>
<link rel="stylesheet" href="/assets/tailwind.css">
```

Exemple `themes` (seed.sql):
```sql
INSERT INTO themes(name, css_vars) VALUES
('default', '{"--bg":"#ffffff","--primary":"#14532d","--text":"#0a0a0a"}'),
('dark',    '{"--bg":"#0b1220","--primary":"#3b82f6","--text":"#e5e7eb"}');
```

---

## 12) Rapports financiers utiles

- **Liste des élèves en défaut de paiement**: voir `ReportController::arrears()`
- **Situation de paiement par élève (ledger)**: `ReportController::studentLedger()`
- **Caisse (journal)**: somme des INCOME vs EXPENSE + export CSV

**Export CSV exemple**
```php
$fp=fopen(__DIR__.'/../../storage/exports/cash_'.date('Ymd').'.csv','w');
fputcsv($fp,['Date','Type','Source','Description','Montant']);
$st=DB::pdo()->query('SELECT at,kind,source,description,amount FROM cashbook ORDER BY at DESC');
foreach($st as $r) fputcsv($fp,[$r['at'],$r['kind'],$r['source'],$r['description'],$r['amount']]);
fclose($fp);
```

---

## 13) Sécurité & bonnes pratiques

- PDO + requêtes préparées ✅
- Hashage: `password_hash()`/`password_verify()` ✅
- CSRF token pour POST ✅
- Rôles (RBAC) : ADMIN, CASHIER, TEACHER, VIEWER ✅
- Validation de licence **obligatoire** (bloque l’accès hors page licence) ✅
- Journalisation minimale `/storage/logs/app.log` (ajouter try/catch autour des opérations critiques)

---

## 14) Installation rapide

1. Créez DB `educhad`; importez `/scripts/migrate.sql` puis `/scripts/seed.sql`.
2. Copiez `/app/.env.php` à partir de l’exemple plus haut.
3. Lancez un serveur local: `php -S localhost:8000 -t public`.
4. Connectez-vous avec l’admin créé dans `seed.sql` (ex: admin@educhad.local / mot de passe hashé fourni).
5. Allez dans **Paramètres → Licence** et collez la clé fournie pour l’année en cours.

---

## 15) Seed minimal (utilisateur, classes, matières)

```sql
INSERT INTO users(name,email,password_hash,role) VALUES
('Administrateur','admin@educhad.local', '$2y$10$9x7e1x3vGm2a8tKJr9PjHeoYl3Yk3Q8mC7sG1zV9o5r1oNw0JrLmi', 'ADMIN');
-- mot de passe: Admin@123 (à changer au 1er login)

INSERT INTO classes(name, level) VALUES ('6ème',1),('5ème',2),('4ème',3),('3ème',4),('2nde',5),('1ère',6),('Tle',7);
INSERT INTO subjects(name) VALUES ('Français'),('Mathématiques'),('SVT'),('Physique-Chimie'),('Histoire-Géo'),('Anglais');
-- coefficients exemple pour 2nde
INSERT INTO class_subjects(class_id,subject_id,coefficient)
SELECT c.id, s.id, CASE s.name
  WHEN 'Mathématiques' THEN 5
  WHEN 'Physique-Chimie' THEN 4
  WHEN 'Français' THEN 4
  WHEN 'Anglais' THEN 3
  ELSE 2 END
FROM classes c CROSS JOIN subjects s WHERE c.name='2nde';
```

---

## 16) Points d’extension & idées futures

- Impression PDF (ajouter dompdf en local – offline – via `/vendor`)
- Import Excel/CSV des élèves et notes
- Barcodes/QR pour cartes d’élèves & factures
- Sauvegardes automatiques SQL (dump) avec planification système
- Historique/traçabilité des modifications (audit log)
- Multilingue (français/anglais/arabe)

---

## 17) Licence – mode opératoire

- L’éditeur (vous) génère une clé annuelle liée au **nom d’école** et **à l’année** via `scripts/generate_license.php`.
- L’établissement colle la clé + date d’expiration (au 31/12/AAAA ou 31/08/AAAA selon votre calendrier) dans **Paramètres → Licence**.
- **Offline**: la validation se fait localement via HMAC (pas d’appel réseau). En cas d’expiration, seul l’écran Licence est accessible.

---

## 18) UI – Layout Tailwind (extrait `layout.php`)

```php
<!doctype html>
<html lang="fr" class="bg-[var(--bg)] text-[var(--text)]">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/assets/tailwind.css">
  <title><?= htmlspecialchars($title ?? 'EduChad') ?></title>
</head>
<body class="min-h-screen">
  <?php include __DIR__.'/partials/topnav.php'; ?>
  <div class="flex">
    <?php include __DIR__.'/partials/sidebar.php'; ?>
    <main class="flex-1 p-6">
      <?php include __DIR__.'/partials/alerts.php'; ?>
      <?= $content ?>
    </main>
  </div>
</body>
</html>
```

---

**Ce document fournit une base complète et modulaire.** Vous pouvez copier ce dossier, ajuster les vues et compléter les contrôleurs selon vos besoins locaux (programmes, trimestres/semestres, etc.).

