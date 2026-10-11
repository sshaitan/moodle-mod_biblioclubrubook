# Changelog

## 2026-10-12 - v1.10

- Addressed Moodle review #2: replace direct cURL calls with Moodle's HTTP client;
  preserve TLS verification, external cookies, fingerprint and stable User-Agent.
- Correctly encode JSON form payloads (including ampersands and plus signs);
  handle malformed API responses and failed authentication without type errors.
- Addressed Moodle review #3: declare external authentication, search and reading metadata.
- Addressed Moodle review #4: move the prefixed fallback redirect helper to locallib.php.
- Addressed Moodle review #5: namespace and autoload the API class; remove global BASE_URL.
- Added offline HTTP/authentication and privacy regression tests.
- Fixed a further Moodle 5.2 regression: the book picker no longer has a hard
  dependency on the removed core/modal_factory. Use ModalCancel.create() on modern
  Moodle with a lazy legacy fallback; rebuilt both shipped AMD bundles.
- Verified book search, selection and saving through the actual Moodle form;
  added two offline modal compatibility cases.
- Require Moodle 3.5.3 and block_biblioclub_ru v0.0.7 (2026101200).
- Disabled automatic Moodle Plugins Directory submission on tag push.
- Review #1 remains pending approval of the dependent block in the Moodle Plugins Directory.

## 2026-09-14

- Added support for the redesigned Biblioclub `uinfo` fingerprint session.
- Replaced the obsolete `PHPSESSID` extraction and cookie forwarding logic.
- Restored signed organisation login, book search, access checks, metadata
  loading, and viewer-link generation against `biblioclub.ru`.
- Enabled TLS certificate verification for Biblioclub API requests.
- Verified installation and end-to-end API calls on Moodle 5.2.2 with PHP 8.4.
