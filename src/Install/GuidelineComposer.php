<?php

declare(strict_types=1);

namespace Yard\Brave\Boost\Install;

use Yard\Brave\Boost\Support\Paths;

/**
 * Discovers the bundled guideline fragments and concatenates them into a single
 * block. Fragments are plain .md or .blade.php files in .ai/guidelines/, rendered
 * with the given flags so theme-specific sections can be toggled.
 */
class GuidelineComposer
{
	/**
	 * @param  array<string, mixed>  $flags
	 */
	public function __construct(protected array $flags = [])
	{
	}

	public function compose(): string
	{
		$sections = [];

		foreach ($this->fragments() as $path) {
			$name = $this->sectionName($path);
			$body = trim($this->render($path));

			if ('' === $body) {
				continue;
			}

			$sections[] = "=== {$name} rules ===\n\n{$body}";
		}

		return implode("\n\n", $sections);
	}

	/**
	 * Sorted list of guideline fragment files.
	 *
	 * @return list<string>
	 */
	protected function fragments(): array
	{
		$dir = Paths::ai('guidelines');

		if (! is_dir($dir)) {
			return [];
		}

		$files = glob($dir.'/*.{md,blade.php}', GLOB_BRACE) ?: [];
		sort($files);

		return $files;
	}

	protected function render(string $path): string
	{
		if (str_ends_with($path, '.blade.php')) {
			return (string) \Illuminate\Support\Facades\View::file($path, $this->flags)->render();
		}

		return (string) file_get_contents($path);
	}

	protected function sectionName(string $path): string
	{
		$base = basename($path);
		$base = preg_replace('/\.(blade\.php|md)$/', '', $base) ?? $base;

		// Drop a leading numeric ordering prefix like "10-".
		return (string) preg_replace('/^\d+[-_]/', '', $base);
	}
}
