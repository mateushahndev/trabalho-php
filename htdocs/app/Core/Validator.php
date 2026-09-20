<?php
declare(strict_types=1);

namespace App\Core;

final class Validator
{
    private array $errors = [];

    public function __construct(
        private readonly array $data,
        private readonly array $rules,
    ) {}

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function errors(): array
    {
        if ($this->errors !== []) {
            return $this->errors;
        }
        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            $label = $this->label($field);
            foreach ((array) $fieldRules as $ruleDef) {
                [$name, $param] = array_pad(explode(':', $ruleDef, 2), 2, null);
                $msg = $this->check($name, $param, $value, $label);
                if ($msg !== null) {
                    $this->errors[$field] = $msg;
                    break; // one error per field
                }
            }
        }
        return $this->errors;
    }

    public function passes(): bool
    {
        return $this->errors() === [];
    }

    private function check(string $name, ?string $param, mixed $value, string $label): ?string
    {
        $blank = $value === null || (is_string($value) && trim($value) === '');

        switch ($name) {
            case 'required':
                if ($blank) return "O campo '{$label}' é obrigatório.";
                break;
            case 'email':
                if (!$blank && !filter_var(trim((string) $value), FILTER_VALIDATE_EMAIL)) {
                    return "E-mail inválido para '{$label}'.\n";
                }
                break;
            case 'min':
                if (!$blank && strlen((string) $value) < (int) $param) {
                    return "'{$label}' deve ter no mínimo {$param} caracteres.\n";
                }
                break;
            case 'max':
                if (!$blank && strlen((string) $value) > (int) $param) {
                    return "'{$label}' deve ter no máximo {$param} caracteres.\n";
                }
                break;
            case 'numeric':
                if (!$blank && !is_numeric($value)) {
                    return "'{$label}' deve ser um número válido.\n";
                }
                break;
            case 'int':
                if (!$blank && !ctype_digit((string) $value)) {
                    return "'{$label}' deve ser um número inteiro.\n";
                }
                break;
            case 'positive':
                if (!$blank && (float) $value <= 0) {
                    return "'{$label}' deve ser maior que zero.\n";
                }
                break;
            case 'gte':
                if (!$blank && (float) $value < (float) $param) {
                    return "'{$label}' deve ser no mínimo {$param}.\n";
                }
                break;
            case 'date':
                if (!$blank) {
                    $dt = \DateTime::createFromFormat('Y-m-d', (string) $value);
                    if ($dt === false || $dt->format('Y-m-d') !== (string) $value) {
                        return "'{$label}' deve estar no formato AAAA-MM-DD.\n";
                    }
                }
                break;
            case 'datetime_local':
                if (!$blank) {
                    $parts = ['Y-m-d\TH:i:s', 'Y-m-d\TH:i'];
                    $ok = false;
                    foreach ($parts as $fmt) {
                        $dt = \DateTime::createFromFormat($fmt, (string) $value);
                        if ($dt !== false) {
                            if (\DateTime::createFromFormat($fmt, (string) $value)?->format($fmt) === (string) $value) {
                                    $ok = true;
                                    break 2; // quebra o foreach e o switch
                                }
                        }
                    }
                    if (!$ok) return "'{$label}' deve estar no formato AAAA-MM-DDTHH:MM.\n";
                }
                break;
            case 'in':
                $options = array_map('trim', explode(',', (string) $param));
                if (!$blank && !in_array(trim((string) $value), $options, true)) {
                    return "'{$label}' deve ser um dos valores: " . implode(', ', $options) . ".\n";
                }
                break;
        }
        return null;
    }

    private function label(string $field): string
    {
        $map = [
            'nome'             => 'Nome',
            'email'            => 'E-mail',
            'senha'            => 'Senha',
            'senha_confirmacao'=> 'Confirmação de senha',
            'ativo'            => 'Ativo subjacente',
            'tipo_opcao'       => 'Tipo de opção',
            'preco_exercicio'  => 'Preço exercício (strike)',
            'data_vencto'      => 'Data de vencimento',
            'preco_atual'      => 'Preço atual de mercado',
            'contrato_id'      => 'Contrato',
            'quantidade'       => 'Quantidade',
            'preco_medio'      => 'Preço médio',
            'operacao'         => 'Operação',
            'preco'            => 'Preço',
            'comissao'         => 'Comissão',
            'data_transacao'   => 'Data/hora da transação',
        ];
        // se null no mapeamento, descapitaliza e recapitaliza as primeiras letra de $field
        return $map[$field] ?? ucfirst(mb_strtolower($field));
    }
}
