<?php

declare(strict_types=1);

namespace Yard\Brave\Boost\Contracts;

interface SupportsSkills
{
	/**
	 * Project-relative directory skills are synced into (e.g. ".claude/skills").
	 */
	public function skillsPath(): string;
}
