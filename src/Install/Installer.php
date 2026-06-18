<?php

declare(strict_types=1);

namespace Yard\Brave\Boost\Install;

use Illuminate\Support\Collection;
use Yard\Brave\Boost\Contracts\Agent;
use Yard\Brave\Boost\Contracts\SupportsSkills;

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
	 * @param  array<string, bool>     $flags
	 *
	 * @return array<string, string>  Agent display name => 'ok' | error message.
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
	 *
	 * @return array<string, string>  Agent display name => 'ok' | error message.
	 */
	public function installSkills(Collection $agents): array
	{
		$skills = $this->skills->all();
		$results = [];

		foreach ($agents as $agent) {
			if (! $agent instanceof SupportsSkills) {
				continue;
			}

			try {
				(new SkillWriter($agent))->sync($skills);
				$results[$agent->displayName()] = 'ok';
			} catch (\Throwable $e) {
				$results[$agent->displayName()] = $e->getMessage();
			}
		}

		return $results;
	}

	/**
	 * @return Collection<int, Skill>
	 */
	public function availableSkills(): Collection
	{
		return $this->skills->all();
	}
}
