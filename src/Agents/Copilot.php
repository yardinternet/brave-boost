<?php

declare(strict_types=1);

namespace Yard\Brave\Boost\Agents;

use Yard\Brave\Boost\Contracts\Agent;

class Copilot implements Agent
{
	public function name(): string
	{
		return 'copilot';
	}

	public function displayName(): string
	{
		return 'GitHub Copilot';
	}

	public function guidelinesPath(): string
	{
		return '.github/copilot-instructions.md';
	}

	public function frontmatter(): bool
	{
		return false;
	}

	public function projectHints(): array
	{
		return ['.github/copilot-instructions.md'];
	}

	public function systemBinary(): ?string
	{
		return null;
	}
}
