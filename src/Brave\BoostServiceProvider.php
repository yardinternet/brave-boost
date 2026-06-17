<?php

declare(strict_types=1);

namespace Yard\Brave\Boost;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Yard\Brave\Boost\Console\BoostCommand;

class Brave\BoostServiceProvider extends PackageServiceProvider
{
	public function configurePackage(Package $package): void
	{
		$package
			->name('brave-boost')
			->hasConfigFile()
			->hasViews()
			->hasCommand(BoostCommand::class);
	}

	public function packageRegistered(): void
	{
		$this->app->singleton(Boost::class, fn () => new Boost($this->app));
	}

	public function packageBooted(): void
	{
		$this->app->make(Boost::class);
	}
}
