# Private Divi Source Audit

This project may need a local, read-only audit of the Divi theme source to identify private hooks, options, or registries used by Divi Builder.

## Local Placement

Place the local Divi theme copy here:

```text
vendor-private/Divi/
```

## Rules

- Never commit `vendor-private/Divi/`.
- Never commit any Divi source file.
- Never copy Divi commercial source code into public documentation.
- Use the local copy only for read-only searches of hooks, options, and integration points.
- Do not publish extracted Divi code snippets.

## Audit Targets

Search the local Divi copy for:

```text
Post Type Integration
et_builder_post_types
et_builder_post_types_blacklist
et_builder_should_load_framework
et_builder_is_post_type_allowed
et_pb_post_types
builder_post_types
custom post types
project
post_types
```

## Goal

Identify the smallest safe integration needed for `seed_template` to appear in Divi Builder Post Type Integration without making the CPT public, changing `publicly_queryable`, adding a custom Divi module, or changing the WP Seed template renderer.
