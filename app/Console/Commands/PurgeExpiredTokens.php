<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class PurgeExpiredTokens extends Command
{
    protected $signature = 'tokens:purge-expired';

    protected $description = 'Elimina tokens de Sanctum con fecha de expiración vencida.';

    public function handle(): int
    {
        $deleted = PersonalAccessToken::where('expires_at', '<', now())->delete();

        $this->info("Tokens vencidos eliminados: {$deleted}");

        return self::SUCCESS;
    }
}
