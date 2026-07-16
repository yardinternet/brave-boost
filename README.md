# Brave Boost

[![Code Style](https://github.com/yardinternet/brave-boost/actions/workflows/format-php.yml/badge.svg?no-cache)](https://github.com/yardinternet/brave-boost/actions/workflows/format-php.yml)
[![PHPStan](https://github.com/yardinternet/brave-boost/actions/workflows/phpstan.yml/badge.svg?no-cache)](https://github.com/yardinternet/brave-boost/actions/workflows/phpstan.yml)
[![Tests](https://github.com/yardinternet/brave-boost/actions/workflows/run-tests.yml/badge.svg?no-cache)](https://github.com/yardinternet/brave-boost/actions/workflows/run-tests.yml)
[![Code Coverage Badge](https://github.com/yardinternet/brave-boost/blob/badges/coverage.svg)](https://github.com/yardinternet/brave-boost/actions/workflows/badges.yml)
[![Lines of Code Badge](https://github.com/yardinternet/brave-boost/blob/badges/lines-of-code.svg)](https://github.com/yardinternet/brave-boost/actions/workflows/badges.yml)

Brave Boost installs Brave AI guidelines and skills into the coding agents you use (Claude Code, Cursor, GitHub Copilot), so they follow Brave ecosystem conventions in your project.

## Installation

1. Install:

    ```sh
    composer require yard/brave-boost
    ```

2. Discover the package:

    ```shell
    wp acorn package:discover
    ```

## Usage

1. Run the install command:

```shell
wp acorn boost:install
```

2. Add the generated files to the `.gitignore` of your project.

```gitignore
CLAUDE.md
.cursor/rules/brave-boost.mdc
.github/copilot-instructions.md
.claude/skills/brave-*/
```

## What gets written

| Agent | Guidelines | Skills |
| --- | --- | --- |
| Claude Code | `CLAUDE.md` | `.claude/skills/` |
| Cursor | `.cursor/rules/brave-boost.mdc` | — |
| GitHub Copilot | `.github/copilot-instructions.md` | — |

Guidelines are written inside `<brave-boost-guidelines>` markers — content outside the markers is preserved. 

## Configuration

```shell
wp acorn vendor:publish --provider="Yard\Brave\Boost\BoostServiceProvider"
```

- `project_root` — override where files are written. Defaults to the nearest `.git` ancestor.
- `skills.exclude` — skill names to skip.

## About us

[![banner](https://raw.githubusercontent.com/yardinternet/.github/refs/heads/main/profile/assets/small-banner-github.svg)](https://www.yard.nl/werken-bij/)
