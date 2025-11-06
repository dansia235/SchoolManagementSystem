# EduChad - Système de Gestion d'École Secondaire

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-purple.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)
![License](https://img.shields.io/badge/license-Proprietary-red.svg)

**EduChad** est un système complet de gestion scolaire conçu pour fonctionner 100% offline (hors ligne), adapté aux écoles secondaires du Tchad et d'Afrique francophone.

## 🌟 Fonctionnalités Principales

### 📚 Gestion Académique
- **Gestion des élèves**: Inscription, suivi, dossiers complets
- **Gestion des classes**: Organisation par niveaux (6ème à Terminale)
- **Gestion des matières**: Coefficients personnalisables par classe
- **Saisie des notes**: Par trimestre, avec calcul automatique des moyennes
- **Bulletins**: Génération et impression de bulletins scolaires
- **Classements**: Calcul automatique des rangs par classe

### 💰 Gestion Financière
- **Facturation**: Création et suivi des factures élèves
- **Paiements**: Enregistrement avec méthodes multiples (Espèces, Mobile Money, Banque)
- **Caisse**: Journal de caisse (entrées/sorties)
- **Impayés**: Suivi des élèves en situation d'impayé
- **Rapports financiers**: États détaillés et exports CSV

### 👥 Gestion Administrative
- **Multi-utilisateurs**: Rôles définis (Admin, Caissier, Enseignant, Observateur)
- **Personnalisation**: Logo, nom d'école, thèmes de couleurs
- **Système de licence**: Validation annuelle offline par HMAC
- **Sécurité**: Protection CSRF, hashage bcrypt, sessions sécurisées
- **Audit**: Traçabilité des actions importantes

## 🛠️ Stack Technique

- **Backend**: PHP 8.0+ (vanilla, sans framework)
- **Base de données**: MySQL 5.7+ / MariaDB 10.3+
- **Frontend**: HTML5, Tailwind CSS (CDN), JavaScript vanilla
- **Architecture**: MVC personnalisé avec PDO
- **Offline**: 100% autonome, aucune dépendance Internet

## 📋 Prérequis

- **Serveur Web**: Apache 2.4+ ou Nginx (avec PHP-FPM)
- **PHP**: Version 8.0 ou supérieure
  - Extensions requises: PDO, pdo_mysql, mbstring, fileinfo, session
- **MySQL**: Version 5.7+ ou MariaDB 10.3+
- **Espace disque**: Minimum 500 MB (pour l'application et les données)

## 🚀 Installation

### 1. Cloner le projet

```bash
git clone <url-du-repo> educhad
cd educhad
```

### 2. Créer la base de données

```sql
CREATE DATABASE educhad CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'educhad_user'@'localhost' IDENTIFIED BY 'mot_de_passe_securise';
GRANT ALL PRIVILEGES ON educhad.* TO 'educhad_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Importer le schéma et les données

```bash
# Importer le schéma de base
mysql -u educhad_user -p educhad < scripts/migrate.sql

# Importer les données de démonstration (optionnel)
mysql -u educhad_user -p educhad < scripts/seed.sql
```

### 4. Configurer l'environnement

```bash
# Copier le fichier de configuration exemple
cp app/.env.example.php app/.env.php

# Éditer et adapter les paramètres
nano app/.env.php
```

Modifiez les paramètres de connexion à la base de données :

```php
'DB_HOST' => 'localhost',
'DB_NAME' => 'educhad',
'DB_USER' => 'educhad_user',
'DB_PASS' => 'votre_mot_de_passe',
'APP_SECRET' => 'générez_une_clé_aléatoire_longue_64_caractères',
```

### 5. Configurer les permissions

```bash
# Donner les permissions d'écriture aux dossiers nécessaires
chmod -R 755 storage/
chmod -R 755 public/uploads/
chmod 644 storage/logs/app.log
```

### 6. Configurer le serveur web

#### Apache

Le fichier `.htaccess` est déjà inclus dans `public/`. Assurez-vous que `mod_rewrite` est activé :

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Configurez votre VirtualHost :

```apache
<VirtualHost *:80>
    ServerName educhad.local
    DocumentRoot /chemin/vers/educhad/public

    <Directory /chemin/vers/educhad/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Serveur PHP intégré (développement uniquement)

```bash
php -S localhost:8000 -t public/
```

### 7. Accéder à l'application

Ouvrez votre navigateur et accédez à :
- `http://localhost:8000` (serveur PHP)
- `http://educhad.local` (Apache avec VirtualHost)

**Identifiants par défaut:**
- Email: `admin@educhad.local`
- Mot de passe: `Admin@123`

⚠️ **IMPORTANT**: Changez immédiatement le mot de passe administrateur après la première connexion !

## 🔐 Gestion de la Licence

EduChad utilise un système de licence annuelle validé offline.

### Générer une licence

Sur le serveur (éditeur) :

```bash
php scripts/generate_license.php "Nom Exact de l'École" 2025 "VOTRE_APP_SECRET"
```

Exemple :
```bash
php scripts/generate_license.php "Lycée Privé EduChad" 2025 "mon_secret_application"
```

La clé générée sera affichée et sauvegardée dans `scripts/`.

### Activer la licence

1. Connectez-vous en tant qu'administrateur
2. Allez dans **Paramètres → Licence**
3. Collez la clé de licence
4. Saisissez la date d'expiration (ex: 2025-08-31)
5. Cliquez sur "Activer la licence"

⚠️ **Important**: Le nom de l'école dans la base de données doit correspondre EXACTEMENT au nom utilisé pour générer la licence.

## 📁 Structure du Projet

```
educhad/
├── app/
│   ├── bootstrap.php          # Initialisation de l'application
│   ├── config.php             # Configuration globale
│   ├── .env.php              # Configuration sensible (à créer)
│   ├── helpers.php            # Fonctions utilitaires
│   ├── Core/                  # Classes core
│   │   ├── DB.php            # Singleton PDO
│   │   ├── Auth.php          # Authentification & RBAC
│   │   ├── License.php       # Gestion des licences
│   │   └── View.php          # Rendu des vues
│   ├── Controllers/           # Contrôleurs
│   ├── Models/               # Modèles de données
│   └── Views/                # Vues (HTML/PHP)
├── public/
│   ├── index.php             # Point d'entrée
│   ├── assets/               # CSS, JS, images
│   └── uploads/              # Fichiers uploadés
├── scripts/
│   ├── migrate.sql           # Schéma de BDD
│   ├── seed.sql             # Données de démo
│   └── generate_license.php  # Générateur de licences
└── storage/
    ├── logs/                 # Logs applicatifs
    └── exports/              # Exports CSV/PDF
```

## 👥 Rôles et Permissions

| Rôle | Permissions |
|------|-------------|
| **ADMIN** | Accès complet à toutes les fonctionnalités |
| **CASHIER** | Facturation, paiements, caisse, rapports financiers |
| **TEACHER** | Élèves, notes, bulletins |
| **VIEWER** | Consultation uniquement (élèves, bulletins, rapports) |

## 🔧 Configuration Avancée

### Personnalisation du logo

1. Allez dans **Paramètres → Général**
2. Uploadez votre logo (format PNG, JPG, max 2MB)
3. Le logo apparaîtra sur toutes les pages et les bulletins

### Changement de thème

4 thèmes sont disponibles :
- **Défaut (Clair)** : Thème blanc professionnel
- **Sombre** : Pour réduire la fatigue oculaire
- **Vert Nature** : Tons verts apaisants
- **Bleu Professionnel** : Tons bleus corporatifs

### Gestion des utilisateurs

1. Allez dans **Paramètres → Utilisateurs**
2. Créez de nouveaux comptes avec les rôles appropriés
3. Désactivez les comptes inactifs au lieu de les supprimer

## 📊 Rapports Disponibles

- **Bulletins scolaires** : Par élève et par trimestre
- **Listes de classe** : Avec photos et informations
- **État des impayés** : Élèves en retard de paiement
- **Situation de paiement** : Historique par élève
- **Journal de caisse** : Entrées/sorties avec export CSV
- **Statistiques** : Tableau de bord avec KPIs

## 🐛 Dépannage

### Erreur de connexion à la base de données

Vérifiez :
- Les paramètres dans `app/.env.php`
- Que MySQL est démarré : `sudo systemctl status mysql`
- Les permissions de l'utilisateur MySQL

### Erreur 500 (Internal Server Error)

- Activez le mode debug dans `app/.env.php` : `'DEBUG' => true`
- Consultez les logs : `tail -f storage/logs/app.log`
- Vérifiez les permissions des dossiers

### Les images/uploads ne s'affichent pas

```bash
chmod -R 755 public/uploads/
chown -R www-data:www-data public/uploads/
```

### Session expirée trop rapidement

Modifiez dans `app/.env.php` :
```php
'SESSION_LIFETIME' => 28800, // 8 heures au lieu de 2
```

## 🔄 Mise à Jour

1. Sauvegardez la base de données :
```bash
mysqldump -u educhad_user -p educhad > backup_$(date +%Y%m%d).sql
```

2. Sauvegardez les uploads :
```bash
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz public/uploads/
```

3. Récupérez la nouvelle version
4. Exécutez les migrations si nécessaire
5. Testez en mode debug

## 📝 Maintenance

### Sauvegarde quotidienne (cron)

Ajoutez dans votre crontab (`crontab -e`) :

```bash
# Sauvegarde quotidienne à 2h du matin
0 2 * * * mysqldump -u educhad_user -pMOT_DE_PASSE educhad | gzip > /backup/educhad_$(date +\%Y\%m\%d).sql.gz

# Nettoyage des vieux logs (>30 jours)
0 3 * * * find /chemin/vers/educhad/storage/logs/ -name "*.log" -mtime +30 -delete
```

### Optimisation de la base de données

```sql
OPTIMIZE TABLE students, grades, invoices, payments, cashbook;
```

## 🤝 Support

Pour toute question ou problème :

1. Consultez la documentation complète
2. Vérifiez les logs d'erreur
3. Contactez votre fournisseur EduChad

## 📜 Licence

EduChad est un logiciel propriétaire. L'utilisation nécessite l'acquisition d'une licence annuelle.

## ⚖️ Mentions Légales

© 2024-2025 EduChad. Tous droits réservés.

Ce logiciel est fourni "tel quel", sans garantie d'aucune sorte.

---

**Version**: 1.0.0
**Date**: 2024
**Conçu pour**: Écoles secondaires d'Afrique francophone
