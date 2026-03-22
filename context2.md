# Locarore — Contexte projet exhaustif (23 mars 2026)

> Ce document est le contexte de reprise du projet. Il ne déclenche aucune action à sa lecture — il sert uniquement à reconstituer l'état complet du projet pour reprendre le travail dans de bonnes conditions.

---

## 1. Vision métier

Application de **gestion de location de matériel de décoration** (lettres géantes lumineuses, vases, arches, etc.).

### Partie publique
- Navigation arborescente par catégories, URLs slug SEO
- Fiche produit avec carousel photos, description WYSIWYG, prix indicatif
- Panier : un panier = un intervalle de dates + plusieurs produits/quantités
- Granularité à la journée (pas d'heures)
- La validation du panier crée une **demande de réservation** en statut `pending` — elle doit être confirmée manuellement en backoffice

### Partie privée (admin)
- Authentification par mot de passe simple (config)
- CRUD catégories (parentes/enfants), produits, packs, photos
- Gestion des réservations avec machine à états
- Calendrier global et par produit
- Vue détaillée réservation avec comparatif de prix

---

## 2. Stack technique

| Élément | Choix |
|---|---|
| Langage | PHP 8.5-fpm |
| Base de données | MySQL 8.0 |
| Serveur | Nginx alpine |
| Infra | Docker Compose v2 (`sudo docker compose`) |
| Frontend | Tailwind CSS (build npm + CDN pour le WYSIWYG) |
| WYSIWYG | EasyMDE (Markdown) |
| Framework | Aucun — DDD maison |
| ORM | Aucun — PDO direct |
| Namespace | `Rore\` |
| Hébergement cible | OVH mutualisé (PHP + MySQL) |

### Commandes Docker
```bash
sudo docker compose up -d       # démarrer
sudo docker compose down        # arrêter
sudo docker compose ps          # état
sudo docker exec -i locarore-mysql-1 mysql -u locarore -plocarore locarore < fichier.sql
```

### Commandes Make
```bash
make build / make start / make stop / make restart / make logs
make css          # build Tailwind one-shot
make css-watch    # build Tailwind en watch
```

---

## 3. Architecture DDD

```
src/
  Application/
    Cart/               # AddToCart, RemoveFromCart, SetCartDates, CheckoutUseCase
    Catalog/            # Create/Update Category, Product, Pack, ToggleX, UploadPhoto
    Reservation/        # Create, Confirm, Cancel, SetReservationStatus, GetReservations
  Domain/
    Catalog/
      Entity/           # Category, Product, ProductPhoto, Pack, PackItem
      Repository/       # CategoryRepositoryInterface, ProductRepositoryInterface, PackRepositoryInterface
      ValueObject/      # Slug
    Reservation/
      Entity/           # Reservation, ReservationItem
      Repository/       # ReservationRepositoryInterface
      Service/          # AvailabilityService
  Infrastructure/
    Database/           # Connection (PDO singleton)
    Http/               # Router
    Persistence/        # MySqlCategoryRepository, MySqlProductRepository, MySqlPackRepository, MySqlReservationRepository
    Storage/            # FileUploader
  Presentation/
    Controller/
      Admin/            # AdminController, AuthController, DashboardController, CategoryController,
                        # ProductController, PackController, ReservationController
      Site/             # HomeController, CategoryController, ProductController, CartController
      Controller.php    # Base controller (render, redirect, flash, requirePost)
lib/
  helpers.php           # e(), slugify(), formatDate(), dateRangeLabel(), nbDays(), statusLabel(), statusBadgeClass()
templates/
  admin/                # login, dashboard, categories/, products/, packs/, reservations/
  site/                 # home, category, product, cart, checkout, confirmation, partials/
config/
  app.ini               # config principale avec placeholders ${VAR}
.env                    # secrets réels (ignoré par git)
.env.example            # template à commit
public/
  index.php             # point d'entrée unique, autoload, .env loader, router
  .htaccess             # redirige tout vers index.php
  assets/
    css/app.css         # Tailwind compilé
    uploads/products/   # photos uploadées (ignoré par git)
sql/
  schema.sql            # schéma complet (source de vérité)
  migration_v2.sql      # migrations historiques
  migration_pricing.sql # migration tarification (price_base, price_extra_we, price_extra_sem)
```

---

## 4. Configuration

### `config/app.ini`
```ini
[app]
name = "Locarore"
env  = development

[admin]
password = ${ADMIN_PASSWORD}

[database]
host     = mysql
port     = 3306
name     = locarore
user     = locarore
password = ${DB_PASSWORD}
charset  = utf8mb4

[upload]
max_size      = 5242880
allowed_types = image/jpeg,image/png,image/webp
upload_path   = /assets/uploads/products
```

### `.env` (git-ignoré, à créer localement)
```
ADMIN_PASSWORD=admin123
DB_PASSWORD=locarore
```

### `.env.example` (git-tracké)
```
ADMIN_PASSWORD=changeme
DB_PASSWORD=changeme
```

### Chargement dans `public/index.php`
```php
// Charge .env → $_ENV + putenv()
// Charge app.ini → résout ${VAR} via preg_replace_callback → parse_ini_string()
```

---

## 5. Schéma de base de données

### `categories`
```sql
id, parent_id (nullable → sous-catégorie), name, slug (unique), description, is_active, created_at, updated_at
```

### `products`
```sql
id, category_id (FK catégorie principale), name, slug (unique), description, stock,
price_base DECIMAL(10,2) DEFAULT 80.00,
price_extra_we DECIMAL(10,2) DEFAULT 0.00,
price_extra_sem DECIMAL(10,2) DEFAULT 15.00,
is_active, created_at, updated_at
```

### `product_categories` (pivot M:N)
```sql
product_id, category_id  — un produit peut appartenir à plusieurs catégories
```

### `product_photos`
```sql
id, product_id, filename, sort_order, created_at
```

### `reservations`
```sql
id, customer_name, customer_email, customer_phone, customer_address, event_address,
start_date DATE, end_date DATE,
status ENUM('pending','quoted','confirmed','cancelled') DEFAULT 'pending',
notes, created_at, updated_at
```

### `reservation_items`
```sql
id, reservation_id, product_id, pack_id (nullable), quantity,
unit_price_snapshot DECIMAL(10,2) NULL   -- prix unitaire capturé au moment du checkout
```

### `packs`
```sql
id, name, slug (unique), description, price_per_day DECIMAL(10,2), is_active, created_at, updated_at
```
> Note : les packs ont encore `price_per_day` (ancien modèle). À harmoniser si besoin.

### `pack_items`
```sql
id, pack_id, product_id, quantity
```

---

## 6. Modèle de tarification des produits

### Règle
$$\text{Prix} = \text{price\_base} + \max(0,\ \text{jours} - 2) \times \text{taux}$$

- **`price_base`** : forfait de base (défaut 80 €), couvre les **1 à 2 premiers jours**
- **`price_extra_we`** : supplément par jour supplémentaire si la période contient **un samedi ET un dimanche ET ≤ 4 jours** (défaut 0 €)
- **`price_extra_sem`** : supplément par jour supplémentaire sinon (défaut 15 €)

### Exemples
| Période | Jours | WE ? | Calcul | Prix |
|---|---|---|---|---|
| Ven→Lun (récup ven, retour lun) | 4 | oui (sam+dim) | 80 + (2×0) | **80 €** |
| Lun→Mer | 3 | non | 80 + (1×15) | **95 €** |
| Lun (journée) | 1 | non | 80 + 0 | **80 €** |

### Implémentation
`Product::calculatePrice(string|DateTimeImmutable $start, string|DateTimeImmutable $end): float`
— accepte string ou DateTimeImmutable pour les deux arguments (la session stocke des strings).

### Prix capturé (`unit_price_snapshot`)
Au moment du **checkout**, `CheckoutUseCase` appelle `calculatePrice()` pour chaque produit et stocke le résultat dans `reservation_items.unit_price_snapshot`.

En backoffice (`show.php`), le tableau des articles affiche :
- **Prix capturé** (au moment de la commande)
- **Prix actuel** (recalculé en temps réel) — en orange avec ⚠ si différent du snapshot
- **Total capturé** par ligne
- Total global + total "au tarif actuel" si les deux diffèrent

---

## 7. Statuts de réservation

```
pending → quoted → confirmed → cancelled
          ↕        ↕ ↕
       (retours arrière possibles dans tous les sens)
```

| Statut | Couleur | Bloque le stock |
|---|---|---|
| `pending` | jaune | oui (partiel — compte dans la dispo) |
| `quoted` | orange | oui (bloque comme confirmed) |
| `confirmed` | vert | oui |
| `cancelled` | rouge | non |

### Transitions disponibles depuis le backoffice
- `pending` → devis, confirmer, annuler
- `quoted` → confirmer, ↩ pending, annuler
- `confirmed` → ↩ devis, ↩ pending, annuler
- `cancelled` → ↩ pending

### Impact stock
Dans `MySqlReservationRepository` :
- `findConfirmedOverlapping()` : `WHERE status IN ('confirmed','quoted')`
- `countReservedQtyForProduct()` : `WHERE status IN ('pending','quoted','confirmed')`
- `getReservedPeriodsByProduct()` : `WHERE status IN ('confirmed','quoted')`

---

## 8. Router

`Router` maison dans `src/Infrastructure/Http/Router.php`.

- `{param}` — segment unique
- `{param+}` — multi-segments (pour les URLs arborescentes)

### URLs publiques
```
GET  /
GET  /categorie/{path+}          → CategoryController::show()
GET  /produit/{path+}            → ProductController::show()
GET  /panier                     → CartController::index()
POST /panier/dates               → CartController::setDates()
POST /panier/ajouter             → CartController::add()
POST /panier/supprimer           → CartController::remove()
GET  /panier/checkout            → CartController::checkout()
POST /panier/checkout            → CartController::processCheckout()
GET  /panier/confirmation        → CartController::confirmation()
```

### URLs admin
```
GET/POST /admin                  → AuthController::login()
POST     /admin/connexion        → AuthController::processLogin()
POST     /admin/deconnexion      → AuthController::logout()
GET      /admin/dashboard
GET/POST /admin/categories/creer
GET/POST /admin/categories/{id}/modifier
POST     /admin/categories/{id}/toggle
GET/POST /admin/produits/creer
GET/POST /admin/produits/{id}/modifier
POST     /admin/produits/{id}/toggle
POST     /admin/produits/{id}/photo
POST     /admin/produits/photo/{photoId}/supprimer
GET/POST /admin/packs/creer
GET/POST /admin/packs/{id}/modifier
POST     /admin/packs/{id}/toggle
GET      /admin/reservations
GET      /admin/reservations/calendrier
GET      /admin/reservations/{id}
POST     /admin/reservations/{id}/confirmer
POST     /admin/reservations/{id}/devis          → quote() → statut quoted
POST     /admin/reservations/{id}/statut         → setStatus() → transitions libres
POST     /admin/reservations/{id}/annuler
```

---

## 9. Panier — comportement détaillé

**Session key** : `$_SESSION['rore_cart']` = `['start_date', 'end_date', 'items' => [productId => qty]]`

**Flux** :
1. Client choisit ses dates (sur la fiche produit ou le panier)
2. Client ajoute des produits — `AvailabilityService` vérifie le stock sur la période
3. Client valide → `CheckoutUseCase` :
   - calcule `unit_price_snapshot` pour chaque item
   - crée la `Reservation` + `ReservationItem[]`
   - vide le panier (`CartSession::clear()`)
4. Réservation créée en `pending`

**Reset des dates** : `POST /panier/dates` avec `start_date=""` → `CartController::setDates()` détecte les valeurs vides et appelle `cart->clear()` (reset complet), sans passer par `SetCartDatesUseCase`.

**Bug corrigé** : auparavant, "Modifier les dates" sur la fiche produit était un `<a href="/panier">` qui ne réinitialisait jamais les dates. Remplacé par un `<form POST>` vers `/panier/dates` avec des valeurs vides.

---

## 10. Calendrier des réservations

### Calendrier global (`/admin/reservations/calendrier`)
- Template : `templates/admin/reservations/calendar.php`
- Couleurs : vert = confirmé, orange = devis (quoted), jaune = pending

### Calendrier produit (admin seulement, fiche `/admin/produits/{id}/modifier`)
- Section ajoutée en bas de `templates/admin/products/form.php`
- JS pur, affiche 6 mois
- Couleurs : vert = disponible, orange = devis, rouge = confirmé, gris = passé
- Données via `$calendarEvents` : array `[start, end, qty, status]`
- Source : `MySqlReservationRepository::getReservedPeriodsByProduct()`

---

## 11. Entités Domain — résumé

### `Product`
```php
id, categoryId, name, slug, description, stock,
priceBase, priceExtraWe, priceExtraSem,
isActive, createdAt, updatedAt
// + photos[], categoryIds[] (chargés après)
calculatePrice(start, end): float
```

### `Category`
```php
id, parentId (nullable), name, slug, description, isActive, createdAt, updatedAt
```

### `Pack`
```php
id, name, slug, description, pricePerDay, isActive, createdAt, updatedAt
// + items[] PackItem
```

### `Reservation`
```php
id, customerName, customerEmail, customerPhone, customerAddress, eventAddress,
startDate, endDate, status, notes, createdAt, updatedAt
// + items[] ReservationItem
isPending(), isQuoted(), isConfirmed(), isCancelled()
```

### `ReservationItem`
```php
id, reservationId, productId, quantity, packId (nullable),
unitPriceSnapshot (nullable float)  // capturé au checkout
getUnitPriceSnapshot(): ?float
getTotalSnapshot(): ?float          // unitPrice × quantity
```

---

## 12. Helpers (`lib/helpers.php`)

```php
e(mixed $value): string                          // htmlspecialchars
slugify(string $text): string                    // slug SEO avec diacritiques
formatDate($date, $format): string               // formatage date
dateRangeLabel($start, $end): string             // "du 12 juin au 14 juin 2026"
nbDays($start, $end): int                        // nombre de jours inclusif
statusLabel(string $status): string              // "En attente", "Devis envoyé", etc.
statusBadgeClass(string $status): string         // classes Tailwind pour le badge
```

---

## 13. Fichiers importants à connaître

| Fichier | Rôle |
|---|---|
| `public/index.php` | Point d'entrée : autoload, .env, config, session, routes |
| `lib/helpers.php` | Fonctions globales |
| `config/app.ini` | Config app (passwords via ${VAR}) |
| `.env` | Secrets (ignoré git) |
| `sql/schema.sql` | Schéma de référence |
| `src/Domain/Catalog/Entity/Product.php` | Logique prix (`calculatePrice`) |
| `src/Application/Cart/CheckoutUseCase.php` | Capture des prix snapshot |
| `src/Application/Reservation/SetReservationStatusUseCase.php` | Transitions statuts |
| `src/Infrastructure/Persistence/MySqlReservationRepository.php` | Requêtes dispo/stock/calendrier |
| `templates/admin/reservations/show.php` | Vue détaillée réservation avec comparatif prix |
| `templates/admin/reservations/calendar.php` | Calendrier global admin |
| `templates/admin/products/form.php` | Formulaire produit + calendrier produit |
| `templates/site/cart.php` | Panier client |

---

## 14. Points de vigilance / dette technique

- Les **packs** ont encore `price_per_day` (ancien modèle) — ils n'ont pas été migrés vers le modèle WE/SEM. À harmoniser si un pack peut être ajouté directement au panier.
- Les **réservations existantes** avant la migration des prix auront `unit_price_snapshot = NULL` — le backoffice affiche `—` dans ce cas.
- Le `Makefile` utilise `docker compose` sans `sudo` — sur la machine de dev il faut `sudo make start` (ou ajouter l'utilisateur au groupe docker).
- Tailwind est buildé en local avec npm (`npx tailwindcss ...`) — il faut un `make css` après tout ajout de nouvelles classes.
- Les photos uploadées sont stockées dans `public/assets/uploads/products/` (ignoré par git).

---

## 15. Migrations appliquées

| Fichier | Contenu |
|---|---|
| `sql/schema.sql` | Schéma complet initial |
| `sql/migration_v2.sql` | Migrations historiques (catégories parentes, packs, slugs, etc.) |
| `sql/migration_pricing.sql` | `ALTER TABLE products CHANGE price_per_day → price_base + ADD price_extra_we + price_extra_sem` |
| En base directement | `ALTER TABLE reservation_items ADD unit_price_snapshot DECIMAL(10,2) NULL` |
| En base directement | `ALTER TABLE reservations CHANGE status ENUM + 'quoted'` |
