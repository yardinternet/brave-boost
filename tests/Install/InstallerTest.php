<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Yard\Brave\Boost\Install\Installer;
use Yard\Brave\Boost\Install\SkillComposer;
use Yard\Brave\Boost\Support\Paths;

beforeEach(function () {
	Paths::useProjectRoot(tmpDir());
	$this->installer = new Installer(new SkillComposer);
});

it('writes guidelines for each given agent', function () {
	$results = $this->installer->installGuidelines(new Collection([fakeAgent('AGENT.md')]));

	expect($results)->toBe(['Fake Agent' => 'ok'])
		->and(file_get_contents(Paths::project('AGENT.md')))->toContain('<brave-boost-guidelines>');
});

it('installs skills only for skill-capable agents', function () {
	$agents = new Collection([fakeAgent('A.md'), fakeSkillAgent('.claude/skills')]);

	$result = $this->installer->installSkills($agents);

	expect($result['installed'])->toContain('brave-post-type')
		->and($result['results'])->toHaveKey('Fake Skill Agent')
		->and($result['results'])->not->toHaveKey('Fake Agent')
		->and(is_dir(Paths::project('.claude/skills/brave-post-type')))->toBeTrue();
});
