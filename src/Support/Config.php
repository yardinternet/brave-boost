<?php

declare(strict_types=1);

namespace Yard\Brave\Boost\Support;

/**
 * Persists install choices to brave-boost.json at the project root so re-runs
 * and the update command can replay them without prompting.
 */
class Config
{
	protected const FILE = 'brave-boost.json';

	/** @var array<string, mixed> */
	protected array $data;

	public function __construct()
	{
		$this->data = $this->read();
	}

	/**
	 * @return list<string>
	 */
	public function agents(): array
	{
		return array_values((array) ($this->data['agents'] ?? []));
	}

	/**
	 * @param list<string> $agents
	 */
	public function setAgents(array $agents): void
	{
		$this->data['agents'] = array_values($agents);
	}

	/**
	 * @return list<string>
	 */
	public function skills(): array
	{
		return array_values((array) ($this->data['skills'] ?? []));
	}

	/**
	 * @param list<string> $skills
	 */
	public function setSkills(array $skills): void
	{
		$this->data['skills'] = array_values($skills);
	}

	public function path(): string
	{
		return Paths::project(self::FILE);
	}

	public function save(): void
	{
		ksort($this->data);

		file_put_contents(
			$this->path(),
			json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function read(): array
	{
		$path = $this->path();

		if (! is_file($path)) {
			return [];
		}

		$decoded = json_decode((string) file_get_contents($path), true);

		return is_array($decoded) ? $decoded : [];
	}
}
