# Updates

WP Seed Content Kit can be updated from the WordPress admin through GitHub Releases.

The first release that includes this update checker must still be installed manually by ZIP. Versions installed before the update checker exists cannot detect GitHub updates by themselves.

## Channel

- Repository: https://github.com/warzou/WP-seed-content-kit
- Release asset: `wp-seed-content-kit.zip`
- Update provider: Plugin Update Checker v5.7

## Requirements

- The GitHub repository must remain public.
- Each release must include a normal GitHub Release, not a prerelease.
- Each release must attach a ZIP asset named exactly `wp-seed-content-kit.zip`.
- The ZIP must contain a single root directory named `wp-seed-content-kit/`.
- The plugin `Version` header must be higher than the installed version.

## Security

- No token is required.
- No secret is stored in the plugin.
- Silent auto-updates are not enabled.

## Validation Flow

1. Install the first update-enabled ZIP manually.
2. Publish a later GitHub Release with a higher plugin version.
3. Confirm that WordPress detects the update from the Plugins screen.
4. Run the update from WordPress admin.
5. Confirm that the plugin remains active and the shortcodes still render.

## Manual Rollback

If an update fails, install the previous `wp-seed-content-kit.zip` release manually from WordPress admin or by restoring the plugin directory from backup.
