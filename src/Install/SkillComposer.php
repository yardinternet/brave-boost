<?php

declare(strict_types=1);

namespace Yard\Brave\Boost\Install;

use Illuminate\Support\Collection;
use Yard\Brave\Boost\Support\Paths;

/**
 * Discovers the skills bundled in .ai/skills/. Each skill is a directory holding
 * a SKILL.md with YAML frontmatter (name, description).
 */
class SkillComposer
{
	/**
	 * @return Collection<int, Skill>
	 */
	public function all(): Collection
	{
		$dir = Paths::ai('skills');

		if (! is_dir($dir)) {
			return collect();
		}

		$exclude = (array) config('brave-boost.skills.exclude', []);

		return collect(glob($dir.'/*/SKILL.md') ?: [])
			->map(fn (string $file) => $this->parse($file))
			->filter()
			->reject(fn (Skill $skill) => in_array($skill->name, $exclude, true))
			->values();
	}

	protected function parse(string $skillFile): ?Skill
	{
		$sourceDir = dirname($skillFile);
		$frontmatter = $this->frontmatter((string) file_get_contents($skillFile));

		$name = $frontmatter['name'] ?? basename($sourceDir);
		$description = trim((string) ($frontmatter['description'] ?? ''));

		return new Skill((string) $name, $description, $sourceDir);
	}

	/**
	 * Tolerant frontmatter parse for the top-level scalar keys we care about.
	 *
	 * Avoids a strict YAML parser because skill descriptions routinely contain
	 * unquoted colons and em-dashes. Supports inline values and folded/literal
	 * blocks (`key: >` / `key: |`).
	 *
	 * @return array<string, string>
	 */
	protected function frontmatter(string $contents): array
	{
		if (! preg_match('/^---\s*\n(.*?)\n---\s*\n/s', $contents, $m)) {
			return [];
		}

		$lines = explode("\n", $m[1]);
		$result = [];
		$count = count($lines);

		for ($i = 0; $i < $count; $i++) {
			if (! preg_match('/^([A-Za-z0-9_-]+):\s?(.*)$/', $lines[$i], $kv)) {
				continue;
			}

			[$key, $value] = [$kv[1], trim($kv[2])];

			// Folded (>) or literal (|) block: gather the indented lines below.
			if ('>' === $value || '|' === $value || '>-' === $value || '|-' === $value) {
				$block = [];
				while ($i + 1 < $count && ($lines[$i + 1] === '' || preg_match('/^\s+/', $lines[$i + 1]))) {
					$block[] = trim($lines[++$i]);
				}
				$value = trim(implode(' ', $block));
			}

			$result[$key] = trim($value, "\"'");
		}

		return $result;
	}
}
