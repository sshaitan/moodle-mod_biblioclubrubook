# Moodle Plugins Directory review follow-up — v1.10

Version: `2026101200`, release `1.10`, tag `v1.10` (12 October 2026).
Requires Moodle 3.5.3 (`2018051703`) and block_biblioclub_ru v0.0.7 (`2026101200`).
Install/update the block first, then update the module and run Moodle's upgrade.
Existing organisation settings and course book resources are retained.

## Findings addressed

| Issue | Implementation | Verification |
| --- | --- | --- |
| [#2](https://github.com/sshaitan/moodle-mod_biblioclubrubook/issues/2) | Moodle `curl` replaces direct cURL calls; Moodle proxy and URL security policy apply. Custom headers use `setHeader()`; TLS verification is enabled. JSON form data is correctly encoded. | Offline request/session tests, blocked-host policy test and live API smoke. |
| [#3](https://github.com/sshaitan/moodle-mod_biblioclubrubook/issues/3) | External-location metadata lists actual identity, session, search and reading data. Request methods correctly return no locally stored per-user activity data. | Metadata/compliance tests; requests leave shared course book resources intact; core Privacy API checks. |
| [#4](https://github.com/sshaitan/moodle-mod_biblioclubrubook/issues/4) | Fallback handler moved out of `view.php` into `locallib.php` as `biblioclubrubook_error_redirect_handler()`. | PHP lint and updated call sites; no unprefixed handler remains. |
| [#5](https://github.com/sshaitan/moodle-mod_biblioclubrubook/issues/5) | `mod_biblioclubrubook\ub_api` uses the Moodle component namespace/autoloader. `BASE_URL` is class-scoped. | Autoload test, search/view callers updated and live API smoke. |

## Remaining approval prerequisite

[#1](https://github.com/sshaitan/moodle-mod_biblioclubrubook/issues/1) is **not a
code defect fixed by this release**: the dependent block must first be approved
in the Moodle Plugins Directory. Keep this issue open until that condition is met.
Submit the two plugins together and explicitly link the block's submission.
Removing the dependency would break access to organisation domain/secret settings.

## Privacy boundary

The module's table contains shared course resources, not per-user reading history.
Authentication cookies are cached temporarily in the Moodle session. The external
service receives identity, session, search, book and page-range data, declared in
Privacy API metadata. Moodle privacy requests cannot export/delete data at the
external service; requests about that data must be directed to Biblioclub.
English and Russian privacy descriptions are supplied in the plugin and README.

## Verification

- Moodle 5.2.2, PHP 8.4.25, PostgreSQL 16; both plugin upgrades and PHP lint passed.
- Combined plugin suite: 18 tests covering rendering, scoped privacy operations,
  session reuse/expiry, headers, TLS, form encoding, response handling and URL policy.
- Moodle core privacy provider and deleted-user checks passed for both components.
- Live external API smoke with the existing test identity: organisation login,
  user identification, search (30 publications), book fields and reader links passed.
- Browser seamless login to the new storefront passed, including reload persistence.
- Actual book-picker UI: search returned 30 publications, a book was selected and
  the activity was saved in the local course. The original bundle failed to load
  on Moodle 5.2 because core/modal_factory is removed. The fixed source uses
  ModalCancel.create() with a lazy factory fallback for older versions; both
  shipped AMD bundles have been rebuilt.
- Opening the saved activity reached the actual external reader (`book_view_strip`,
  HTTP 200) without using the fallback redirect.
- Two additional offline JavaScript cases check modern/legacy modal creation and
  verify that neither shipped AMD bundle requires the old factory upfront:
  `node tests/js/modal_compatibility_test.cjs`.
- The minimum Moodle version is an API requirement; only the stated runtime was tested.

For reproducible automated commands see the
[block review notes](https://github.com/sshaitan/moodle-block_biblioclub_ru/blob/master/docs/moodle-review.md).
Unit tests make no external HTTP calls and need no organisation secret.

The existing Webpack build uses the locked dependencies in `vue/package-lock.json`;
the shipped bundles were rebuilt with Node 16.20.2: `cd vue && npm ci --ignore-scripts
&& npm run prod`. Build dependencies are development-only, not installed in Moodle.

## Suggested resubmission message

> Please review mod_biblioclubrubook v1.10 together with block_biblioclub_ru v0.0.7.
> Module findings #2–#5 have been addressed: Moodle curl, external-data Privacy API,
> the prefixed locallib handler and the namespaced/autoloaded API class.
> Automated tests and live verification are documented in the repository.
> Finding #1 remains an approval prerequisite, not a removed dependency: the block
> must be approved first. The module now requires the fixed block v0.0.7.

GitHub releases do not indicate Moodle Directory approval. Tag pushes no longer
submit to Moodle.org automatically; the repository owner handles the submission.
