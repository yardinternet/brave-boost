<?php

declare(strict_types=1);

use Yard\Brave\Boost\Contracts\Agent;
use Yard\Brave\Boost\Contracts\SupportsSkills;
use Yard\Brave\Boost\Support\Paths;
use Yard\Brave\Boost\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

uses()->beforeEach(fn () => Paths::reset())->in(__DIR__);

/**
 * Create a unique temporary directory and return its absolute path.
 */
function tmpDir(string $prefix = 'brave-boost'): string
{
	$dir = sys_get_temp_dir().'/'.$prefix.'-'.uniqid('', true);
	mkdir($dir, 0755, true);

	return $dir;
}

/**
 * A minimal Agent for guideline tests.
 */
function fakeAgent(string $path = 'AGENT.md', bool $frontmatter = false): Agent
{
	return new class($path, $frontmatter) implements Agent {
		public function __construct(private string $path, private bool $frontmatter)
		{
		}

		public function name(): string
		{
			return 'fake';
		}

		public function displayName(): string
		{
			return 'Fake Agent';
		}

		public function guidelinesPath(): string
		{
			return $this->path;
		}

		public function frontmatter(): bool
		{
			return $this->frontmatter;
		}

		public function projectHints(): array
		{
			return [];
		}

		public function systemBinary(): ?string
		{
			return null;
		}
	};
}

/**
 * A minimal skill-capable Agent for skill tests.
 */
function fakeSkillAgent(string $skillsPath = '.skills'): Agent
{
	return new class($skillsPath) implements Agent, SupportsSkills {
		public function __construct(private string $skillsPath)
		{
		}

		public function name(): string
		{
			return 'fake_skills';
		}

		public function displayName(): string
		{
			return 'Fake Skill Agent';
		}

		public function guidelinesPath(): string
		{
			return 'FAKE.md';
		}

		public function frontmatter(): bool
		{
			return false;
		}

		public function projectHints(): array
		{
			return [];
		}

		public function systemBinary(): ?string
		{
			return null;
		}

		public function skillsPath(): string
		{
			return $this->skillsPath;
		}
	};
}
