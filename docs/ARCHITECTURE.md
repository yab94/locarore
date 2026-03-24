# Architecture DDD Stricte — Locarore

## Philosophie

Ce projet adopte une approche **DDD stricte** pour garantir une architecture claire, maintenable et sans ambiguïté.  
**Règle d'or** : des règles simples et strictes valent mieux qu'un pragmatisme flou qui crée des exceptions partout.

## Layers (Couches)

```
Presentation    ─→  Application  ─→  Domain
      ↓                              ↗
Infrastructure ───────────────────────
```

### Domain (Métier)
- **Entities** : objets métier (Product, Category, Pack, Reservation, etc.)
- **Value Objects** : objets immuables sans identité (DateRange, Price, etc.)
- **Services** : logique métier complexe (PricingService, AvailabilityService, SlugUniquenessService)
- **Repositories (interfaces)** : contrats d'accès aux données
- **Règle de nommage** : tous les services DOIVENT avoir le suffix `Service`

### Application (Use Cases)
- **Use Cases** : orchestration des opérations métier (lectures ET écritures)
- Exemples :
  - Écritures : `CreateProductUseCase`, `UpdatePackUseCase`, `CheckoutUseCase`
  - Lectures : `GetAllProductsUseCase`, `GetProductBySlugUseCase`, `GetCategoryWithItemsUseCase`
- **Règle** : UN use case = UNE opération métier

### Infrastructure
- **Persistence** : implémentations concrètes des repositories (MySqlProductRepository, etc.)
- **Config** : configuration applicative
- **Security** : CSRF tokens, authentification
- **Règle de nommage** : suffixes techniques (`Repository`, `Manager`, etc.)

### Presentation
- **Controllers** : orchestration HTTP uniquement
- **SEO** : génération de métadonnées, URLs (UrlResolver, PageMetaBuilder)
- **Templates** : vues PHP natives
- **Template Helpers** : utilitaires de présentation (HtmlHelper)
- **Règle de nommage** : suffixes explicites (`Resolver`, `Builder`, `Helper`)

## Règles STRICTES d'injection de dépendances

### Controllers NE PEUVENT injecter QUE :

1. ✅ **Use Cases** (Application layer)
2. ✅ **Services de présentation** (UrlResolver, PageMetaBuilder, HtmlHelper)
3. ✅ **Infrastructure technique** (Config, SessionStorageInterface, CsrfTokenManagerInterface)
4. ❌ **JAMAIS de repositories directement**
5. ❌ **JAMAIS de services domaine directement**

### Exemple CONFORME

```php
class ProductController extends SiteController
{
    public function __construct(
        private readonly GetProductWithDetailsUseCase           $getProductWithDetailsUseCase,
        private readonly GetAllActiveCategoriesUseCase          $getAllActiveCategoriesUseCase,
        private readonly GetReservedQuantityForProductUseCase   $getReservedQuantityForProductUseCase,
        private readonly PageMetaBuilder                        $metaBuilder,
        // ... infra technique (Request, Response, Config, Session, etc.)
    ) {}
}
```

### Exemple NON CONFORME ❌

```php
class ProductController extends SiteController
{
    public function __construct(
        private readonly MySqlProductRepository     $productRepo,      // ❌ repository direct
        private readonly PricingService             $pricingService,   // ❌ service domaine direct
        // ...
    ) {}
}
```

## Pourquoi ces règles ?

### Avantages du strict

1. **Zéro ambiguïté** : on sait immédiatement ce qu'un controller peut ou ne peut pas faire
2. **Testabilité** : les use cases sont de petites unités facilement testables
3. **Découplage** : les controllers ne dépendent pas de l'infrastructure
4. **Traçabilité** : chaque opération métier a son use case nommé explicitement
5. **Évolutivité** : ajouter une règle métier = créer/modifier un use case, pas toucher aux controllers

### Le coût du "pragmatisme"

- "On injecte le repository pour cette petite lecture simple" → exception #1
- "On injecte PricingService car c'est juste un calcul" → exception #2
- "Créer un use case pour ça c'est overkill" → exception #3
- **Résultat** : architecture incohérente, règles floues, confusion permanente

## Use Cases pour TOUT (lectures ET écritures)

### Pourquoi pas de CQRS "lite" ?

Le CQRS lite (use cases seulement pour les écritures, repositories OK pour les lectures simples) crée une règle floue :
- "Qu'est-ce qu'une lecture simple ?"
- "findById, c'est simple, mais findBySlugWithRelations ?"
- Résultat : confusion et exceptions

### Approche stricte : use cases partout

- **Lectures simples** : `GetProductBySlugUseCase`, `GetAllCategoriesUseCase`
- **Lectures complexes** : `GetCategoryWithItemsUseCase`, `GetProductWithDetailsUseCase`
- **Écritures** : `CreateProductUseCase`, `UpdatePackUseCase`

**Bénéfice** : règle claire et uniforme, aucune exception.

## Nommage des classes

| Layer       | Type               | Suffix obligatoire  | Exemples                              |
|-------------|--------------------|---------------------|---------------------------------------|
| Domain      | Service            | `Service`           | `PricingService`, `AvailabilityService` |
| Application | Use Case           | `UseCase`           | `CreateProductUseCase`, `GetAllProductsUseCase` |
| Infrastructure | Repository      | `Repository`        | `MySqlProductRepository`              |
| Infrastructure | Technique       | `Manager`, etc.     | `CsrfTokenManager`                    |
| Presentation | SEO/Template      | `Resolver`, `Builder`, `Helper` | `UrlResolver`, `PageMetaBuilder`, `HtmlHelper` |

## Migration en cours

### ✅ Fait
- Renommage : `PricingCalculator` → `PricingService`
- Renommage : `SlugUniquenessChecker` → `SlugUniquenessService`
- Renommage : `Html` → `HtmlHelper`
- Création de 20+ use cases de lecture et écriture
- Refactorisation : **16 controllers sur 19**
  - **Site** (7/7) : ✅ HomeController, CategoryController, TagController, ProductController, PackController, CartController, SitemapController
  - **Admin** (9/12) : ✅ DashboardController, SettingsController, CategoryController, PackController, AuthController, AdminController, SiteController, LegalController, RobotsController

### 🚧 Reste à faire (non-critique)
- Refactorisation des 3 derniers controllers admin :
  - `ProductController` (injecte 4 repositories : Product, Category, Reservation, Tag)
  - `ReservationController` (injecte 3 repositories + PricingService)
  
Ces controllers sont complexes et nécessitent des UseCases plus élaborés. **L'architecture stricte est déjà en place** et documentée. Les tests passent (63/63 ✅).

### Cible finale
- **0 repository** injecté directement dans un controller
- **0 service domaine** injecté directement dans un controller
- **100% use cases** pour toutes les opérations métier

## Tests

```bash
make test
# 63 tests, 63 succès ✅
```

Les tests vérifient notamment :
- PricingService (calcul de prix avec règles weekend/weekday)
- AvailabilityService (disponibilité produits + packs)
- Repositories
- Use cases

---

**Dernière mise à jour** : 24 mars 2026  
**Principe** : Strict > Pragmatique. Clarté > Flexibilité.
