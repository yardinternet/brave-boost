<?php

declare(strict_types=1);

namespace Yard\Brave\Boost\Install;

use Yard\Brave\Boost\Support\Paths;

/**
 * Inspects the project root and returns flags consumed by the guideline Blade
 * fragments (e.g. {@see GuidelineComposer}). Add a key here, then guard a
 * section with `@if (! empty($key))` in a .blade.php guideline.
 *
 * @return array<string, bool>
 */
class ProjectFlags
{
	/**
	 * @return array<string, bool>
	 */
	public static function detect(): array
	{
		return [
			'hasVite' => self::exists(['vite.config.js', 'vite.config.ts', 'vite.config.mjs']),
		];
	}

	/**
	 * @param  list<string>  $candidates
	 */
	protected static function exists(array $candidates): bool
	{
		foreach ($candidates as $file) {
			if (file_exists(Paths::project($file))) {
				return true;
			}
		}

		return false;
	}
}
