# Changelog

## 2026-09-14

- Added support for the redesigned Biblioclub `uinfo` fingerprint session.
- Replaced the obsolete `PHPSESSID` extraction and cookie forwarding logic.
- Restored signed organisation login, book search, access checks, metadata
  loading, and viewer-link generation against `biblioclub.ru`.
- Enabled TLS certificate verification for Biblioclub API requests.
- Verified installation and end-to-end API calls on Moodle 5.2.2 with PHP 8.4.
