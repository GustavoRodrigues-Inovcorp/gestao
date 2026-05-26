<?php
namespace Database\Seeders;

use App\Models\Plano;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlanosSeeder extends Seeder
{
    public function run(): void
    {
        $planos = [
            [
                'nome'             => 'Free',
                'slug'             => 'free',
                'descricao'        => 'Plano gratuito com funcionalidades básicas.',
                'preco_mensal'     => 0,
                'preco_anual'      => 0,
                'max_utilizadores' => 1,
                'max_clientes'     => 10,
                'max_documentos'   => 20,
                'trial_dias'       => 0,
                'ativo'            => true,
                'is_free'          => true,
                'ordem'            => 1,
                'features'         => ['clientes', 'propostas', 'calendario'],
            ],
            [
                'nome'             => 'Starter',
                'slug'             => 'starter',
                'descricao'        => 'Para pequenas equipas que precisam de mais.',
                'preco_mensal'     => 19.99,
                'preco_anual'      => 199.90,
                'max_utilizadores' => 3,
                'max_clientes'     => 100,
                'max_documentos'   => 500,
                'trial_dias'       => 14,
                'ativo'            => true,
                'is_free'          => false,
                'ordem'            => 2,
                'features'         => ['clientes', 'fornecedores', 'propostas', 'encomendas', 'calendario', 'financeiro'],
            ],
            [
                'nome'             => 'Pro',
                'slug'             => 'pro',
                'descricao'        => 'Para empresas em crescimento.',
                'preco_mensal'     => 49.99,
                'preco_anual'      => 499.90,
                'max_utilizadores' => 10,
                'max_clientes'     => 500,
                'max_documentos'   => 2000,
                'trial_dias'       => 14,
                'ativo'            => true,
                'is_free'          => false,
                'ordem'            => 3,
                'features'         => ['clientes', 'fornecedores', 'propostas', 'encomendas', 'calendario', 'financeiro', 'arquivo_digital', 'ordens_trabalho'],
            ],
            [
                'nome'             => 'Enterprise',
                'slug'             => 'enterprise',
                'descricao'        => 'Sem limites. Para grandes organizações.',
                'preco_mensal'     => 99.99,
                'preco_anual'      => 999.90,
                'max_utilizadores' => -1, // ilimitado
                'max_clientes'     => -1,
                'max_documentos'   => -1,
                'trial_dias'       => 14,
                'ativo'            => true,
                'is_free'          => false,
                'ordem'            => 4,
                'features'         => ['clientes', 'fornecedores', 'propostas', 'encomendas', 'calendario', 'financeiro', 'arquivo_digital', 'ordens_trabalho', 'configuracoes'],
            ],
        ];

        foreach ($planos as $plano) {
            Plano::firstOrCreate(['slug' => $plano['slug']], $plano);
        }
    }
}