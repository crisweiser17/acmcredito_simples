<?php
namespace App\Services;

use App\Database\Connection;

class ContractService {
  public static function gerarContratoHTML(int $loan_id): string {
    $pdo = Connection::get();
    $sql = 'SELECT l.*, c.*, (SELECT valor FROM config WHERE chave=\'empresa_nome\') AS empresa_nome, (SELECT valor FROM config WHERE chave=\'empresa_cnpj\') AS empresa_cnpj, (SELECT valor FROM config WHERE chave=\'empresa_endereco\') AS empresa_endereco, (SELECT valor FROM config WHERE chave=\'multa_percentual\') AS multa_percentual, (SELECT valor FROM config WHERE chave=\'juros_mora_percentual_dia\') AS juros_mora_percentual_dia FROM loans l JOIN clients c ON l.client_id=c.id WHERE l.id=:id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$loan_id]);
    $data = $stmt->fetch();
    $stmtp = $pdo->prepare('SELECT * FROM loan_parcelas WHERE loan_id=:id ORDER BY numero_parcela');
    $stmtp->execute(['id'=>$loan_id]);
    $parcelas = $stmtp->fetchAll();
    $tabela = '';
    foreach ($parcelas as $p) {
      $tabela .= '<tr><td>'.$p['numero_parcela'].'</td><td>'.date('d/m/Y', strtotime($p['data_vencimento'])).'</td><td>R$ '.number_format((float)$p['valor'],2,',','.').'</td><td>R$ '.number_format((float)$p['juros_embutido'],2,',','.').'</td><td>R$ '.number_format((float)$p['amortizacao'],2,',','.').'</td><td>R$ '.number_format((float)$p['saldo_devedor'],2,',','.').'</td></tr>';
    }
    $template = file_get_contents(dirname(__DIR__,2).'/templates/contrato.html');
    $place = [
      '{{LOAN_ID}}' => str_pad((string)$loan_id,6,'0',STR_PAD_LEFT),
      '{{EMPRESA_NOME}}' => $data['empresa_nome'] ?? '',
      '{{EMPRESA_CNPJ}}' => $data['empresa_cnpj'] ?? '',
      '{{EMPRESA_ENDERECO}}' => $data['empresa_endereco'] ?? '',
      '{{CLIENTE_NOME}}' => $data['nome'] ?? '',
      '{{CLIENTE_CPF}}' => $data['cpf'] ?? '',
      '{{CLIENTE_NASCIMENTO}}' => date('d/m/Y', strtotime($data['data_nascimento'])),
      '{{CLIENTE_ENDERECO}}' => ($data['endereco'] ?? '').', '.($data['numero'] ?? '').', '.($data['cidade'] ?? '').'/'.($data['estado'] ?? ''),
      '{{VALOR_PRINCIPAL}}' => number_format((float)$data['valor_principal'],2,',','.'),
      '{{NUM_PARCELAS}}' => (string)$data['num_parcelas'],
      '{{VALOR_PARCELA}}' => number_format((float)$data['valor_parcela'],2,',','.'),
      '{{TAXA_JUROS}}' => number_format((float)$data['taxa_juros_mensal'],2,',','.'),
      '{{CET}}' => number_format((float)$data['cet_percentual'],2,',','.'),
      '{{VALOR_TOTAL}}' => number_format((float)$data['valor_total'],2,',','.'),
      '{{TOTAL_JUROS}}' => number_format((float)$data['total_juros'],2,',','.'),
      '{{DATA_PRIMEIRO_VENCIMENTO}}' => date('d/m/Y', strtotime($data['data_primeiro_vencimento'])),
      '{{TABELA_PARCELAS}}' => $tabela,
      '{{MULTA_PERCENTUAL}}' => (string)($data['multa_percentual'] ?? ''),
      '{{JUROS_MORA_DIA}}' => (string)($data['juros_mora_percentual_dia'] ?? ''),
      '{{CIDADE}}' => (string)($data['cidade'] ?? ''),
      '{{DATA_CONTRATO}}' => date('d/m/Y')
    ];
    return str_replace(array_keys($place), array_values($place), $template);
  }
}