use Mcp\Capability\Prompt\Completion\ProviderInterface;

class UserIdCompletionProvider implements ProviderInterface
{
    public function __construct(
        private DatabaseService $db
    ) {}

    public function getCompletions(string $currentValue): array
    {
        return $this->db->searchUserIds($currentValue);
    }
}

#[McpResourceTemplate(uriTemplate: 'user://{userId}/profile')]
public function getUserProfile(
    #[CompletionProvider(provider: UserIdCompletionProvider::class)]
    string $userId
): array
{
    return $this->users[$userId] ?? 
        throw new \InvalidArgumentException('User not found');
}