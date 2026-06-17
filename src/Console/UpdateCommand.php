<?php

declare(strict_types=1);

namespace Yard\Brave\Boost\Console;

use Illuminate\Console\Command;
use Yard\Brave\Boost\Install\AgentDetector;
use Yard\Brave\Boost\Install\Installer;
use Yard\Brave\Boost\Install\ProjectFlags;
use Yard\Brave\Boost\Support\Config;
use Yard\Brave\Boost\Support\Paths;

/**
 * Non-interactive re-run. Reads brave-boost.json and rewrites guidelines + skills
 * for the previously selected agents. Run after `composer update`.
 */
class UpdateCommand extends Command
{
	protected $signature = 'brave-boost:update
        {--path= : Project root to write into (defaults to the git root)}';

	protected $description = 'Re-apply Brave guidelines and skills from the saved config';

	public function handle(AgentDetector $detector, Installer $installer, Config $config): int
	{
		if (is_string($this->option('path')) && $this->option('path') !== '') {
			Paths::useProjectRoot((string) $this->option('path'));
		}

		$agents = $detector->byNames($config->agents());

		if ($agents->isEmpty()) {
			$this->warn('No saved agents in '.$config->path().'. Run brave-boost:install first.');

			return self::SUCCESS;
		}

		$installer->installGuidelines($agents, ProjectFlags::detect());

		$skills = $installer->installSkills($agents, $config->skills());
		$config->setSkills($skills['installed']);
		$config->save();

		$this->info('Brave Boost updated for: '.$agents->map->displayName()->implode(', '));

		return self::SUCCESS;
	}
}
