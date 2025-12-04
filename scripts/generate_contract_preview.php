<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

// Obtain DB connection
$pdo = \App\Database\Connection::get();
$root = dirname(__DIR__);

// Find a client with all three documents and files existing
$sql = "SELECT id, nome, doc_cnh_frente, doc_cnh_verso, doc_selfie FROM clients 
        WHERE (deleted_at IS NULL) AND (COALESCE(is_draft,0)=0)
          AND prova_vida_status='aprovado' AND cpf_check_status='aprovado'
          AND doc_cnh_frente IS NOT NULL AND doc_cnh_frente<>''
          AND doc_cnh_verso IS NOT NULL AND doc_cnh_verso<>''
          AND doc_selfie IS NOT NULL AND doc_selfie<>''
        ORDER BY updated_at DESC";
$rows = $pdo->query($sql)->fetchAll();
$cid = null; $chosen = null;
foreach ($rows as $r) {
  $f = $root . '/' . ltrim((string)$r['doc_cnh_frente'], '/');
  $v = $root . '/' . ltrim((string)$r['doc_cnh_verso'], '/');
  $s = $root . '/' . ltrim((string)$r['doc_selfie'], '/');
  if (file_exists($f) && file_exists($v) && file_exists($s)) { $cid = (int)$r['id']; $chosen = $r; break; }
}
if (!$cid) { echo "NO_CLIENT_WITH_DOCS\n"; exit(1); }

// Insert a simple loan and generate contract+link
$valor = 1000.00; $parcelas = 2; $taxa = 2.5; $primeiro = date('Y-m-d', strtotime('+10 days'));
$im = $taxa/100.0; $PMT = round(($valor*($im)*pow(1+$im,$parcelas))/(pow(1+$im,$parcelas)-1),2);
$valorTotal = round($PMT*$parcelas,2); $totalJuros = round($valorTotal-$valor,2);
$stmt = $pdo->prepare('INSERT INTO loans (client_id, valor_principal, num_parcelas, taxa_juros_mensal, valor_parcela, valor_total, total_juros, data_primeiro_vencimento, status) VALUES (:client_id,:valor_principal,:num_parcelas,:taxa_juros_mensal,:valor_parcela,:valor_total,:total_juros,:data_primeiro_vencimento,\'calculado\')');
$stmt->execute([
  'client_id'=>$cid,
  'valor_principal'=>$valor,
  'num_parcelas'=>$parcelas,
  'taxa_juros_mensal'=>$taxa,
  'valor_parcela'=>$PMT,
  'valor_total'=>$valorTotal,
  'total_juros'=>$totalJuros,
  'data_primeiro_vencimento'=>$primeiro
]);
$loan_id = (int)$pdo->lastInsertId();
\App\Controllers\LoansController::contratoELink($loan_id);
$row = $pdo->prepare('SELECT contrato_token FROM loans WHERE id=:id'); $row->execute(['id'=>$loan_id]); $token = (string)$row->fetchColumn();
echo "CLIENT_ID=$cid\nLOAN_ID=$loan_id\nTOKEN=$token\nURL=http://localhost:8000/assinar/$token\n";