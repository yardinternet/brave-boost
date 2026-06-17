<?php

declare(strict_types=1);

use Yard\Brave\Boost\Install\GuidelineWriter;
use Yard\Brave\Boost\Support\Paths;

beforeEach(function () {
	Paths::useProjectRoot(tmpDir());
});

it('writes a wrapped guideline block to a new file', function () {
	(new GuidelineWriter(fakeAgent('AGENT.md')))->write('hello rules');

	$contents = file_get_contents(Paths::project('AGENT.md'));

	expect($contents)
		->toContain('<brave-boost-guidelines>')
		->toContain('hello rules')
		->toContain('</brave-boost-guidelines>');
});

it('seeds a header and do-not-edit hint on a new file', function () {
	(new GuidelineWriter(fakeAgent('AGENT.md')))->write('hello rules');

	$contents = file_get_contents(Paths::project('AGENT.md'));

	expect($contents)
		->toContain('# AI guidelines')
		->toContain('Do not edit inside these markers')
		->and($contents)->toStartWith('# AI guidelines');
});

it('does not re-add the header when only the block is replaced', function () {
	$writer = new GuidelineWriter(fakeAgent('AGENT.md'));
	$writer->write('first');
	$writer->write('second');

	$contents = file_get_contents(Paths::project('AGENT.md'));

	expect(substr_count($contents, '# AI guidelines'))->toBe(1);
});

it('creates nested directories for the guideline path', function () {
	(new GuidelineWriter(fakeAgent('.cursor/rules/brave.mdc')))->write('x rules');

	expect(is_file(Paths::project('.cursor/rules/brave.mdc')))->toBeTrue();
});

it('prepends frontmatter only when the agent requires it and file is empty', function () {
	(new GuidelineWriter(fakeAgent('A.mdc', frontmatter: true)))->write('x');

	expect(file_get_contents(Paths::project('A.mdc')))->toStartWith("---\nalwaysApply: true\n---");
});

it('preserves custom content and appends the block', function () {
	$path = Paths::project('AGENT.md');
	file_put_contents($path, "# My own notes\nkeep me");

	(new GuidelineWriter(fakeAgent('AGENT.md')))->write('boost rules');

	expect(file_get_contents($path))
		->toContain('# My own notes')
		->toContain('keep me')
		->toContain('boost rules');
});

it('replaces an existing block without duplicating it', function () {
	$agent = fakeAgent('AGENT.md');
	$writer = new GuidelineWriter($agent);

	$writer->write('first version');
	$writer->write('second version');

	$contents = file_get_contents(Paths::project('AGENT.md'));

	expect(substr_count($contents, '<brave-boost-guidelines>'))->toBe(1)
		->and($contents)->toContain('second version')
		->and($contents)->not->toContain('first version');
});
