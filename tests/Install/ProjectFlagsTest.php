<?php

declare(strict_types=1);

use Yard\Brave\Boost\Install\ProjectFlags;
use Yard\Brave\Boost\Support\Paths;

beforeEach(function () {
	Paths::useProjectRoot(tmpDir());
});

it('flags hasVite false when no vite config is present', function () {
	expect(ProjectFlags::detect()['hasVite'])->toBeFalse();
});

it('flags hasVite true when a vite config exists at the project root', function () {
	file_put_contents(Paths::project('vite.config.js'), 'export default {}');

	expect(ProjectFlags::detect()['hasVite'])->toBeTrue();
});
