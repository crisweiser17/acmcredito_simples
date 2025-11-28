<?php
namespace App\Services;

use App\Helpers\ConfigRepo;

class MessagingService {
  public static function render(string $tipo, array $loan, array $parcelas): string {
    $empresaRazao = ConfigRepo::get('empresa_razao_social', 'ACM Empresa Simples de Crédito');
    $empresaCnpj = ConfigRepo::get('empresa_cnpj', '00.000.000/0001-00');
    $tplPath = dirname(__DIR__,2).'/templates/'.self::mapTipoToFile($tipo);
    $tpl = is_file($tplPath) ? file_get_contents($tplPath) : '';
    $lista = '';
    $hoje = date('Y-m-d');
    if ($tipo === 'confirmacao') {
      foreach ($parcelas as $p) {
        $lista .= 'Parcela '.$p['numero_parcela'].': R$ '.number_format((float)$p['valor'],2,',','.').' — vence em '.date('d/m/Y', strtotime($p['data_vencimento']))."\n";
      }
    } elseif ($tipo === 'lembrete') {
      $limite = date('Y-m-d', strtotime('+15 days'));
      $lista .= "Próximos vencimentos:\n";
      foreach ($parcelas as $p) {
        if ($p['status']==='pendente' && $p['data_vencimento'] >= $hoje && $p['data_vencimento'] <= $limite) {
          $lista .= '- Parcela '.$p['numero_parcela'].' em '.date('d/m/Y', strtotime($p['data_vencimento'])).' — R$ '.number_format((float)$p['valor'],2,',','.')."\n";
        }
      }
      $lista .= "\nParcelas em aberto:\n";
      foreach ($parcelas as $p) {
        if ($p['status']==='pendente') {
          $lista .= '- Parcela '.$p['numero_parcela'].' — vence em '.date('d/m/Y', strtotime($p['data_vencimento'])).' — R$ '.number_format((float)$p['valor'],2,',','.')."\n";
        }
      }
    } elseif ($tipo === 'cobranca') {
      foreach ($parcelas as $p) {
        if ($p['status']==='vencido') {
          $lista .= 'Parcela '.$p['numero_parcela'].' vencida em '.date('d/m/Y', strtotime($p['data_vencimento'])).' — R$ '.number_format((float)$p['valor'],2,',','.')."\n";
        }
      }
    } elseif ($tipo === 'aprovacao') {
      $lista = '';
    }
    $place = [
      '{{CLIENTE_NOME}}' => (string)($loan['nome'] ?? ''),
      '{{CLIENTE_CPF}}' => (string)($loan['cpf'] ?? ''),
      '{{VALOR_PRINCIPAL}}' => number_format((float)($loan['valor_principal'] ?? 0),2,',','.'),
      '{{NUM_PARCELAS}}' => (string)($loan['num_parcelas'] ?? ''),
      '{{VALOR_PARCELA}}' => number_format((float)($loan['valor_parcela'] ?? 0),2,',','.'),
      '{{LISTA_PARCELAS}}' => $lista,
      '{{EMPRESA_RAZAO}}' => $empresaRazao,
      '{{EMPRESA_CNPJ}}' => $empresaCnpj,
    ];
    $out = str_replace(array_keys($place), array_values($place), $tpl);
    return trim(self::toPlainText($out));
  }

  private static function mapTipoToFile(string $tipo): string {
    return match($tipo) {
      'confirmacao' => 'msg_confirmacao_financiamento.html',
      'lembrete' => 'msg_lembrete_vencimentos.html',
      'cobranca' => 'msg_cobranca_amigavel.html',
      'aprovacao' => 'msg_aprovacao.html',
      default => 'msg_confirmacao_financiamento.html'
    };
  }

  private static function toPlainText(string $html): string {
    $html = preg_replace('/<br\s*\/>|<br>/i', "\n", $html);
    return html_entity_decode(strip_tags($html));
  }
}