# WP Seed Content Kit

WP Seed Content Kit is a small editorial framework plugin for WordPress.

It provides reusable structured content and shortcode-based displays without requiring a specific theme, builder, ACF, Composer, npm, or any external service.

## V1 scope

V1 includes only:

- testimonials as structured content;
- cards for native WordPress posts;
- shortcode integration;
- scoped CSS with the `seed-` prefix.

V1 does not include:

- ACF integration;
- Quotes;
- Stage or event modules;
- admin settings;
- Gutenberg custom blocks;
- Divi custom modules;
- imports or migrations.

## Requirements

- WordPress.
- A theme capable of rendering normal shortcodes.
- PHP compatible with the target WordPress installation.

No external dependency is required.

ACF is not required.

## Available shortcodes

```text
[seed_cards]
```

Displays native WordPress posts as cards.

```text
[seed_cards category="inspirations" limit="3" columns="3"]
```

Displays up to 3 native WordPress posts from the `inspirations` category in 3 columns.

```text
[seed_testimonials]
```

Displays published testimonials that have publication consent enabled.

## ZIP installation

The ZIP should contain a single root folder:

```text
wp-seed-content-kit/
```

Minimum expected structure:

```text
wp-seed-content-kit/
- wp-seed-content-kit.php
- includes/
- assets/
- README.md
- docs/
```

Install from WordPress admin:

1. Go to Plugins > Add New.
2. Upload the ZIP.
3. Activate WP Seed Content Kit.
4. Test shortcodes on a draft page first.

## Uninstall and rollback

For a simple rollback:

1. Remove shortcodes from test pages if needed.
2. Deactivate the plugin.
3. Confirm existing pages and posts remain accessible.

The plugin must not modify native posts or pages automatically.

Before any production test, create a database and file backup.

## Documentation

- `docs/USAGE.md`
- `docs/TESTING.md`
