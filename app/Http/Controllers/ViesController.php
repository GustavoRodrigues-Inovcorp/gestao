<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ViesController extends Controller
{
    public function lookup(Request $request)
    {
        // Endpoint simples para preencher automaticamente dados fiscais a partir do VIES.
        $request->validate([
            'country_code' => ['required', 'string', 'size:2'],
            'vat_number'   => ['required', 'string'],
        ]);

        $countryCode = strtoupper((string) $request->input('country_code'));
        $vatNumber   = preg_replace('/[^0-9A-Za-z]/', '', (string) $request->input('vat_number'));

        try {
            $response = Http::timeout(10)->get('https://ec.europa.eu/taxation_customs/vies/rest-api/ms/' . $countryCode . '/vat/' . $vatNumber);

            if ($response->failed()) {
                return response()->json(['error' => 'Erro ao consultar o VIES.'], 422);
            }

            $data = $response->json();

            if (!$data['isValid']) {
                return response()->json(['error' => 'NIF inválido ou não encontrado no VIES.'], 422);
            }

            // Divide o endereço devolvido pelo serviço em linhas úteis.
            $address = $data['address'] ?? '';
            $lines   = array_filter(array_map('trim', explode("\n", $address)));
            $lines   = array_values($lines);

            return response()->json([
                'nome'     => $data['name'] ?? '',
                'morada'   => $lines[0] ?? '',
                'localidade' => $lines[1] ?? '',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível contactar o VIES.'], 422);
        }
    }
}