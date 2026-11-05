<?php
declare(strict_types=1);
namespace App\Integrations\Adapters;
use App\Integrations\Contracts\IntegrationAdapter;
class WhatsAppAdapter implements IntegrationAdapter
{
    public function isConfigured(): bool { return false; }
    public function test(): bool { return false; }
}
