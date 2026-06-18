<?php

declare(strict_types=1);

namespace Yard\Brave\Boost\Install;

use Illuminate\Support\Collection;
use Yard\Brave\Boost\Agents\ClaudeCode;
use Yard\Brave\Boost\Agents\Copilot;
use Yard\Brave\Boost\Agents\Cursor;
use Yard\Brave\Boost\Contracts\Agent;
use Yard\Brave\Boost\Support\Paths;

// Detection is unused since install writes to all agents unconditionally.
// Kept for potential future use.
class AgentDetector
{
	/**
	 * Every agent the package knows how to write to.
	 *
	 * @return Collection<int, Agent>
	 */
	public function all(): Collection
	{
		/** @var Collection<int, Agent> $agents */
		$agents = collect([
			new ClaudeCode,
			new Cursor,
			new Copilot,
		]);

		return $agents;
	}

	/**
	 * Resolve agents by their machine name (used by the update command).
	 *
	 * @param  list<string>  $names
	 *
	 * @return Collection<int, Agent>
	 */
	public function byNames(array $names): Collection
	{
		return $this->all()->filter(fn (Agent $a) => in_array($a->name(), $names, true))->values();
	}

	/**
	 * Machine names of agents that look already present in the project or system,
	 * used to pre-select the install prompt.
	 *
	 * @return list<string>
	 */
	public function detected(): array
	{
		return $this->all()
			->filter(fn (Agent $agent) => $this->isPresent($agent))
			->map(fn (Agent $agent) => $agent->name())
			->values()
			->all();
	}

	protected function isPresent(Agent $agent): bool
	{
		foreach ($agent->projectHints() as $hint) {
			if (file_exists(Paths::project($hint))) {
				return true;
			}
		}

		return $agent->systemBinary() !== null && $this->binaryExists($agent->systemBinary());
	}

	protected function binaryExists(string $binary): bool
	{
		$probe = stripos(PHP_OS, 'WIN') === 0 ? 'where' : 'command -v';

		$result = @shell_exec($probe.' '.escapeshellarg($binary).' 2>/dev/null');

		return is_string($result) && trim($result) !== '';
	}
}
