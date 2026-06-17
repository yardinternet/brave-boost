<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Yard\Brave\Boost\Install\Skill;
use Yard\Brave\Boost\Install\SkillWriter;
use Yard\Brave\Boost\Support\Paths;

beforeEach(function () {
	Paths::useProjectRoot(tmpDir());
});

/**
 * Build a fake skill source dir with a SKILL.md and a nested reference file.
 */
function makeSkill(string $name): Skill
{
	$dir = tmpDir('skill-src').'/'.$name;
	mkdir($dir.'/references', 0755, true);
	file_put_contents($dir.'/SKILL.md', "---\nname: {$name}\n---\nbody");
	file_put_contents($dir.'/references/note.md', 'ref');

	return new Skill($name, 'desc', $dir);
}

it('copies a skill directory recursively into the agent skills path', function () {
	$writer = new SkillWriter(fakeSkillAgent('.claude/skills'));

	$installed = $writer->sync(new Collection([makeSkill('alpha')]));

	expect($installed)->toBe(['alpha'])
		->and(is_file(Paths::project('.claude/skills/alpha/SKILL.md')))->toBeTrue()
		->and(is_file(Paths::project('.claude/skills/alpha/references/note.md')))->toBeTrue();
});

it('prunes previously installed skills that are no longer bundled', function () {
	$base = Paths::project('.claude/skills');
	mkdir($base.'/stale', 0755, true);
	file_put_contents($base.'/stale/SKILL.md', 'old');

	$writer = new SkillWriter(fakeSkillAgent('.claude/skills'));
	$writer->sync(new Collection([makeSkill('alpha')]), previous: ['stale', 'alpha']);

	expect(is_dir($base.'/stale'))->toBeFalse()
		->and(is_dir($base.'/alpha'))->toBeTrue();
});
