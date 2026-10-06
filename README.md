# Care Appliance Aircon Trading - Oroquieta branch

Local WordPress website for **Care Appliance Aircon Trading (Oroquieta branch)** - direct aircon dealer, installer & service center in Purok 2, Upper Loboc, Oroquieta City. Built as an **Astra child theme** with **Elementor** (free) for the landing page, running in Docker.

- **Local URL**: http://localhost:8083
- **WP Admin**: http://localhost:8083/wp-admin
- **Username**: `franzlystrr` / **Password**: `YourNewPasswordHere` *(placeholder - change it in wp-admin under Users > Profile before going live)*
- **Facebook**: https://www.facebook.com/profile.php?id=100064011216452

## Business facts (source: their FB page + cover banner)

- Direct aircon supplier/dealer & installer; trades spare parts & installation materials
- Brands carried: Daikin, Midea, TCL, Samsung, Carrier, Condura, General Royal, ChiQ, Fujidenzo, Koppel, Panasonic, G.E.
- Free installation labor & materials at direct "bodega" prices; delivery available
- Phones: 0964-086-2665 (primary), 0910-003-4325 (on their banner); FB About also lists 0912-587-0708
- Address: Gen. Deloso St., Purok 2, Upper Loboc, Oroquieta City
- ~11,000+ FB followers (social proof used on the page)

## Quick start

```bash
docker compose up -d
```

Stop with `docker compose down` (add `-v` to wipe the database - destructive).

## Project structure

```
Care Appliances/
├── docker-compose.yml      WordPress + MySQL 8.0 (port 8083 -> 80)
├── build-page.php          One-shot Elementor page builder (run inside container; not web-accessible)
├── fb-images/cover.jpg     Their FB cover banner (source copy)
├── themes/
│   ├── astra/              Astra parent theme 4.14.0 - DO NOT EDIT
│   └── astra-child/        All custom code lives here
│       ├── style.css       Design tokens (--ca-*) + header logo sizing + helpers
│       ├── functions.php   Asset loading + Elementor theme locations
│       └── assets/img/     logo.jpg (OZAMIZ wordmark removed) + brand-banner.jpg (imported into the media library)
└── README.md
```

## How the landing page works

The Home page (id 5, front page) is a **pure Elementor document** - 8 container sections: hero (gradient), brands strip, services cards, why-us + call card, about + brand banner, Facebook page embed, contact + Google map, final CTA. Edited visually in Elementor at http://localhost:8083/wp-admin.

- Design tokens in `themes/astra-child/style.css` use the brand colors from their banner (`--ca-primary: #189ae0`, `--ca-dark: #0d3a5c`, `--ca-accent: #ff8a00`). Mirror them in Elementor's Site Settings > Global Colors if you extend the design.
- The header logo + favicon come from `themes/astra-child/assets/img/logo.jpg` (attachment #9; the original had the "OZAMIZ" wordmark, which was removed — the heart tip and hand V-gap were reconstructed so the design reads naturally).
- Full-bleed sections rely on the Astra page meta `site-content-layout = page-builder` (set on the Home page) plus a CSS fallback in the child theme.
- `build-page.php` can regenerate the whole page (idempotent): copy it back into `themes/astra-child/`, run `docker compose exec wordpress php wp-content/themes/astra-child/build-page.php`, then remove it again. Never leave it in the themes folder - it would be web-executable.

## Elementor 4.x container gotchas (learned the hard way)

- Containers use `content_width` => `'boxed'` | `'full'` (NOT 'full-width'). Only with `'full'` does the `width` control (e.g. `{'unit'=>'%','size'=>31.8}`) apply - that's how multi-column rows of containers are built.
- Desktop/tablet rules land inside `@media(min-width:768px)`, `_mobile` settings inside `@media(max-width:767px)`.
- Elementor syncs site options (blogname) into its kit on `update_option`, which requires a capable user: call `wp_set_current_user(1)` in CLI scripts before touching options.
- The `wordpress:cli` image rewrites `wp-config.php` on every run - always pass the `WORDPRESS_DB_*` env vars and mount `themes/` or the child theme is invisible to wp-cli (full command in git history / below).

## wp-cli one-off

```bash
docker run --rm --network care-appliances_default \
  -v care-appliances_wordpress_data:/var/www/html \
  -v "D:/wordpress/Care Appliances/themes:/var/www/html/wp-content/themes" \
  -u 33:33 \
  -e WORDPRESS_DB_HOST=db:3306 -e WORDPRESS_DB_USER=wordpress \
  -e WORDPRESS_DB_PASSWORD=wordpress_password -e WORDPRESS_DB_NAME=wordpress_care_appliances \
  wordpress:cli wp <command>
```

## Ports in use on this machine

| Site | Port |
|------|------|
| Snakes | 8080 |
| Bellas | 8081 |
| Landoys | 8082 |
| **Care Appliances** | **8083** |
