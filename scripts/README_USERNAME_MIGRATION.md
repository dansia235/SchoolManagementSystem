# Migration Username - Instructions

Ce document explique comment migrer vers le système de connexion par username.

## 🔄 Pour une nouvelle installation

Si vous installez EduChad pour la première fois :

```bash
# 1. Créer la base de données
mysql -u root -p
CREATE DATABASE educhad CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

# 2. Importer le schéma complet (inclut déjà la colonne username)
mysql -u root -p educhad < scripts/migrate.sql

# 3. Importer les données de démonstration
mysql -u root -p educhad < scripts/seed.sql
```

Vous pouvez maintenant vous connecter avec :
- **Username:** `admin` | **Password:** `admin123`
- **Username:** `enseignant` | **Password:** `admin123`
- **Username:** `caissier` | **Password:** `admin123`
- **Username:** `observateur` | **Password:** `admin123`

## 🔧 Pour une installation existante

Si vous avez déjà une base de données EduChad sans la colonne username :

```bash
# Appliquer la migration pour ajouter la colonne username
mysql -u root -p educhad < scripts/add_username.sql
```

Cette migration va :
1. ✅ Ajouter la colonne `username` à la table `users`
2. ✅ Créer des usernames pour les utilisateurs existants
3. ✅ Rendre la colonne `username` obligatoire et unique

## 📋 Comptes par défaut

Après la migration, les comptes suivants seront disponibles :

| Rôle | Username | Password | Email |
|------|----------|----------|-------|
| 👨‍💼 **Administrateur** | `admin` | `admin123` | admin@educhad.local |
| 👨‍🏫 **Enseignant** | `enseignant` | `admin123` | teacher@educhad.local |
| 💰 **Caissier** | `caissier` | `admin123` | cashier@educhad.local |
| 👁️ **Observateur** | `observateur` | `admin123` | viewer@educhad.local |

## ⚠️ Important

1. **Changez les mots de passe** après la première connexion !
2. Le mot de passe par défaut `admin123` est simple pour faciliter les tests
3. Pour la production, utilisez des mots de passe forts

## 🧪 Tester la connexion

1. Accédez à : `http://localhost/educhad` ou `http://localhost:8000`
2. Entrez un username (ex: `admin`)
3. Entrez le mot de passe (`admin123`)
4. Cliquez sur "Se connecter"

## 🔐 Générer un nouveau hash de mot de passe (PHP)

Si vous souhaitez changer les mots de passe :

```php
<?php
echo password_hash('votre_mot_de_passe', PASSWORD_DEFAULT);
?>
```

Puis mettez à jour dans la base de données :

```sql
UPDATE users SET password_hash = 'NOUVEAU_HASH_ICI' WHERE username = 'admin';
```

## ✅ Vérification

Pour vérifier que la migration a réussi :

```sql
-- Afficher tous les utilisateurs avec leurs usernames
SELECT id, username, name, email, role, is_active FROM users;
```

Vous devriez voir tous les utilisateurs avec leur username correctement défini.
