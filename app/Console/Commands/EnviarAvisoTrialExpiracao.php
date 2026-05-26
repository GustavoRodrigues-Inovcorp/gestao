<?php
namespace App\Console\Commands;

use App\Mail\TrialExpiraMail;
use App\Models\PlanoLog;
use App\Models\Subscricao;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnviarAvisoTrialExpiracao extends Command
{
    protected $signature = 'subscricoes:enviar-aviso-trial';
    protected $description = 'Envia email de aviso quando o trial está prestes a terminar';

    public function handle(): int
    {
        $alvo = now()->addDays(3)->toDateString();

        Subscricao::with(['tenant.users', 'plano'])
            ->where('estado', 'trial')
            ->whereDate('trial_fim', $alvo)
            ->whereNull('trial_aviso_enviado_em')
            ->chunkById(100, function ($subscricoes) {
                foreach ($subscricoes as $subscricao) {
                    // Compat: evitar operador nullsafe para analisadores sem PHP8
                    $emails = [];
                    if ($subscricao->tenant && $subscricao->tenant->users) {
                        $emails = $subscricao->tenant->users
                            ->pluck('email')
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();
                    }

                    if (empty($emails)) {
                        continue;
                    }

                    foreach ($emails as $email) {
                        Mail::to($email)->send(new TrialExpiraMail($subscricao, 3));
                    }

                    $subscricao->update([
                        'trial_aviso_enviado_em' => now(),
                    ]);

                    $firstUser = null;
                    if ($subscricao->tenant) {
                        $firstUser = $subscricao->tenant->users()->first();
                    }

                    PlanoLog::create([
                        'tenant_id'         => $subscricao->tenant_id,
                        'user_id'           => $firstUser ? $firstUser->id : 1,
                        'acao'              => 'trial_aviso_enviado',
                        'plano_novo_id'     => $subscricao->plano_id,
                        'notas'             => 'Email de aviso de fim do trial enviado.',
                    ]);
                }
            });

        $this->info('Avisos de trial enviados.');

        return self::SUCCESS;
    }
}