<?php

declare(strict_types=1);

use Yard\Brave\Boost\Support\Config;
use Yard\Brave\Boost\Support\Paths;

beforeEach(function () {
	Paths::useProjectRoot(tmpDir());
});

it('writes the config file at the project root', function () {
	expect((new Config)->path())->toBe(Paths::project('brave-boost.json'));
});

it('persists and reloads agents and skills', function () {
	$config = new Config;
	$config->setAgents(['claude_code', 'cursor']);
	$config->setSkills(['brave-post-type']);
	$config->save();

	$reloaded = new Config;

	expect($reloaded->agents())->toBe(['claude_code', 'cursor'])
		->and($reloaded->skills())->toBe(['brave-post-type']);
});

it('returns empty arrays when no config exists', function () {
	$config = new Config;

	expect($config->agents())->toBe([])
		->and($config->skills())->toBe([]);
});
