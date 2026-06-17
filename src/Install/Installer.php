<?php

declare(strict_types=1);

namespace Yard\Brave\Boost\Install;

use Illuminate\Support\Collection;
use Yard\Brave\Boost\Contracts\Agent;
use Yard\Brave\Boost\Contracts\SupportsSkills;

/**
 * Performs the actual writing once choices are known. Shared by the install and
 * update commands so both behave identically.
 */
class Installer
{
	public function __construct(
		protected SkillComposer $skills,
	) {
	}

	/**
	 * Write the composed guideline block to each agent.
	 *
	 * @param  Collection<int, Agent>  $agents
	 * @param  array<string, bool>     $flags  Project flags for conditional guideline sections.
	 *
	 * @return array<string, string>   Agent display name => 'ok' | error message.
	 */
	public function installGuidelines(Collection $agents, array $flags = []): array
	{
		$block = (new GuidelineComposer($flags))->compose();
		$results = [];

		foreach ($agents as $agent) {
			try {
				(new GuidelineWriter($agent))->write($block);
				$results[$agent->displayName()] = 'ok';
			} catch (\Throwable $e) {
				$results[$agent->displayName()] = $e->getMessage();
			}
		}

		return $results;
	}

	/**
	 * Sync bundled skills into every skill-capable agent.
	 *
	 * @param  Collection<int, Agent>  $agents
	 * @param  list<string>            $previous
	 *
	 * @return array{installed: list<string>, results: array<string, string>}
	 */
	public function installSkills(Collection $agents, array $previous = []): array
	{
		$skills = $this->skills->all();
		$installed = [];
		$results = [];

		foreach ($agents as $agent) {
			if (! $agent instanceof SupportsSkills) {
				continue;
			}

			try {
				$installed = (new SkillWriter($agent))->sync($skills, $previous);
				$results[$agent->displayName()] = 'ok';
			} catch (\Throwable $e) {
				$results[$agent->displayName()] = $e->getMessage();
			}
		}

		return ['installed' => $installed, 'results' => $results];
	}

	/**
	 * @return Collection<int, Skill>
	 */
	public function availableSkills(): Collection
	{
		return $this->skills->all();
	}
}
