<?php

declare(strict_types=1);

namespace Yard\Brave\Boost\Contracts;

interface Agent
{
	/**
	 * Machine name stored in brave-boost.json (e.g. "claude_code").
	 */
	public function name(): string;

	/**
	 * Human label shown in the install prompt (e.g. "Claude Code").
	 */
	public function displayName(): string;

	/**
	 * Project-relative path the guidelines are written to (e.g. "CLAUDE.md").
	 */
	public function guidelinesPath(): string;

	/**
	 * Whether the guideline file needs YAML frontmatter prepended.
	 */
	public function frontmatter(): bool;

	/**
	 * Hints used to auto-detect this agent in a project (dirs/files at root).
	 *
	 * @return list<string>
	 */
	public function projectHints(): array;

	/**
	 * Shell binary used to detect a system-wide install (e.g. "claude"), or null.
	 */
	public function systemBinary(): ?string;
}
