<?php

namespace App\Resources;

use Google\Client;
use Google\Service\Drive;
use RuntimeException;

class WorkspaceResourse
{
	private Drive $drive;

	public function __construct(string $credentialsPath)
	{
		if ($credentialsPath === '' || !is_file($credentialsPath)) {
			throw new RuntimeException('GOOGLE_APPLICATION_CREDENTIALS must point to a readable service-account JSON file.');
		}

		$client = new Client();
		$client->setAuthConfig($credentialsPath);
		$client->setScopes([Drive::DRIVE_READONLY]);
		$this->drive = new Drive($client);
	}

	public function search(string $query): string
	{
		$escapedQuery = str_replace('\\', '\\\\', str_replace("'", "\\'", $query));
		$files = $this->drive->files->listFiles([
			'q' => "trashed = false and fullText contains '{$escapedQuery}'",
			'pageSize' => 20,
			'orderBy' => 'modifiedTime desc',
			'fields' => 'files(id,name,mimeType,description,modifiedTime,webViewLink)',
		])->getFiles();

		$matches = [];
		foreach ($files as $file) {
			$content = $this->readFileContent($file);
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

	private function readFileContent(Drive\DriveFile $file): string
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

		$response = $this->drive->files->export($file->getId(), $exportMimeType, ['alt' => 'media']);

		return (string) $response->getBody();
	}
}
