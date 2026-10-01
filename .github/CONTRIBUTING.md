# Contributing

Contributions are **welcome** and will be fully **credited**.

Please read and understand the contribution guide before creating an issue or pull request.

## Etiquette

This project is open source, and as such, the maintainers give their free time to build and maintain the source code
held within. They make the code freely available in the hope that it will be of use to other developers. It would be
extremely unfair for them to suffer abuse or anger for their hard work.

Please be considerate towards maintainers when raising issues or presenting pull requests. Let's show the
world that developers are civilized and selfless people.

It's the duty of the maintainer to ensure that all submissions to the project are of sufficient
quality to benefit the project. Many developers have different skills, strengths, and weaknesses. Respect the maintainer's decision, and do not be upset or abusive if your submission is not used.

## Viability

When requesting or submitting new features, first consider whether it might be useful to others. Open
source projects are used by many developers, who may have entirely different needs to your own. Think about
whether or not your feature is likely to be used by other users of the project.

## Procedure

Before filing an issue:

- Attempt to replicate the problem, to ensure that it wasn't a coincidental incident.
- Check to make sure your feature suggestion isn't already present within the project.
- Check the pull requests tab to ensure that the bug doesn't have a fix in progress.
- Check the pull requests tab to ensure that the feature isn't already in progress.

Before submitting a pull request:

- Check the codebase to ensure that your feature doesn't already exist.
- Check the pull requests to ensure that another person hasn't already submitted the feature or fix.

## Requirements

If the project maintainer has any additional requirements, you will find them listed here.

- **[PSR-2 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)** - The easiest way to apply the conventions is to install [PHP Code Sniffer](https://pear.php.net/package/PHP_CodeSniffer).

- **Add tests!** - Your patch won't be accepted if it doesn't have tests.

- **Document any change in behaviour** - Make sure the `README.md` and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** - We try to follow [SemVer v2.0.0](https://semver.org/). Randomly breaking public APIs is not an option.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please [squash them](https://www.git-scm.com/book/en/v2/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages) before submitting.

## Documentation contract

The README is a **landing page**, not the manual. It must always contain, and only contain:

| README.md | docs/ |
| --- | --- |
| Requirements matrix (PHP / Laravel / Filament) | Step-by-step guides per feature |
| Install steps and plugin registration | Full Blade examples, copy-paste partials |
| One minimal end-to-end usage example | Settings key reference, config options |
| Feature list (one line per feature) | Caching and performance notes |
| Links into `docs/`, `CHANGELOG.md`, `UPGRADE.md` | Customization and extension points |

Rules:

1. **Same PR, same change.** A PR that adds, renames or removes anything public (facade method,
   Blade component, artisan command, settings key, publish tag, config key, migration) updates
   `README.md` (if it changes the feature list, install steps or the usage example) and the
   relevant `docs/` page in that PR. The PR template has a checkbox for it and the
   **Docs guard** workflow (`bin/check-docs-updated.sh`) fails PRs that touch `src/`, `config/`,
   `routes/`, migrations or Blade components without touching `README.md` or `docs/`.
   Internal-only changes (refactors, tests, CI) get the `skip-docs` label instead.
2. **README = latest tagged release.** Merged-but-untagged changes are listed under
   **Unreleased** in `CHANGELOG.md`. Tag the release as soon as a feature PR that changed the
   README is merged; until then the README header says which unreleased version it describes.
3. **No dead APIs.** When an API is renamed or removed, delete every README/docs snippet using
   it in the same PR and add the migration path to `UPGRADE.md`.
4. **Snippets are real code.** Every snippet in the README and docs must run as written; check
   them against a fresh Laravel app before tagging a release that changes them.

Run the guard locally before pushing:

```bash
bin/check-docs-updated.sh origin/main HEAD
```

## Releasing

1. Move the **Unreleased** entries in `CHANGELOG.md` under the new version heading.
2. Remove the "unreleased" note from the top of `README.md`.
3. Tag and publish the GitHub release (the Update Changelog workflow records the release notes).

**Happy coding**!
