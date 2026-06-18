# Brave Boost

[![Code Style](https://github.com/yardinternet/brave-boost/actions/workflows/format-php.yml/badge.svg?no-cache)](https://github.com/yardinternet/brave-boost/actions/workflows/format-php.yml)
[![PHPStan](https://github.com/yardinternet/brave-boost/actions/workflows/phpstan.yml/badge.svg?no-cache)](https://github.com/yardinternet/brave-boost/actions/workflows/phpstan.yml)
[![Tests](https://github.com/yardinternet/brave-boost/actions/workflows/run-tests.yml/badge.svg?no-cache)](https://github.com/yardinternet/brave-boost/actions/workflows/run-tests.yml)
[![Code Coverage Badge](https://github.com/yardinternet/brave-boost/blob/badges/coverage.svg)](https://github.com/yardinternet/brave-boost/actions/workflows/badges.yml)
[![Lines of Code Badge](https://github.com/yardinternet/brave-boost/blob/badges/lines-of-code.svg)](https://github.com/yardinternet/brave-boost/actions/workflows/badges.yml)

Brave Boost installs Brave AI guidelines and skills into the coding agents you use (Claude Code, Cursor, GitHub Copilot), so they follow Brave ecosystem conventions in your project.

## Installation

1. Add to the `repositories` section of `composer.json`:

    ```json
    {
      "type": "vcs",
      "url": "git@github.com:yardinternet/brave-boost.git"
    }
    ```

2. Install:

    ```sh
    composer require yard/brave-boost
    ```

3. Discover the package:

    ```shell
    wp acorn package:discover
    ```

## Usage

```shell
wp acorn boost:install
```

Writes guidelines and skills for all supported agents. Re-run any time to update (e.g. after `composer update`).

### Options

| Flag | Effect |
| --- | --- |
| `--no-guidelines` | Skip writing AI guidelines |
| `--no-skills` | Skip installing skills |
| `--path=` | Override project root (defaults to git root) |

### What gets written

| Agent | Guidelines | Skills |
| --- | --- | --- |
| Claude Code | `CLAUDE.md` | `.claude/skills/` |
| Cursor | `.cursor/rules/brave-boost.mdc` | — |
| GitHub Copilot | `.github/copilot-instructions.md` | — |

Guidelines are written inside `<brave-boost-guidelines>` markers — content outside the markers is preserved. Add these files to `.gitignore`:

```gitignore
CLAUDE.md
.cursor/rules/brave-boost.mdc
.github/copilot-instructions.md
.claude/skills/brave-*/
```

### Configuration

```shell
wp acorn vendor:publish --provider="Yard\Brave\Boost\BoostServiceProvider"
```

- `project_root` — override where files are written. Defaults to the nearest `.git` ancestor.
- `skills.exclude` — skill names to skip.

## About us

[![banner](https://raw.githubusercontent.com/yardinternet/.github/refs/heads/main/profile/assets/small-banner-github.svg)](https://www.yard.nl/werken-bij/)
