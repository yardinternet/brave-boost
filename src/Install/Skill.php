<?php

declare(strict_types=1);

namespace Yard\Brave\Boost\Install;

class Skill
{
	public function __construct(
		public readonly string $name,
		public readonly string $description,
		public readonly string $sourceDir,
	) {
	}
}
