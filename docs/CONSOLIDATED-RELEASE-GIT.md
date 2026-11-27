# Source and publication plan

## Start here for 0.9.0 and later

Official source: [warzou/WP-seed-content-kit](https://github.com/warzou/WP-seed-content-kit),
branch `main`. Latest stable at this checkpoint: tag `v0.9.0`; resolve the
exact reference commit with `git rev-parse 'v0.9.0^{commit}'`. Check the
official tags and release list on later resumes rather than treating this
checkpoint as permanently current.

1. In a clean checkout of the official remote, use `git fetch --tags origin`
   and `git switch -c codex/<task> origin/main`. Preserve existing worktrees.
2. See `plugin/docs/TESTING.md`. Run the focused `php tests/<module>-harness.php`
   and `node tests/<module>-harness.js` tests, PHP lint and JS syntax, and
   `git diff --check`. For Directory, run the WordPress L3/L4 harnesses
   with `WP_SEED_WORDPRESS_LOAD` pointing to a disposable installation;
   run Formation concurrency as described below. Never use production fixtures.
3. Commit only approved source, documentation and permanent tests. Integrate
   them into official `main` through a reviewed PR. A validated change is not
   considered complete until it is part of this cumulative Git reference.
4. With explicit publication authorization, build from a clean export of
   the exact release commit: `python tools/build-release-zip.py --output dist/wp-seed-content-kit.zip`.
   Validate the per-file manifest, exact ZIP SHA-256 and two independent
   builds from that commit using the same Python/zlib version.
5. Rehearse the native WordPress upgrade in isolation, including the updater
   and the auto-update path for existing opt-in sites. Tag the tested commit
   and publish the stable GitHub release with the canonical
   `wp-seed-content-kit.zip` asset. Verify its downloaded SHA-256 and tag
   target. Never attach private review archives, DEV configuration or data.

## Historical 0.9.0 candidate recovery plan

- Designated branch: `codex/consolidated-release`, base `v0.8.1` at
  `889afc0e53200ae96fe187304851551f341b1632`. Before approval, HEAD is still the
  base and the cumulative changes are unstaged. The branch name alone does not
  identify the candidate. Use `cumulative-source.patch`, `git-stage-paths.txt`
  and `manifest.json` in the review archive to recover the precise state.
- Recover into a clean checkout of the exact base with LF text:
  `git -c core.autocrlf=input apply --check cumulative-source.patch`, then
  `git -c core.autocrlf=input apply cumulative-source.patch`. The build tool
  requires Python 3.8+:
  `python tools/build-release-zip.py --output dist/wp-seed-content-kit.zip`.
  Compare the file manifest and ZIP SHA-256 with the review archive. Use the
  same Python/zlib versions recorded in `rebuild.json` for the byte-identical
  ZIP check across machines.
- After HUMAN PASS and explicit publication authorization only, stage the
  exact allowlist `git-stage-paths.txt` from the archive (one path per line,
  `git add --pathspec-from-file=git-stage-paths.txt`). Review `git diff --cached
  --check` and the entire staged diff. Record the consolidated commit on the
  reference branch. Do not stage the old untracked experimental reports,
  backups, disposable WordPress databases, credentials, outputs or site presets.
- Compare the approved commit with fetched `origin/main` and `v0.8.1` before
  deciding the merge strategy. Use a normal PR with an explicit ancestry
  review. Do not force-push or replace main. Retest and rebuild from the
  resulting tree. If the approved runtime bytes change, issue a distinct RC
  and obtain review of the new ZIP rather than replacing the existing RC.
- Following approval of the stable version bump, rebuild from the exact stable
  commit, tag it as `v0.9.0` and follow the existing GitHub release runbook.
  The updater asset is named `wp-seed-content-kit.zip`. Verify asset SHA-256
  and tag target after upload. The RC review archive is not the updater asset.

## Permanent MariaDB concurrency test

In a disposable loopback-only WordPress with the candidate installed,
Formation enabled and a fixture administrator, set
`WP_SEED_WORDPRESS_LOAD` to its `wp-load.php` and
`WPSCK_FORMATION_CONCURRENCY=disposable-local`. Run
`php tests/wordpress-formation-concurrency-harness.php` (runner PHP 7.4+).
It launches two processes, verifies distinct MySQL connections and observes
the second waiting in `GET_LOCK` while the first owns the lock. It exercises
exact duplicates, distinct dates, updates, current-ID exclusion and lock
release. It cleans up its own fixtures. Never run it on DEV or production.
