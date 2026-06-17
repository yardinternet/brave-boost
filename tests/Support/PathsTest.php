<?php

declare(strict_types=1);

use Yard\Brave\Boost\Support\Paths;

it('honours an explicit project root override', function () {
	$root = tmpDir();
	Paths::useProjectRoot($root);

	expect(Paths::projectRoot())->toBe($root)
		->and(Paths::project('CLAUDE.md'))->toBe($root.'/CLAUDE.md');
});

it('detects the nearest .git ancestor', function () {
	$root = tmpDir();
	mkdir($root.'/.git');
	mkdir($root.'/nested/deep', 0755, true);

	$reflect = new ReflectionMethod(Paths::class, 'findGitRoot');
	$reflect->setAccessible(true);

	expect($reflect->invoke(null, $root.'/nested/deep'))->toBe($root);
});

it('points at the bundled .ai directory', function () {
	expect(is_dir(Paths::ai()))->toBeTrue()
		->and(is_dir(Paths::ai('skills')))->toBeTrue()
		->and(is_dir(Paths::ai('guidelines')))->toBeTrue();
});
