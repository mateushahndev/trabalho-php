<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Contract;
use App\Models\Position;
use App\Models\Transaction;

final class TransactionController extends Controller
{
    public static function recordSale(int $userId, int $contractId, int $quantidade, float $preco, float $comissao, string $dataTransacao): void
    {
        Transaction::record($userId, $contractId, 'VENDA', $quantidade, $preco, $comissao, $dataTransacao);
    }

    public function index(): void
    {
        $this->requireAuth();
        $user   = $this->currentUser();
        $uid    = (int) $user->toArray()['id'];

        $filterOp = isset($_GET['operacao']) ? strtoupper((string)$_GET['operacao']) : null;

        $transactions = Transaction::forUser($uid, $filterOp);

        $this->view('transactions/index', [
            'pageTitle'    => 'Transações - Opções B3',
            'activeNav'    => 'transacoes',
            'transactions' => $transactions,
            'filterOp'     => $filterOp ?? '',
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $contracts = Contract::all();
        $now       = date('Y-m-d H:i:s');

        $this->view('transactions/form', [
            'pageTitle'   => 'Nova transação · Opções B3',
            'activeNav'   => 'transacoes',
            'contracts'   => $contracts,
            'errors'      => [],
            'old'         => [
                'contrato_id'     => '',
                'operacao'        => 'COMPRA',
                'quantidade'      => '',
                'preco'           => '',
                'comissao'        => '0',
                'data_transacao'  => $now,
            ],
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $user  = $this->currentUser();
        $uid   = (int) $user->toArray()['id'];

        $data = [
            'contrato_id'     => $this->input('contrato_id') ?? '',
            'operacao'        => strtoupper($this->input('operacao') ?? 'COMPRA'),
            'quantidade'      => $this->input('quantidade') ?? '',
            'preco'           => $this->input('preco') ?? '',
            'comissao'        => $this->input('comissao') ?? '0',
            'data_transacao'  => $this->input('data_transacao') ?? date('Y-m-d H:i:s'),
        ];

        // T ao invés de espaço para datetime_local
        if (str_contains((string)$data['data_transacao'], ' ') && !str_contains((string)$data['data_transacao'], 'T')) {
            $data['data_transacao'] = str_replace(' ', 'T', (string)$data['data_transacao']);
        }

        // Validar que o contrato existe
        $contractValid = ctype_digit((string) $data['contrato_id'])
            && Contract::findById((int) $data['contrato_id']) !== null;
        if (!$contractValid) {
            $data['_bad'] = true;
        }

        $errors = Validator::make($data, [
            'contrato_id'     => ['required', 'int'],
            'operacao'        => ['required', 'in:COMPRA,VENDA'],
            'quantidade'      => ['required', 'positive'],
            'preco'           => ['required', 'numeric', 'gte:0'],
            'comissao'        => ['required', 'numeric', 'gte:0'],
            'data_transacao'  => ['required', 'datetime_local'],
        ])->errors();

        if (!$contractValid) {
            $errors['contrato_id'] = 'Selecione um contrato válido.';
        }

        // Venda não pode exceder quantidade em aberto
        if (!isset($errors['quantidade']) && $data['operacao'] === 'VENDA') {
            $open = Position::findOpen($uid, (int) $data['contrato_id']);
            $abertaQtd = $open ? (int) $open['quantidade_aberta'] : 0;
            if ($open === null) {
                $errors['quantidade'] = 'Você não possui posição aberta para este contrato.';
            } elseif ((int)(float)$data['quantidade'] > $abertaQtd) {
                $errors['quantidade'] = "A quantidade de venda ({$data['quantidade']}) supera a posição abierta ({\\$abertaQtd}).";
            }
        }

        if ($errors !== []) {
            $this->storeOldInput($data);
            $this->view('transactions/form', [
                'pageTitle'   => 'Nova transação - Opções B3',
                'activeNav'   => 'transacoes',
                'contracts'   => Contract::all(),
                'errors'      => $errors,
                'old'         => $data,
            ]);
            return;
        }

        // Tempo SQL
        $dtStr = (string) $data['data_transacao'];
        foreach (['Y-m-d\TH:i:s', 'Y-m-d\TH:i'] as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $dtStr);
            if ($dt !== false) {
                $sqlDt = $dt->format('Y-m-d H:i:s');
                break;
            }
        }
        if (!isset($sqlDt)) {
            $errors['data_transacao'] = 'Formato de data inválido.';
        }
        // Re-exibe tela se conversão de formato de data/hora falhar
        if (isset($errors['data_transacao'])) {
            $this->storeOldInput($data);
            $this->view('transactions/form', [
                'pageTitle'   => 'Nova transação · Opções B3',
                'activeNav'   => 'transacoes',
                'contracts'   => Contract::all(),
                'errors'      => $errors,
                'old'         => $data,
            ]);
            return;
        }

        try {
            Transaction::record(
                $uid,
                (int) $data['contrato_id'],
                $data['operacao'],
                (int)(float) $data['quantidade'],
                (float) $data['preco'],
                (float) $data['comissao'],
                $sqlDt
            );
            Session::flash('success', 'Transação registrada com sucesso!');
        } catch (\DomainException $e) {
            $errors['quantidade'] = $e->getMessage();
            $this->storeOldInput($data);
            $this->view('transactions/form', [
                'pageTitle'   => 'Nova transação - Opções B3',
                'activeNav'   => 'transacoes',
                'contracts'   => Contract::all(),
                'errors'      => $errors,
                'old'         => $data,
            ]);
            return;
        } catch (\Throwable $e) {
            Session::flash('error', 'Erro inesperado ao registrar a transação.');
            $this->redirect('/transacoes');
            return;
        }

        $this->redirect('/transacoes');
    }
}
