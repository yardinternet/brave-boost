<?php

declare(strict_types=1);

namespace Yard\Brave\Boost\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use function Laravel\Prompts\multiselect;
use Yard\Brave\Boost\Contracts\Agent;
use Yard\Brave\Boost\Install\AgentDetector;
use Yard\Brave\Boost\Install\Installer;
use Yard\Brave\Boost\Install\ProjectFlags;
use Yard\Brave\Boost\Support\Config;

use Yard\Brave\Boost\Support\Paths;

class InstallCommand extends Command
{
	protected $signature = 'brave-boost:install
        {--path= : Project root to write into (defaults to the git root)}
        {--no-guidelines : Skip writing AI guidelines}
        {--no-skills : Skip installing skills}';

	protected $description = 'Install Brave AI guidelines and skills into your editors/agents';

	public function handle(AgentDetector $detector, Installer $installer, Config $config): int
	{
		if (is_string($this->option('path')) && $this->option('path') !== '') {
			Paths::useProjectRoot((string) $this->option('path'));
		}

		$this->info('Brave Boost — writing to: '.Paths::projectRoot());

		$agents = $this->selectAgents($detector);

		if ($agents->isEmpty()) {
			$this->warn('No agents selected. Nothing to do.');

			return self::SUCCESS;
		}

		$features = $this->selectFeatures();

		if ($features->contains('guidelines')) {
			$this->report('Guidelines', $installer->installGuidelines($agents, ProjectFlags::detect()));
		}

		if ($features->contains('skills')) {
			$skills = $installer->installSkills($agents, $config->skills());
			$this->report('Skills', $skills['results']);
			$config->setSkills($skills['installed']);
		}

		$config->setAgents($agents->map(fn (Agent $a) => $a->name())->all());
		$config->save();

		$this->newLine();
		$this->info('Done. Config saved to '.$config->path());

		return self::SUCCESS;
	}

	/**
	 * @return Collection<int, Agent>
	 */
	protected function selectAgents(AgentDetector $detector): Collection
	{
		$all = $detector->all();

		/** @var list<string> $options */
		$options = $all->mapWithKeys(fn (Agent $a) => [$a->name() => $a->displayName()])->all();

		$selected = multiselect(
			label: 'Which agents should receive Brave guidelines/skills?',
			options: $options,
			default: $detector->detected(),
			hint: 'Detected agents are pre-selected. Re-run any time to change.',
		);

		return $all->filter(fn (Agent $a) => in_array($a->name(), $selected, true))->values();
	}

	/**
	 * @return Collection<int, string>
	 */
	protected function selectFeatures(): Collection
	{
		$options = [];
		if (! $this->option('no-guidelines')) {
			$options['guidelines'] = 'AI guidelines';
		}
		if (! $this->option('no-skills')) {
			$options['skills'] = 'Skills';
		}

		if ([] === $options) {
			return collect();
		}

		if (count($options) === 1) {
			$selected = array_keys($options);
		} else {
			$selected = multiselect(
				label: 'What should be installed?',
				options: $options,
				default: array_keys($options),
				required: true,
			);
		}

		return collect($selected)->map(fn ($key): string => (string) $key)->values();
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
