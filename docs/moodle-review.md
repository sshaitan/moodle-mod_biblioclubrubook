# Moodle Plugins Directory review follow-up — v1.11

Version: `2026101201`, release `1.11`, tag `v1.11` (12 October 2026).
Requires Moodle 3.5.3 (`2018051703`) and block_biblioclub_ru v0.0.7 (`2026101200`).
Install/update the block first, then update the module and run Moodle's upgrade.
Existing organisation settings and course book resources are retained.

## Automated directory validation follow-up

The submitted v1.10 ZIP failed `moodle-plugin-ci validate`: its English language
file did not contain `biblioclubrubook:addinstance`, although the capability was
declared in `db/access.php`. The failure was reproduced with the official Moodle
Plugin CI 4.5.11 validator (exit 1). Release v1.11 supplies English and Russian
names for both `addinstance` and `view`; the same validator passes (exit 0).
A regression test checks every declared capability against both language files.
The combined plugin PHPUnit suite was rerun on Moodle 5.2.2 / PHP 8.4.25 /
PostgreSQL 16.13: **19 tests / 91 assertions**, all passing, without warnings.
The module upgrade, lint of all 28 PHP files and both offline JS cases also pass.
Live EBS/browser checks listed below were performed for v1.10; v1.11 does not
change authentication, HTTP or book-picker logic and made no new EBS requests.

The new `.github/workflows/validate.yml` runs that structure check on pushes and
pull requests. It uses a pinned Moodle 5.2.2 checkout and a static-validation-only
config fixture: no database installation, EBS requests or credentials are needed.
The fixture deliberately refuses normal Moodle bootstrap. This check does not
replace runtime tests, all directory checks or moderator approval.

To repeat against a development Moodle checkout and a downloaded official PHAR:

```bash
php /path/to/moodle-plugin-ci.phar validate --moodle=/path/to/moodle /path/to/biblioclubrubook
```

See the [official validator documentation](https://moodlehq.github.io/moodle-plugin-ci/CLI.html#validate).

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

> Please review mod_biblioclubrubook v1.11 together with block_biblioclub_ru v0.0.7.
> The v1.10 automated validation failure (missing biblioclubrubook:addinstance
> English string) is fixed. Both declared capabilities now have English and
> Russian descriptions, covered by a regression test. The official
> moodle-plugin-ci 4.5.11 validate command passes locally.
> Module findings #2–#5 have been addressed: Moodle curl, external-data Privacy API,
> the prefixed locallib handler and the namespaced/autoloaded API class.
> Automated tests and live verification are documented in the repository.
> Finding #1 remains an approval prerequisite, not a removed dependency: the block
> must be approved first. The module now requires the fixed block v0.0.7.

GitHub releases do not indicate Moodle Directory approval. Tag pushes no longer
submit to Moodle.org automatically; the repository owner handles the submission.
