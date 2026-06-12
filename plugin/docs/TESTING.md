# Testing - WP Seed Content Kit

## Goal

This document defines the minimum manual test path before using the V1 ZIP on a WordPress site.

Do not deploy directly to production before validating locally or on the laboratory site.

## Preconditions

- WordPress is available.
- A backup exists before any non-local test.
- The plugin ZIP contains only the plugin folder and required files.
- No ACF plugin is required.
- No Composer install is required.
- No npm install is required.
- No external service is required.

## ZIP install test

1. Build or prepare the ZIP with one root folder:

```text
wp-seed-content-kit/
```

2. Install it from WordPress admin.
3. Activate the plugin.
4. Confirm there is no fatal error.
5. Confirm the plugin can be deactivated.
6. Reactivate the plugin for shortcode tests.

## Testimonials tests

1. Create a new testimonial.
2. Fill in name or initials.
3. Fill in testimonial text.
4. Fill in context.
5. Fill in date.
6. Publish without publication consent.
7. Add this shortcode to a draft page:

```text
[seed_testimonials]
```

8. Confirm the testimonial is not displayed.
9. Enable publication consent.
10. Confirm the testimonial is displayed.
11. Enable featured.
12. Test:

```text
[seed_testimonials featured="true"]
[seed_testimonials featured="false"]
[seed_testimonials limit="1" columns="1"]
[seed_testimonials limit="3" columns="3"]
```

## Cards tests

1. Confirm native WordPress posts exist.
2. Add this shortcode to a draft page:

```text
[seed_cards]
```

3. Confirm cards are displayed.
4. Test:

```text
[seed_cards category="inspirations" limit="3" columns="3"]
[seed_cards show_image="false"]
[seed_cards show_excerpt="false"]
[seed_cards show_button="false"]
```

5. Test a missing category slug.
6. Confirm an empty state is displayed without public PHP errors.

## Responsive tests

Check at minimum:

- mobile around 375 px;
- tablet around 768 px;
- desktop around 1280 px.

Confirm:

- cards do not overflow;
- text remains readable;
- images keep a stable ratio;
- grids collapse cleanly.

## Builder and theme checks

Test the shortcodes where available:

- Gutenberg shortcode block;
- Spectra shortcode context;
- Divi Code or Text module;
- Astra-based page.

Confirm:

- no theme layout breaks;
- no builder block is modified;
- neighboring sections are not affected;
- CSS remains scoped to `seed-` classes.

## Rollback test

1. Remove shortcodes from draft test pages if needed.
2. Deactivate the plugin.
3. Confirm existing pages remain accessible.
4. Confirm existing posts remain accessible.
5. Confirm WordPress admin remains accessible.

## Release blockers

Do not release the ZIP if:

- PHP syntax has not been validated;
- the plugin cannot activate;
- shortcodes show public PHP errors;
- existing pages or posts are modified unexpectedly;
- ACF or another external dependency becomes required;
- a site-specific category or module is hardcoded.
