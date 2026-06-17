<?php

declare(strict_types=1);

use Yard\Brave\Boost\Agents\Cursor;
use Yard\Brave\Boost\Contracts\Agent;
use Yard\Brave\Boost\Install\AgentDetector;
use Yard\Brave\Boost\Support\Paths;

it('knows all three agents', function () {
	$names = (new AgentDetector)->all()->map(fn (Agent $a) => $a->name())->all();

	expect($names)->toBe(['claude_code', 'cursor', 'copilot']);
});

it('resolves agents by name', function () {
	$agents = (new AgentDetector)->byNames(['cursor']);

	expect($agents)->toHaveCount(1)
		->and($agents->first())->toBeInstanceOf(Cursor::class);
});

it('detects an agent from a project hint directory', function () {
	Paths::useProjectRoot(tmpDir());
	mkdir(Paths::project('.claude'));

	expect((new AgentDetector)->detected())->toContain('claude_code');
});
