# Brave Boost

[![Code Style](https://github.com/yardinternet/brave-boost/actions/workflows/format-php.yml/badge.svg?no-cache)](https://github.com/yardinternet/brave-boost/actions/workflows/format-php.yml)
[![PHPStan](https://github.com/yardinternet/brave-boost/actions/workflows/phpstan.yml/badge.svg?no-cache)](https://github.com/yardinternet/brave-boost/actions/workflows/phpstan.yml)
[![Tests](https://github.com/yardinternet/brave-boost/actions/workflows/run-tests.yml/badge.svg?no-cache)](https://github.com/yardinternet/brave-boost/actions/workflows/run-tests.yml)
[![Code Coverage Badge](https://github.com/yardinternet/brave-boost/blob/badges/coverage.svg)](https://github.com/yardinternet/brave-boost/actions/workflows/badges.yml)
[![Lines of Code Badge](https://github.com/yardinternet/brave-boost/blob/badges/lines-of-code.svg)](https://github.com/yardinternet/brave-boost/actions/workflows/badges.yml)



Brave Boost installs Brave AI guidelines and skills into the coding agents you
use (Claude Code, Cursor, GitHub Copilot), so they follow Brave ecosystem
conventions in your project.

## Requirements

- [Sage](https://github.com/roots/sage) >= 10.0
- [Acorn](https://github.com/roots/acorn) >= 4.0

## Installation

1. Add the following to the `repositories` section of your `composer.json`:

    ```json
    {
      "type": "vcs",
      "url": "git@github.com:yardinternet/brave-boost.git"
    }
    ```

2. Install with Composer:

    ```sh
    composer require yard/brave-boost
    ```

3. Discover the package with Acorn:

    ```shell
    wp acorn package:discover
    ```

## Usage

Run the interactive installer and pick your agents:

```shell
wp acorn brave-boost:install
```

Re-apply guidelines and skills non-interactively (e.g. after `composer update`),
reusing the choices saved in `brave-boost.json`:

```shell
wp acorn brave-boost:update
```

### What gets written

| Agent          | Guidelines                          | Skills            |
| -------------- | ----------------------------------- | ----------------- |
| Claude Code    | `CLAUDE.md`                         | `.claude/skills/` |
| Cursor         | `.cursor/rules/brave-boost.mdc`     | —                 |
| GitHub Copilot | `.github/copilot-instructions.md`   | —                 |

Guidelines are written inside `<brave-boost-guidelines>` markers — any content
you add outside the markers is preserved on update. Commit the generated files;
they are shared project context, not local config.

### Configuration

Publish the config to tweak behaviour:

```shell
wp acorn vendor:publish --provider="Yard\Brave\Boost\BoostServiceProvider"
```

- `project_root` — where files are written. Defaults to the nearest `.git`
  ancestor (Acorn's `base_path()` points at the theme, not the repo root).
  Override per-run with `--path`.
- `skills.exclude` — skill names to skip when installing.

## About us

[![banner](https://raw.githubusercontent.com/yardinternet/.github/refs/heads/main/profile/assets/small-banner-github.svg)](https://www.yard.nl/werken-bij/)
