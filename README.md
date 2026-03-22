# Locarore

Application de location de matériel de décoration événementielle.

## Stack

- **PHP 8.5** (FPM)
- **MySQL 8**
- **Nginx**
- **Tailwind CSS**
- Architecture **DDD** — namespace `Rore\`

## Démarrage rapide

```bash
# Construire les images
make build

# Démarrer les conteneurs
make start

# Compiler le CSS Tailwind
npm install
make css

# Accès
# Site     → http://localhost:8080
# Admin    → http://localhost:8080/admin  (mot de passe : admin123)
```

## Commandes Make

| Commande | Description |
|---|---|
| `make build` | Build Docker images |
| `make start` | Start containers |
| `make stop` | Stop containers |
| `make restart` | Restart containers |
| `make logs` | Follow logs |
| `make css` | Build CSS (minified) |
| `make css-watch` | Watch CSS |

## Structure

```
src/
  Domain/        ← Entités, interfaces, services métier
  Application/   ← Use cases, CartSession
  Infrastructure ← PDO, repositories MySQL, FileUploader, Router
  Presentation/  ← Controllers (Admin + Site)
templates/       ← Vues PHP + Tailwind
public/          ← Document root (index.php, assets)
sql/             ← schema.sql
config/          ← app.ini
```
# locarore
