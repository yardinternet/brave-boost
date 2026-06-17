<?php

declare(strict_types=1);

use Yard\Brave\Boost\Install\GuidelineComposer;

it('composes bundled guideline fragments into named sections', function () {
	$output = (new GuidelineComposer)->compose();

	expect($output)
		->toContain('=== foundation rules ===')
		->toContain('=== brave-core rules ===');
});

it('renders blade fragments with the given flags', function () {
	$withVite = (new GuidelineComposer(['hasVite' => true]))->compose();
	$withoutVite = (new GuidelineComposer)->compose();

	expect($withVite)->toContain('built with Vite')
		->and($withoutVite)->not->toContain('built with Vite');
});
