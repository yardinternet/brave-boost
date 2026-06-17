<?php

declare(strict_types=1);

namespace Yard\Brave\Boost\Support;

class Paths
{
	/**
	 * Explicit override (e.g. from a --path option). Takes precedence over detection.
	 */
	protected static ?string $projectRoot = null;

	public static function useProjectRoot(string $path): void
	{
		static::$projectRoot = rtrim($path, '/');
	}

	/**
	 * Clear the explicit override (mainly for tests).
	 */
	public static function reset(): void
	{
		static::$projectRoot = null;
	}

	/**
	 * Absolute path to the project root where agent files are written.
	 *
	 * Acorn's base_path() points at the theme, not the repo root, so we resolve
	 * the root ourselves: explicit override → config → nearest .git ancestor → cwd.
	 */
	public static function projectRoot(): string
	{
		if (null !== static::$projectRoot) {
			return static::$projectRoot;
		}

		$configured = config('brave-boost.project_root');
		if (is_string($configured) && '' !== $configured) {
			return rtrim($configured, '/');
		}

		return static::findGitRoot(getcwd() ?: '.') ?? (getcwd() ?: '.');
	}

	/**
	 * Join a project-relative path onto the project root.
	 */
	public static function project(string $relative): string
	{
		return static::projectRoot().'/'.ltrim($relative, '/');
	}

	/**
	 * Absolute path to this package's root (where .ai/ lives).
	 */
	public static function packageRoot(): string
	{
		return dirname(__DIR__, 2);
	}

	/**
	 * Absolute path inside the package's bundled .ai/ directory.
	 */
	public static function ai(string $relative = ''): string
	{
		return static::packageRoot().'/.ai'.('' === $relative ? '' : '/'.ltrim($relative, '/'));
	}

	protected static function findGitRoot(string $start): ?string
	{
		$dir = rtrim($start, '/');

		while ('' !== $dir && '/' !== $dir) {
			if (is_dir($dir.'/.git')) {
				return $dir;
			}
			$parent = dirname($dir);
			if ($parent === $dir) {
				break;
			}
			$dir = $parent;
		}

		return null;
	}
}
