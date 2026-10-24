<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Contract;

final class AnalysisController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        
        $contracts = Contract::all();

        $this->view('analysis/index', [
            'pageTitle' => 'Calculadora de Opções · Opções B3',
            'activeNav' => 'analise',
            'contracts' => $contracts,
            'errors'    => [],
            'old'       => [],
            'result'    => null,
            'layout'    => 'layouts/main',
        ]);
    }

    public function calculate(): void
    {
        $this->requireAuth();

        $contractId = $this->input('contrato_id') ?? '';
        $precoAtivo = $this->input('preco_ativo') ?? '';

        $errors = Validator::make([
            'contrato_id' => $contractId,
            'preco_ativo' => $precoAtivo,
        ], [
            'contrato_id' => ['required', 'int'],
            'preco_ativo' => ['required', 'numeric', 'positive'],
        ])->errors();

        $contract = null;
        if (!isset($errors['contrato_id'])) {
            $contract = Contract::findById((int) $contractId);
            if ($contract === null) {
                $errors['contrato_id'] = 'Selecione um contrato válido.';
            }
        }

        if ($errors !== []) {
            $this->storeOldInput(['contrato_id' => $contractId, 'preco_ativo' => $precoAtivo]);
            $this->view('analysis/index', [
                'pageTitle' => 'Calculadora de Opções · Opções B3',
                'activeNav' => 'analise',
                'contracts' => Contract::all(),
                'errors'    => $errors,
                'old'       => ['contrato_id' => $contractId, 'preco_ativo' => $precoAtivo],
                'result'    => null,
                'layout'    => 'layouts/main',
            ]);
            return;
        }

        $strike     = (float) $contract['preco_exercicio'];
        $tipo       = strtoupper((string) $contract['tipo_opcao']);
        $precoAtivo = (float) $precoAtivo;

        if ($tipo === 'CALL') {
            $valorIntrinseco = max(0.0, $precoAtivo - $strike);
            $status = $precoAtivo > $strike 
                ? 'No Dinheiro (In-the-Money)' 
                : ($precoAtivo === $strike ? 'No Dinheiro (At-the-Money)' : 'Fora do Dinheiro (Out-of-the-Money)');
        } else { // PUT
            $valorIntrinseco = max(0.0, $strike - $precoAtivo);
            $status = $precoAtivo < $strike 
                ? 'No Dinheiro (In-the-Money)' 
                : ($precoAtivo === $strike ? 'No Dinheiro (At-the-Money)' : 'Fora do Dinheiro (Out-of-the-Money)');
        }

        $result = [
            'contract'        => $contract,
            'preco_ativo'     => $precoAtivo,
            'strike'          => $strike,
            'tipo'            => $tipo,
            'valor_intrinseco'=> $valorIntrinseco,
            'status'          => $status,
        ];

        Session::flash('success', 'Cálculo realizado com sucesso!');
        $this->view('analysis/index', [
            'pageTitle' => 'Calculadora de Opções · Opções B3',
            'activeNav' => 'analise',
            'contracts' => Contract::all(),
            'errors'    => [],
            'old'       => ['contrato_id' => $contractId, 'preco_ativo' => $precoAtivo],
            'result'    => $result,
            'layout'    => 'layouts/main',
        ]);
    }
}
