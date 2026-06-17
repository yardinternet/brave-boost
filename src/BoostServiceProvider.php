<?php

declare(strict_types=1);

namespace Yard\Brave\Boost;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Yard\Brave\Boost\Console\InstallCommand;
use Yard\Brave\Boost\Console\UpdateCommand;

class BoostServiceProvider extends PackageServiceProvider
{
	public function configurePackage(Package $package): void
	{
		$package
			->name('brave-boost')
			->hasConfigFile()
			->hasCommands([
				InstallCommand::class,
				UpdateCommand::class,
			]);
	}
}
