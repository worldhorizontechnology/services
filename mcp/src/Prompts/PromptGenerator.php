<?php

namespace App\Prompts;

use Mcp\Capability\Attribute\McpPrompt;
use Mcp\Schema\Content\{TextContent, ImageContent};
use Mcp\Schema\PromptMessage;
use Mcp\Schema\Enum\Role;

class PromptGenerator
{
    /**
     * Generates a code review request prompt.
     */
    #[McpPrompt(name: 'code_review')]
    public function reviewCode(string $language, string $code, string $focus = 'general'): array
    {
        return [
            ['role' => 'assistant', 'content' => 'You are an expert code reviewer.'],
            ['role' => 'user', 'content' => "Review this {$language} code focusing on {$focus}:\n\n```{$language}\n{$code}\n```"]
        ];
    }

    #[McpPrompt]
public function analyzeImage(string $imageUrl, string $question): array
{
    $imageData = file_get_contents($imageUrl);
    
    return [
        new PromptMessage(Role::Assistant, [
            new TextContent('You are an image analysis expert.')
        ]),
        new PromptMessage(Role::User, [
            new TextContent($question),
            new ImageContent(
                data: base64_encode($imageData),
                mimeType: 'image/jpeg'
            )
        ])
    ];
}
}