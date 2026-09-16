<?php

namespace App\Resources;

use Google\Client;
use Google\Service\Drive;
use RuntimeException;

class WorkspaceResourse
{
	private ?Drive $drive = null;
	private readonly string $tokenPath;

	public function __construct()
	{
		$this->tokenPath = $_ENV['GOOGLE_WORKSPACE_TOKEN_PATH']
			?? $_SERVER['GOOGLE_WORKSPACE_TOKEN_PATH']
			?? dirname(__DIR__, 2) . '/var/google_workspace_token.json';
	}

	private function drive(): Drive
	{
		if ($this->drive instanceof Drive) {
			return $this->drive;
		}

		$client = new Client();
		$client->setClientId($_ENV['GOOGLE_CLIENT_ID'] ?? null);
		$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET'] ?? null);
		$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI'] ?? 'http://localhost:8000');
		$client->setScopes([
			'https://www.googleapis.com/auth/calendar',
			Drive::DRIVE_READONLY,
		]);
		$client->setAccessType('offline');
		$client->setIncludeGrantedScopes(true);
		$client->setPrompt('consent');

		if (is_file($this->tokenPath)) {
			$token = json_decode((string) file_get_contents($this->tokenPath), true);
			if (is_array($token)) {
				$client->setAccessToken($token);
			}
		}

		if ($client->isAccessTokenExpired()) {
			if ($client->getRefreshToken()) {
				$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
				file_put_contents($this->tokenPath, json_encode($client->getAccessToken(), JSON_THROW_ON_ERROR));
			} else {
				$authUrl = $client->createAuthUrl();
				throw new RuntimeException("Google Workspace OAuth authorization is required. Open this URL: {$authUrl}");
			}
		}

		$this->drive = new Drive($client);

		return $this->drive;
	}

	public function search(string $query): string
	{
		$escapedQuery = str_replace('\\', '\\\\', str_replace("'", "\\'", $query));
		$drive = $this->drive();
		$files = $drive->files->listFiles([
			'q' => "trashed = false and fullText contains '{$escapedQuery}'",
			'pageSize' => 20,
			'orderBy' => 'modifiedTime desc',
			'fields' => 'files(id,name,mimeType,description,modifiedTime,webViewLink)',
		])->getFiles();

		$matches = [];
		foreach ($files as $file) {
			$content = $this->readFileContent($drive, $file);
			$matches[] = [
				'id' => $file->getId(),
				'name' => $file->getName(),
				'mime_type' => $file->getMimeType(),
				'modified_at' => $file->getModifiedTime(),
				'url' => $file->getWebViewLink(),
				'content' => $content,
			];
		}

		return json_encode([
			'query' => $query,
			'source' => 'google_drive',
			'matches' => $matches,
		], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
	}

	private function readFileContent(Drive $drive, Drive\DriveFile $file): string
	{
		$mimeType = $file->getMimeType();
		$exportMimeType = match ($mimeType) {
			'application/vnd.google-apps.document' => 'text/plain',
			'application/vnd.google-apps.spreadsheet' => 'text/csv',
			default => null,
		};

		if ($exportMimeType === null) {
			return $file->getDescription() ?? '';
		}

		$response = $drive->files->export($file->getId(), $exportMimeType, ['alt' => 'media']);

		return (string) $response->getBody();
	}
}
