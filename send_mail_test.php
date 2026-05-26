<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Mail\TrialExpiraMail;
use App\Models\Plano;
use App\Models\Subscricao;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

try {
    $recipient = getenv('MAIL_TEST_TO') ?: User::query()->whereNotNull('email')->value('email');

    if (!$recipient) {
        throw new RuntimeException('Sem destinatario para testar o email.');
    }

    $plano = Plano::where('ativo', true)->orderBy('ordem')->first() ?? Plano::first();
    if (!$plano) {
        throw new RuntimeException('Sem plano disponivel para o teste.');
    }

    $subscricao = new Subscricao([
        'estado' => 'trial',
        'trial_inicio' => now()->subDays(11),
        'trial_fim' => now()->addDays(3),
    ]);
    $subscricao->setRelation('plano', $plano);

    Mail::to($recipient)->send(new TrialExpiraMail($subscricao, 3));

    echo "MAIL_OK:{$recipient}\n";
} catch (Throwable $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
    exit(1);
}
