<?php

declare(strict_types=1);

namespace Yard\Brave\Boost\Agents;

use Yard\Brave\Boost\Contracts\Agent;

class Cursor implements Agent
{
	public function name(): string
	{
		return 'cursor';
	}

	public function displayName(): string
	{
		return 'Cursor';
	}

	public function guidelinesPath(): string
	{
		return '.cursor/rules/brave-boost.mdc';
	}

	public function frontmatter(): bool
	{
		return true;
	}

	public function projectHints(): array
	{
		return ['.cursor'];
	}

	public function systemBinary(): ?string
	{
		return 'cursor';
	}
}
