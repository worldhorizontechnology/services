<?php
declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);
use App\Resources\GoogleCalendarResource;
use App\Tools\CalendarTools;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Mcp\Server;
use Mcp\Server\Session\FileSessionStore;
use Mcp\Server\Transport\StreamableHttpTransport;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/vendor/autoload.php';

// 1. Загрузка переменных окружения из .env
if (file_exists(__DIR__ . '/.env')) {
    (new Dotenv())->usePutenv(true)->bootEnv(__DIR__ . '/.env');
}

// 2. Получение HTTP-запроса
$request = Request::createFromGlobals();

// 3. Считывание и разбор DATABASE_URL
$dbUrl = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? getenv('DATABASE_URL');

if (!$dbUrl) {
    throw new RuntimeException('Переменная DATABASE_URL не найдена в окружении или файле .env');
}

// DsnParser автоматически превращает DSN-строку в массив с ключом 'driver' => 'pdo_sqlite'
$connectionParams = (new DsnParser())->parse($dbUrl);
$connection = DriverManager::getConnection($connectionParams);

// 4. Инициализация сервисов и инструментов
$calendarTools = new CalendarTools(
    new GoogleCalendarResource(),
    $connection
);

// 5. Сборка MCP Server
$server = Server::builder()
    ->setServerInfo('My MCP Server', '1.0.0')
    ->setSession(new FileSessionStore(__DIR__ . '/var/sessions'))
    ->addTool([$calendarTools, 'checkCalendarSlots'], 'check_calendar_slots')
    ->addTool([$calendarTools, 'bookCalendarSlots'], 'book_calendar_slots')
    ->build();

// 6. Конвертация в PSR-7 и подготовка HTTP-транспорта
$psr17Factory = new Psr17Factory();
$psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
$psrRequest = $psrHttpFactory->createRequest($request);

try {
    $transport = new StreamableHttpTransport(
        $psrRequest,
        $psr17Factory,
        $psr17Factory
    );

    // 7. Запуск сервера и отправка ответа
    $psrResponse = $server->run($transport);

    $symfonyResponse = new Response(
        (string) $psrResponse->getBody(),
        $psrResponse->getStatusCode(),
        $psrResponse->getHeaders()
    );
} catch (\Throwable $e) {
    // Выводим реальную причину падения инструмента прямо в ответ
    $symfonyResponse = new Response(
        json_encode([
            'error_class' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        500,
        ['Content-Type' => 'application/json']
    );
}

$symfonyResponse->send();