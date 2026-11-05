<?php
declare(strict_types=1);
namespace App\Integrations\Contracts;
interface IntegrationAdapter
{
    public function isConfigured(): bool;
    public function test(): bool;
}
