<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Contract;

final class ContractController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->view('contracts/index', [
            'pageTitle' => 'Contratos · Opções B3',
            'activeNav' => 'contratos',
            'contracts' => Contract::all(),
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->view('contracts/form', [
            'pageTitle' => 'Novo contrato · Opções B3',
            'activeNav' => 'contratos',
            'contract'  => null,
            'errors'    => [],
            'old'       => [],
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $data = $this->makeContractData();

        $errors = Validator::make($data, [
            'ativo'           => ['required', 'min:2', 'max:20'],
            'tipo_opcao'      => ['required', 'in:CALL,PUT'],
            'preco_exercicio' => ['required', 'numeric', 'positive'],
            'data_vencto'     => ['required', 'date'],
            'preco_atual'     => ['required', 'numeric', 'gte:0'],
        ])->errors();

        if ($errors !== []) {
            $this->storeOldInput($data);
            $this->view('contracts/form', [
                'pageTitle' => 'Novo contrato · Opções B3',
                'activeNav' => 'contratos',
                'contract'  => null,
                'errors'    => $errors,
                'old'       => $data,
            ]);
            return;
        }

        Contract::create(
            (string) $data['ativo'],
            (string) $data['tipo_opcao'],
            (float) $data['preco_exercicio'],
            (string) $data['data_vencto'],
            (float) $data['preco_atual']
        );

        Session::flash('success', 'Contrato cadastrado com sucesso!');
        $this->redirect('/contratos');
    }

    public function edit(): void
    {
        $this->requireAuth();
        $id = $this->intParam('id');
        if ($id === null) $this->notFound();

        $contract = Contract::findById($id);
        if ($contract === null) $this->notFound();

        $old = [
            'ativo'           => (string) $contract['ativo'],
            'tipo_opcao'      => (string) $contract['tipo_opcao'],
            'preco_exercicio' => (string) $contract['preco_exercicio'],
            'data_vencto'     => (string) $contract['data_vencto'],
            'preco_atual'     => (string) $contract['preco_atual'],
        ];

        $this->view('contracts/form', [
            'pageTitle' => 'Editar contrato · Opções B3',
            'activeNav' => 'contratos',
            'contract'  => $contract,
            'errors'    => [],
            'old'       => $old,
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $id = $this->intParam('id');
        if ($id === null) $this->notFound();

        if (Contract::findById($id) === null) $this->notFound();

        $data = $this->makeContractData();

        $errors = Validator::make($data, [
            'ativo'           => ['required', 'min:2', 'max:20'],
            'tipo_opcao'      => ['required', 'in:CALL,PUT'],
            'preco_exercicio' => ['required', 'numeric', 'positive'],
            'data_vencto'     => ['required', 'date'],
            'preco_atual'     => ['required', 'numeric', 'gte:0'],
        ])->errors();

        if ($errors !== []) {
            $this->storeOldInput($data);
            $this->view('contracts/form', [
                'pageTitle' => 'Editar contrato · Opções B3',
                'activeNav' => 'contratos',
                'contract'  => Contract::findById($id),
                'errors'    => $errors,
                'old'       => $data,
            ]);
            return;
        }

        Contract::update(
            $id,
            (string) $data['ativo'],
            (string) $data['tipo_opcao'],
            (float) $data['preco_exercicio'],
            (string) $data['data_vencto'],
            (float) $data['preco_atual']
        );

        Session::flash('success', 'Contrato atualizado com sucesso!');
        $this->redirect('/contratos');
    }

    private function makeContractData(): array
    {
        return [
            'ativo'           => $this->input('ativo') ?? '',
            'tipo_opcao'      => strtoupper($this->input('tipo_opcao') ?? ''),
            'preco_exercicio' => $this->input('preco_exercicio') ?? '',
            'data_vencto'     => $this->input('data_vencto') ?? '',
            'preco_atual'     => $this->input('preco_atual') ?? '',
        ];
    }
}
