<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Contract;
use App\Models\Position;

final class PositionController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $user   = $this->currentUser();
        $userId = (int) $user->toArray()['id'];

        $filter  = isset($_GET['status']) ? strtoupper((string)$_GET['status']) : null;
        $validFilter = ['ABERTA', 'FECHADA'];
        if ($filter !== null && !in_array($filter, $validFilter, true)) {
            $filter = null;
        }

        $positions = Position::forUser($userId, $filter);

        $this->view('positions/index', [
            'pageTitle'   => 'Posições - Opções B3',
            'activeNav'   => 'posicoes',
            'positions'   => $positions,
            'filter'      => $filter ?? '',
            'user'        => $user->toArray(),
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $contracts = Contract::all();

        $this->view('positions/form', [
            'pageTitle'   => 'Nova posição - Opções B3',
            'activeNav'   => 'posicoes',
            'contracts'   => $contracts,
            'contract'    => null,
            'errors'      => [],
            'old'         => [],
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $user  = $this->currentUser();
        $uid   = (int) $user->toArray()['id'];

        $data = [
            'contrato_id' => $this->input('contrato_id') ?? '',
            'quantidade'  => $this->input('quantidade') ?? '',
            'preco_medio' => $this->input('preco_medio') ?? '',
        ];

        $contractValid = ctype_digit((string) $data['contrato_id'])
            && Contract::findById((int) $data['contrato_id']) !== null;

        $errors = Validator::make($data, [
            'contrato_id' => ['required', 'int'],
            'quantidade'  => ['required', 'positive'],
            'preco_medio' => ['required', 'numeric', 'positive'],
        ])->errors();

        if (!$contractValid) {
            $errors['contrato_id'] = 'Selecione um contrato válido.';
        }

        if ($errors !== []) {
            $this->storeOldInput($data);
            $this->view('positions/form', [
                'pageTitle'   => 'Nova posição - Opções B3',
                'activeNav'   => 'posicoes',
                'contracts'   => Contract::all(),
                'contract'    => null,
                'errors'      => $errors,
                'old'         => $data,
            ]);
            return;
        }

        try {
            Position::open(
                $uid,
                (int) $data['contrato_id'],
                (int)((float)$data['quantidade']),
                (float) $data['preco_medio']
            );
            Session::flash('success', 'Posição aberta com sucesso!');
        } catch (\Throwable $e) {
            $this->storeOldInput($data);
            $errors['_global'] = $e->getMessage();
            $this->view('positions/form', [
                'pageTitle'   => 'Nova posição - Opções B3',
                'activeNav'   => 'posicoes',
                'contracts'   => Contract::all(),
                'contract'    => null,
                'errors'      => $errors,
                'old'         => $data,
            ]);
            return;
        }

        $this->redirect('/posicoes');
    }

    public function closeForm(): void
    {
        $this->requireAuth();
        $user = $this->currentUser();
        $uid  = (int) $user->toArray()['id'];

        $id = $this->intParam('id');
        if ($id === null) $this->notFound();

        $position = Position::findById($id);
        if ($position === null || (int)$position['usuario_id'] !== $uid || $position['status'] !== 'ABERTA') {
            $this->notFound();
        }

        $this->view('positions/close-form', [
            'pageTitle'  => 'Fechar posição - Opções B3',
            'activeNav'  => 'posicoes',
            'position'   => $position,
            'errors'     => [],
            'old'        => ['quantidade' => (string) $position['quantidade_aberta']],
        ]);
    }

    public function close(): void
    {
        $this->requireAuth();
        $user = $this->currentUser();
        $uid  = (int) $user->toArray()['id'];

        $id     = $this->intParam('id');
        $contractId = $this->input('contrato_id') ?? '';

        if ($id === null || ctype_digit((string) $contractId) === false) {
            $this->notFound();
        }

        $position  = Position::findById($id);
        if ($position === null || (int)$position['usuario_id'] !== $uid || $position['status'] !== 'ABERTA') {
            $this->notFound();
        }

        $data = [
            'quantidade' => $this->input('quantidade') ?? '',
            'preco'      => $this->input('preco') ?? '',
        ];

        $abertaAtual     = (int)$position['quantidade_aberta'];
        $errors = Validator::make($data, [
            'quantidade' => ['required', 'positive'],
            'preco'      => ['required', 'numeric', 'positive'],
        ])->errors();

        if (!isset($errors['quantidade']) && ((int)(float)$data['quantidade']) > $abertaAtual) {
            $errors['quantidade'] = "A quantidade de fechamento ({$data['quantidade']}) supera a posição aberta ({$abertaAtual}).";
        }

        if ($errors !== []) {
            $this->storeOldInput($data);
            $this->view('positions/close-form', [
                'pageTitle'  => 'Fechar posição - Opções B3',
                'activeNav'  => 'posicoes',
                'position'   => $position,
                'errors'     => $errors,
                'old'        => $data,
            ]);
            return;
        }

        TransactionController::recordSale(
            $uid,
            (int) $contractId,
            (int)(float) $data['quantidade'],
            (float) $data['preco'],
            0.0,
            date('Y-m-d H:i:s')
        );

        Session::flash('success', 'Posição fechada com sucesso! Venda registrada no histórico.');
        $this->redirect('/posicoes');
    }
}
