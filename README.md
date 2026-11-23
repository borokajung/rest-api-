# Irányítószám REST API (Laravel 12 + Sanctum)

Megyék és települések (irányítószámokkal) REST API-ja. Az olvasó végpontok nyilvánosak, az adatmódosítás
(létrehozás / módosítás / törlés) **Bearer tokenes hitelesítést** igényel (Laravel Sanctum).

- Keresés: település névre és irányítószámra (`?search=`)
- Szűrés megyére (`?county_id=` vagy `?county=`)
- Lapozás (`?per_page=`, `?page=`)
- APIDOC dokumentáció, Bruno tesztkollekció, automata tesztek (PHPUnit)

## Telepítés

```bash
composer install
composer require laravel/sanctum     # ha még nincs a vendor mappában
cp .env.example .env                 # ha még nincs .env
php artisan key:generate
```

`.env` – adatbázis (MySQL, pl. XAMPP; előbb hozd létre a `laravel-rest-api` adatbázist):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel-rest-api
DB_USERNAME=root
DB_PASSWORD=
```

> SQLite-hoz: `DB_CONNECTION=sqlite` és a többi `DB_*` sor törölhető (a `database/database.sqlite` fájl létezik).

```bash
php artisan migrate --seed    # táblák + felhasználó + megyék/települések
php artisan serve             # http://localhost:8000
```

Teszt felhasználó: `user@example.com` / `12345678`

> A Sanctum token-tábla migrációja (`personal_access_tokens`) már a projektben van, ezért az `php artisan install:api` futtatása nem szükséges.

## Végpontok

Alap URL: `http://localhost:8000` – minden válasz JSON (küldj `Accept: application/json` fejlécet).
🔒 = `Authorization: Bearer <token>` kötelező.

| Metódus | Végpont | Auth | Leírás |
|---|---|---|---|
| POST | `/api/login` | – | Bejelentkezés (`email`, `password`) → `token` |
| POST | `/api/logout` | 🔒 | Aktuális token törlése |
| GET | `/api/counties` | – | Megyék listája (`?search=`, `?with_cities=1`) |
| GET | `/api/counties/{id}` | – | Egy megye a településeivel |
| POST | `/api/counties` | 🔒 | Megye létrehozása (`name`) |
| PUT | `/api/counties/{id}` | 🔒 | Megye módosítása (`name`) |
| DELETE | `/api/counties/{id}` | 🔒 | Megye törlése (a települések is törlődnek) |
| GET | `/api/cities` | – | Települések: `?search=`, `?county_id=`, `?county=`, `?per_page=`, `?page=` |
| GET | `/api/cities/{id}` | – | Egy település a megyéjével |
| POST | `/api/cities` | 🔒 | Település létrehozása (`county_id`, `zip_code`, `name`) |
| PUT | `/api/cities/{id}` | 🔒 | Település módosítása |
| DELETE | `/api/cities/{id}` | 🔒 | Település törlése |

### Keresés és szűrés példák

```
GET /api/cities?search=vác              # névre keresés
GET /api/cities?search=26               # irányítószám elejére keresés
GET /api/cities?county_id=14            # szűrés megye azonosító szerint
GET /api/cities?county=pest&search=d    # megye neve + keresés együtt
GET /api/cities?per_page=10&page=2      # lapozás
```

### Hibakódok

| Kód | Jelentés |
|---|---|
| 401 | Hiányzó / érvénytelen token vagy hibás belépési adat |
| 404 | A keresett erőforrás nem létezik |
| 422 | Validációs hiba |

## Tesztelés

**Bruno:** nyisd meg a `bruno/` mappát collectionként, válaszd ki a `local` environmentet, majd futtasd előbb a
`Login` kérést (a token automatikusan beállítódik), utána a többit.

**Automata tesztek:**

```bash
php artisan test
```

## APIDOC dokumentáció

A kommentek a controllerekben (`app/Http/Controllers`) vannak, a beállítás az `apidoc.json`-ban.

```bash
npm install
npm run apidoc          # → public/apidoc/index.html
```

Megnyitás szerver futása közben: <http://localhost:8000/apidoc/index.html>

## Technológiák

Laravel 12, Laravel Sanctum (token auth), MySQL/SQLite, apiDoc, Bruno.
