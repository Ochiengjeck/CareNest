# Error - RESOLVED

The "Flux component [icon.external-link] does not exist" error has been fixed.

**Issue**: The icon name `external-link` doesn't exist in Flux UI (which uses Heroicons).

**Fix**: Changed `icon="external-link"` to `icon="arrow-top-right-on-square"` in `⚡show.blade.php` line 173.
