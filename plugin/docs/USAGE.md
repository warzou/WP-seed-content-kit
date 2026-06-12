# Usage - WP Seed Content Kit

## Purpose

WP Seed Content Kit V1 provides two shortcode-driven display tools:

- `[seed_cards]` for native WordPress posts;
- `[seed_testimonials]` for structured testimonials.

The plugin is designed to work with WordPress shortcodes in Gutenberg, Spectra, Divi, Astra, and classic shortcode contexts.

No ACF field group is required.

No external dependency is required.

## Shortcode: seed_cards

Basic usage:

```text
[seed_cards]
```

Example with a category:

```text
[seed_cards category="inspirations" limit="3" columns="3"]
```

Supported attributes:

```text
limit
columns
category
show_image
show_excerpt
show_button
button_label
```

Examples:

```text
[seed_cards limit="6" columns="3"]
[seed_cards category="inspirations" limit="3" columns="3"]
[seed_cards show_image="false"]
[seed_cards show_excerpt="false"]
[seed_cards show_button="false"]
[seed_cards button_label="Read more"]
```

Notes:

- `category` expects a WordPress category slug.
- If `category` is empty, recent published posts are displayed.
- If the category is missing, an empty state is displayed.
- The shortcode displays native WordPress posts only.

## Shortcode: seed_testimonials

Basic usage:

```text
[seed_testimonials]
```

Supported attributes:

```text
limit
columns
featured
context
```

Examples:

```text
[seed_testimonials]
[seed_testimonials limit="3" columns="3"]
[seed_testimonials featured="true"]
[seed_testimonials featured="false"]
[seed_testimonials context="workshop"]
```

Notes:

- Only published testimonials are queried.
- Only testimonials with publication consent enabled are displayed.
- `featured` accepts `all`, `true`, or `false`.
- `context` filters testimonials by exact context value.

## Testimonials admin fields

The testimonial module provides native fields:

- name or initials;
- text;
- context;
- date;
- publication consent;
- featured.

ACF is not required.

## Styling

The public CSS uses the `seed-` prefix.

The plugin does not apply a CSS reset and should not style global elements like `body`, headings, images, or links without scoped classes.

## What V1 does not do

V1 does not provide:

- Quotes module;
- Stage module;
- ACF integration;
- admin settings;
- custom Gutenberg blocks;
- custom Divi modules;
- import or migration tools.
