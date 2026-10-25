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
    /**
     * Shared static method called from PositionController::close() and here.
     */
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
        if ($filterOp !== null && !in_array($filterOp, ['COMPRA', 'VENDA'], true)) {
            $filterOp = null;
        }

        $perPage  = max(1, (int) config('pagination.transactions_per_page', 20));
        $total    = Transaction::countForUser($uid, $filterOp);
        $pages    = max(1, intdiv($total + $perPage - 1, $perPage));
        $page     = $this->page($pages);

        $transactions = Transaction::forUser($uid, $filterOp, $perPage, ($page - 1) * $perPage);

        $base = '/transacoes' . ($filterOp !== null ? '?operacao=' . $filterOp : '');
        $pagerHref = static fn (int $p): string => $base . ($filterOp !== null ? '&' : '?') . 'page=' . $p;

        $this->view('transactions/index', [
            'pageTitle'    => 'Transações - Opções B3',
            'activeNav'    => 'transacoes',
            'transactions' => $transactions,
            'filterOp'     => $filterOp ?? '',
            'pagination'   => [
                'page'     => $page,
                'pages'    => $pages,
                'per_page' => $perPage,
                'total'    => $total,
            ],
            'pagerHref'    => $pagerHref,
            'layout'       => 'layouts/main',
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $contracts = Contract::all();
        $now       = date('Y-m-d H:i:s');

        $this->view('transactions/form', [
            'pageTitle'   => 'Nova transação - Opções B3',
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
            'layout'        => 'layouts/main',
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

        if (str_contains((string)$data['data_transacao'], ' ') && !str_contains((string)$data['data_transacao'], 'T')) {
            $data['data_transacao'] = str_replace(' ', 'T', (string)$data['data_transacao']);
        }

        $contractValid = ctype_digit((string) $data['contrato_id'])
            && Contract::findById((int) $data['contrato_id']) !== null;

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

        if (!isset($errors['quantidade']) && $data['operacao'] === 'VENDA') {
            $open = Position::findOpen($uid, (int) $data['contrato_id']);
            $abertaQtd = $open ? (int) $open['quantidade_aberta'] : 0;
            if ($open === null) {
                $errors['quantidade'] = 'Você não possui posição aberta para este contrato.';
            } elseif ((int)(float)$data['quantidade'] > $abertaQtd) {
                $errors['quantidade'] = "A quantidade de venda ({$data['quantidade']}) supera a posição aberta ({$abertaQtd}).";
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
                'layout'        => 'layouts/main',
            ]);
            return;
        }

        $dtStr = (string) $data['data_transacao'];
        $sqlDt = null;
        foreach (['Y-m-d\TH:i:s', 'Y-m-d\TH:i'] as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $dtStr);
            if ($dt !== false) {
                $sqlDt = $dt->format('Y-m-d H:i:s');
                break;
            }
        }
        if ($sqlDt === null) {
            $errors['data_transacao'] = 'Formato de data inválido.';
        }
        if (isset($errors['data_transacao'])) {
            $this->storeOldInput($data);
            $this->view('transactions/form', [
                'pageTitle'   => 'Nova transação - Opções B3',
                'activeNav'   => 'transacoes',
                'contracts'   => Contract::all(),
                'errors'      => $errors,
                'old'         => $data,
                'layout'        => 'layouts/main',
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
                'layout'        => 'layouts/main',
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
