# Vendored package

- Source: https://github.com/kalimulhaq/qubuilder
- Version: v1.3.1 (commit 7db75cec669dbda44f1f632d84e06303c7cb6f24)
- Vendored: 2026-08-25

This is a plain vendored copy (no git submodule, no `.git` history) checked
into `packages/kalimulhaq/qubuilder` and consumed via a Composer `path`
repository in the root `composer.json`. A `"version"` key was added to this
package's `composer.json` since Composer's path-repository loader cannot
infer a version without a `.git` directory.

To upgrade: re-clone the desired tag from the source above, diff against this
directory, and update the `version` key and this file accordingly.
