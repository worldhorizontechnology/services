<?php

namespace App\Resources;

use RuntimeException;

class WorkspaceResourse
{
	public function __construct(private readonly string $knowledgePath)
	{
	}

	public function search(string $query): string
	{
		if ($this->knowledgePath === '' || !is_file($this->knowledgePath)) {
			throw new RuntimeException('Workspace knowledge file is not configured or does not exist.');
		}

		$contents = file_get_contents($this->knowledgePath);
		if ($contents === false) {
			throw new RuntimeException('Workspace knowledge file cannot be read.');
		}

		$terms = array_filter(preg_split('/\s+/u', mb_strtolower(trim($query))) ?: []);
		$matches = array_values(array_filter(
			preg_split('/\R/', $contents) ?: [],
			static function (string $line) use ($terms): bool {
				$normalized = mb_strtolower($line);

				return $terms === [] || count(array_filter(
					$terms,
					static fn (string $term): bool => str_contains($normalized, $term)
				)) > 0;
			}
		));

		return json_encode([
			'query' => $query,
			'matches' => array_slice($matches, 0, 50),
		], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
	}
}
