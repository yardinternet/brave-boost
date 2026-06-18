<?php

declare(strict_types=1);

namespace Yard\Brave\Boost\Install;

use FilesystemIterator;
use Illuminate\Support\Collection;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Yard\Brave\Boost\Contracts\SupportsSkills;

/**
 * Copies bundled skills into an agent's skills directory.
 */
class SkillWriter
{
	public function __construct(protected SupportsSkills $agent)
	{
	}

	/**
	 * @param  Collection<int, Skill>  $skills
	 *
	 * @return list<string>  Names installed.
	 */
	public function sync(Collection $skills): array
	{
		$base = $this->basePath();
		$this->ensureDirectory($base);

		$installed = [];

		foreach ($skills as $skill) {
			$this->copyDir($skill->sourceDir, $base.'/'.$skill->name);
			$installed[] = $skill->name;
		}

		return $installed;
	}

	protected function basePath(): string
	{
		return \Yard\Brave\Boost\Support\Paths::project($this->agent->skillsPath());
	}

	protected function copyDir(string $source, string $target): void
	{
		$this->deleteDir($target);
		$this->ensureDirectory($target);

		/** @var RecursiveIteratorIterator<RecursiveDirectoryIterator> $iterator */
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ($iterator as $item) {
			/** @var SplFileInfo $item */
			/** @var RecursiveDirectoryIterator $inner */
			$inner = $iterator->getInnerIterator();
			$dest = $target.'/'.$inner->getSubPathname();

			if ($item->isDir()) {
				$this->ensureDirectory($dest);

				continue;
			}

			if (! copy($item->getPathname(), $dest)) {
				throw new RuntimeException("Failed to copy {$item->getPathname()} to {$dest}");
			}
		}
	}

	protected function ensureDirectory(string $dir): void
	{
		if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
			throw new RuntimeException("Failed to create directory {$dir}");
		}
	}

	protected function deleteDir(string $dir): void
	{
		if (! is_dir($dir)) {
			return;
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($iterator as $item) {
			/** @var SplFileInfo $item */
			$item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
		}

		rmdir($dir);
	}
}
