<?php

declare(strict_types=1);

return [

	/*
	|--------------------------------------------------------------------------
	| Project root
	|--------------------------------------------------------------------------
	|
	| Where agent files (CLAUDE.md, .cursor/, .github/, .claude/skills/) are
	| written. Acorn's base_path() points at the theme, not the repo root, so
	| leave this null to auto-detect the nearest .git ancestor, or set an
	| absolute path. Overridable per-run with the --path option.
	|
	*/

	'project_root' => null,

	/*
	|--------------------------------------------------------------------------
	| Skills
	|--------------------------------------------------------------------------
	|
	| Bundled skills are installed into skill-capable agents. List skill names
	| here to exclude them from installation.
	|
	*/

	'skills' => [
		'exclude' => [],
	],
];
