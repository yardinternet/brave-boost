<?php

declare(strict_types=1);

namespace Yard\Brave\Boost\Console;

use Illuminate\Console\Command;
use Yard\Brave\Boost\Install\AgentDetector;
use Yard\Brave\Boost\Install\Installer;
use Yard\Brave\Boost\Install\ProjectFlags;
use Yard\Brave\Boost\Support\Paths;

class InstallCommand extends Command
{
	protected $signature = 'brave-boost:install
        {--path= : Project root to write into (defaults to the git root)}
        {--no-guidelines : Skip writing AI guidelines}
        {--no-skills : Skip installing skills}';

	protected $description = 'Install Brave AI guidelines and skills into your editors/agents';

	public function handle(AgentDetector $detector, Installer $installer): int
	{
		if (is_string($this->option('path')) && $this->option('path') !== '') {
			Paths::useProjectRoot((string) $this->option('path'));
		}

		$this->info('Brave Boost — writing to: '.Paths::projectRoot());

		$agents = $detector->all();

		if (! $this->option('no-guidelines')) {
			$this->report('Guidelines', $installer->installGuidelines($agents, ProjectFlags::detect()));
		}

		if (! $this->option('no-skills')) {
			$this->report('Skills', $installer->installSkills($agents));
		}

		$this->newLine();
		$this->info('Done.');

		return self::SUCCESS;
	}

	/**
	 * @param  array<string, string>  $results
	 */
	protected function report(string $heading, array $results): void
	{
		$this->newLine();
		$this->line("<options=bold>{$heading}</>");

		foreach ($results as $agent => $status) {
			'ok' === $status
				? $this->line("  <fg=green>✓</> {$agent}")
				: $this->line("  <fg=red>✗</> {$agent}: {$status}");
		}
	}
}
