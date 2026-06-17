<?php

declare(strict_types=1);

namespace Yard\Brave\Boost\Console;

use Illuminate\Console\Command;
use Yard\Brave\Boost\Boost;

class BoostCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'boost';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'My custom Acorn command.';

	/**
	 * Execute the console command.
	 */
	public function handle(): void
	{
		$this->info(
			app(Boost::class)->getQuote()
		);
	}
}
