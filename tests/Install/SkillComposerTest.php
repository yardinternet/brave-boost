<?php

declare(strict_types=1);

use Yard\Brave\Boost\Install\Skill;
use Yard\Brave\Boost\Install\SkillComposer;

it('discovers the bundled brave skills', function () {
	$skills = (new SkillComposer)->all();

	$names = $skills->map(fn (Skill $s) => $s->name)->all();

	expect($names)->toContain('brave-post-type', 'brave-gutenberg-block')
		->and($skills->every(fn (Skill $s) => is_dir($s->sourceDir)))->toBeTrue();
});

it('respects the skills.exclude config', function () {
	config()->set('brave-boost.skills.exclude', ['brave-post-type']);

	$names = (new SkillComposer)->all()->map(fn (Skill $s) => $s->name)->all();

	expect($names)->not->toContain('brave-post-type');
});
