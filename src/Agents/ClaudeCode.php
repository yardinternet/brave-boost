<?php

declare(strict_types=1);

namespace Yard\Brave\Boost\Agents;

use Yard\Brave\Boost\Contracts\Agent;
use Yard\Brave\Boost\Contracts\SupportsSkills;

class ClaudeCode implements Agent, SupportsSkills
{
	public function name(): string
	{
		return 'claude_code';
	}

	public function displayName(): string
	{
		return 'Claude Code';
	}

	public function guidelinesPath(): string
	{
		return 'CLAUDE.md';
	}

	public function frontmatter(): bool
	{
		return false;
	}

	public function projectHints(): array
	{
		return ['.claude', 'CLAUDE.md'];
	}

	public function systemBinary(): ?string
	{
		return 'claude';
	}

	public function skillsPath(): string
	{
		return '.claude/skills';
	}
}
