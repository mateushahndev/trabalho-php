<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Contract;
use App\Support\Finance\MarketData;
use App\Support\Finance\Options;
use App\Support\Finance\Online;
use App\Support\Finance\ReferenceRates;

final class ContractController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $perPage = max(1, (int) config('pagination.contracts_per_page', 10));
        $total   = Contract::countAll();
        $pages   = max(1, intdiv($total + $perPage - 1, $perPage));
        $page    = $this->page($pages);

        $contracts = Contract::all($perPage, ($page - 1) * $perPage);

        $this->view('contracts/index', [
            'pageTitle'   => 'Contratos - Opções B3',
            'activeNav'   => 'contratos',
            'contracts'   => $contracts,
            'pagination'  => [
                'page'     => $page,
                'pages'    => $pages,
                'per_page' => $perPage,
                'total'    => $total,
            ],
            'pagerHref'   => static fn (int $p): string => '/contratos?page=' . $p,
            'layout'      => 'layouts/main',
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $online            = Online::is();
        $rfr               = $online ? ReferenceRates::riskFreeRate()[0] : config('finance.risk_free_fallback', 0.14);
        $spotData          = [];
        if ($online) {
            $ativos        = array_unique(array_map(static fn($c) => (string) $c['ativo'], Contract::all()));
            foreach ($ativos as $a) {
                $closes = MarketData::closes($a, 50);
                $spotData[$a]    = $closes !== []
                    ? ['spot'  => end($closes), 'vol' => MarketData::annualizedVolatility($closes) ?? config('finance.vol_fallback', 0.35)]
                    : null;
            }
        }

        $this->view('contracts/form', [
            'pageTitle'  => 'Novo contrato - Opções B3',
            'activeNav'  => 'contratos',
            'contract'   => null,
            'errors'     => [],
            'old'        => [],
            'spotData'   => $spotData,
            'rfr'        => $rfr,
            'online'     => $online,
	    'layout'      => 'layouts/main',
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
                'pageTitle'  => 'Novo contrato - Opções B3',
                'activeNav'  => 'contratos',
                'contract'   => null,
                'errors'     => $errors,
                'old'        => $data,
	    'layout'      => 'layouts/main',
            ]);
            return;
        }

        Contract::create(
            (string) $data['ativo'],
            (string) $data['tipo_opcao'],
            (float) $data['preco_exercicio'],
            (string) $data['data_vencto'],
            (float) $data['preco_atual'],
        );

        Session::flash('success', 'Contrato cadastrado com sucesso!');
        $this->redirect('/contratos');
    }

    public function edit(): void
    {
        $this->requireAuth();
        $id       = $this->intParam('id');
        if ($id === null) {
            $this->notFound();
        }

        $contract = Contract::findById($id);
        if ($contract === null) {
            $this->notFound();
        }

        $old = [
            'ativo'           => (string) $contract['ativo'],
            'tipo_opcao'      => (string) $contract['tipo_opcao'],
            'preco_exercicio' => (string) $contract['preco_exercicio'],
            'data_vencto'     => (string) $contract['data_vencto'],
            'preco_atual'     => (string) $contract['preco_atual'],
        ];

        $online            = Online::is();
        $rfr               = $online ? ReferenceRates::riskFreeRate()[0] : config('finance.risk_free_fallback', 0.14);

        $analysis      = null;
        $spot          = null;
        $vol           = null;
        if ($online) {
            $ativo  = (string) $contract['ativo'];
            $closes = MarketData::closes($ativo, 252);
            if ($closes !== []) {
                $spot     = end($closes);
                $vol      = MarketData::annualizedVolatility($closes) ?? config('finance.vol_fallback', 0.35);
                $analysis = Options::analyze((array) $contract, $spot, $vol, $rfr);
            }
        }

        $spotData = ($spot !== null && $vol !== null && isset($old['ativo']))
            ? [$old['ativo'] => ['spot' => $spot, 'vol' => $vol]]
            : [];

        $this->view('contracts/form', [
            'pageTitle'  => 'Editar contrato - Opções B3',
            'activeNav'  => 'contratos',
            'contract'   => $contract,
            'errors'     => [],
            'old'        => $old,
            'spotData'   => $spotData,
            'rfr'        => $rfr,
            'analysis'   => $analysis,
            'online'     => $online,
	    'layout'      => 'layouts/main',
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $id       = $this->intParam('id');
        if ($id === null) {
            $this->notFound();
        }

        if (Contract::findById($id) === null) {
            $this->notFound();
        }

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
                'pageTitle'  => 'Editar contrato · Opções B3',
                'activeNav'  => 'contratos',
                'contract'   => Contract::findById($id),
                'errors'     => $errors,
                'old'        => $data,
	        'layout'      => 'layouts/main',
            ]);
            return;
        }

        Contract::update(
            $id,
            (string) $data['ativo'],
            (string) $data['tipo_opcao'],
            (float) $data['preco_exercicio'],
            (string) $data['data_vencto'],
            (float) $data['preco_atual'],
        );

        Session::flash('success', 'Contrato atualizado com sucesso!');
        $this->redirect('/contratos');
    }

    public function preview(): void
    {
        $ativo   = $this->input('ativo') ?? '';
        $tipo    = strtoupper($this->input('tipo') ?? 'CALL');
        $strike  = (float) ($this->input('strike') ?? 0);
        $vencto  = $this->input('vencto') ?? '';
        $merc    = (float) ($this->input('merc') ?? 0);
        $spotIn  = $this->input('spot') ?? '';
        $volIn   = $this->input('vol') ?? '';

        $spot = $spotIn !== '' && is_numeric($spotIn) && (float) $spotIn > 0
            ? (float) $spotIn
            : ($ativo !== '' ? (MarketData::spot($ativo) ?? null) : null);

        $vol = $volIn !== '' && is_numeric($volIn) && (float) $volIn > 0
            ? (float) $volIn / 100
            : ($ativo !== ''
                ? MarketData::annualizedVolatility(MarketData::closes($ativo, 252)) 
                : config('finance.vol_fallback', 0.35));

        $rfr = config('finance.risk_free_fallback', 0.14);

        if ($strike <= 0) {
            $html = '<p class="empty">Preencha pelo menos o strike para ver a análise.</p>';
        } elseif ($spot === null || $spot <= 0) {
            $html = '<p class="empty">Insira o spot (preço do ativo) para calcular a análise BS ou deixe o campo vazio para usar o preço de mercado.</p>';
        } else {
            $contract = [
                'tipo_opcao'      => $tipo,
                'preco_exercicio' => $strike,
                'data_vencto'     => $vencto,
                'preco_atual'     => $merc,
            ];
            $analysis = Options::analyze($contract, $spot, $vol, $rfr);
            $html     = $analysis !== null
                ? $this->bsSnippet($analysis)
                : '<p class="empty">Não foi possível calcular a análise BS. Verifique os parâmetros.</p>';
        }

        $this->view('contracts/preview-bs', [
            'html'   => $html,
        ]);
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

    private function bsSnippet(array $a): string
    {
        $g = $a['greeks'];
        $iv = $a['volatility'] !== null ? round($a['volatility'] * 100, 2) . '%' : '—';
        $dias  = round($a['time_to_expiry'] * 365, 0);

        return '<div id="bs-preview" class="bs-panel">'
            . '<div class="bs-table"><div class="bs-row bs-head"><span class="bs-label">Preço Teórico (BS)</span><span class="bs-value">R$ ' . e($a['theoretical_price']) . '</span></div>'
            . '<div class="bs-row"><span class="bs-label">Delta</span><span class="bs-value">' . e($g['delta']) . '</span></div>'
            . '<div class="bs-row"><span class="bs-label">Gamma</span><span class="bs-value">' . e($g['gamma']) . '</span></div>'
            . '<div class="bs-row"><span class="bs-label">Vega</span><span class="bs-value">' . e($g['vega']) . '</span></div>'
            . '<div class="bs-row"><span class="bs-label">Theta</span><span class="bs-value">' . e($g['theta']) . '</span></div>'
            . '<div class="bs-row"><span class="bs-label">Vol. Implícita</span><span class="bs-value">' . e($iv) . '</span></div>'
            . '<div class="bs-row"><span class="bs-label">Prazo</span><span class="bs-value">' . e((string) $dias) . ' dias</span></div>'
            . '</div></div>';
    }
}
