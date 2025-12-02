<?php
namespace App\Controllers;

use App\Database\Connection;
use App\Helpers\Upload;
use App\Helpers\Audit;
use App\Helpers\ConfigRepo;

class ClientesController {
  private static function parseRenda($val): float {
    $s = trim((string)$val);
    if ($s === '') return 0.0;
    $s = preg_replace('/[^\d,\.]/', '', $s);
    $hasComma = strpos($s, ',') !== false;
    $hasDot = strpos($s, '.') !== false;
    if ($hasComma && $hasDot) {
      $lastComma = strrpos($s, ',');
      $lastDot = strrpos($s, '.');
      if ($lastDot !== false && $lastComma !== false && $lastDot > $lastComma) {
        $s = str_replace(',', '', $s);
      } else {
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
      }
    } elseif ($hasComma) {
      $s = str_replace(',', '.', $s);
    } else {
      $s = $s;
    }
    $f = (float)$s;
    if ($f > 99999999.99) $f = 99999999.99;
    if ($f < 0) $f = 0.0;
    return round($f, 2);
  }
  private static function validarENormalizarPix(string $tipo, string $chave, string $cpfNorm): array {
    $t = strtolower(trim($tipo));
    $c = trim($chave);
    if ($t === 'cpf') {
      $n = preg_replace('/\D/', '', $cpfNorm);
      return [strlen($n) === 11, $n, strlen($n) === 11 ? null : 'CPF inválido'];
    }
    if ($t === 'email') {
      $ok = filter_var($c, FILTER_VALIDATE_EMAIL) !== false;
      return [$ok, $ok ? $c : null, $ok ? null : 'Email inválido'];
    }
    if ($t === 'telefone') {
      $n = preg_replace('/\D/', '', $c);
      $ok = (strlen($n) === 10 || strlen($n) === 11);
      return [$ok, $ok ? $n : null, $ok ? null : 'Telefone inválido'];
    }
    if ($t === 'aleatoria') {
      $low = strtolower($c);
      $ok = (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $low) || preg_match('/^[0-9a-f]{32}$/i', $low));
      return [$ok, $ok ? $low : null, $ok ? null : 'Chave aleatória inválida'];
    }
    return [false, null, 'Tipo de chave PIX inválido'];
  }
  public static function novo(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (empty($_FILES['holerites']['name'][0])) {
        $error = 'É necessário enviar pelo menos um holerite.';
        $title = 'Novo Cliente';
        $content = __DIR__ . '/../Views/clientes_novo.php';
        include __DIR__ . '/../Views/layout.php';
        return;
      }
      $cnhUnico = isset($_POST['cnh_arquivo_unico']);
      $hasUnico = !empty($_FILES['cnh_unico']['name'] ?? '');
      $hasFrente = !empty($_FILES['cnh_frente']['name'] ?? '');
      $hasVerso = !empty($_FILES['cnh_verso']['name'] ?? '');
      $hasSelfie = !empty($_FILES['selfie']['name'] ?? '');
      $okCnh = $cnhUnico ? $hasUnico : ($hasFrente && $hasVerso);
      if (!$okCnh || !$hasSelfie) {
        $error = 'É necessário enviar CNH/RG (frente e verso ou arquivo único) e Selfie.';
        $title = 'Novo Cliente';
        $content = __DIR__ . '/../Views/clientes_novo.php';
        include __DIR__ . '/../Views/layout.php';
        return;
      }
      $pdo = Connection::get();
      $pixTipo = trim((string)($_POST['pix_tipo'] ?? ''));
      $pixChaveIn = trim((string)($_POST['pix_chave'] ?? ''));
      [$pixOk, $pixNorm, $pixErr] = self::validarENormalizarPix($pixTipo, $pixChaveIn, preg_replace('/\D/', '', (string)($_POST['cpf'] ?? '')));
      if (!$pixOk) {
        $error = $pixErr ?: 'Chave PIX inválida.';
        $title = 'Novo Cliente';
        $content = __DIR__ . '/../Views/clientes_novo.php';
        include __DIR__ . '/../Views/layout.php';
        return;
      }
      $cpfNorm = preg_replace('/\D/', '', (string)($_POST['cpf'] ?? ''));
      $dupRow = null; $dupStmt = $pdo->prepare("SELECT * FROM clients WHERE REPLACE(REPLACE(REPLACE(cpf,'.',''),'-',''),' ','') = :cpf"); $dupStmt->execute(['cpf'=>$cpfNorm]); $dupRow = $dupStmt->fetch();
      if ($dupRow && (int)($dupRow['is_draft'] ?? 0) === 0) {
        $error = 'CPF já cadastrado.';
        $title = 'Novo Cliente';
        $content = __DIR__ . '/../Views/clientes_novo.php';
        include __DIR__ . '/../Views/layout.php';
        return;
      }
      if ($dupRow && (int)($dupRow['is_draft'] ?? 0) === 1) {
        $clientId = (int)$dupRow['id'];
        $pdo->prepare('UPDATE clients SET is_draft=0, nome=:nome, data_nascimento=:data_nascimento, email=:email, telefone=:telefone, cep=:cep, endereco=:endereco, numero=:numero, complemento=:complemento, bairro=:bairro, cidade=:cidade, estado=:estado, ocupacao=:ocupacao, tempo_trabalho=:tempo_trabalho, renda_mensal=:renda_mensal, cnh_arquivo_unico=:cnh_arquivo_unico, observacoes=:observacoes, pix_tipo=:pix_tipo, pix_chave=:pix_chave WHERE id=:id')
            ->execute([
              'nome' => (function($n){ $n = trim((string)$n); if ($n==='') return $n; if (function_exists('mb_strtoupper')) { return mb_strtoupper($n, 'UTF-8'); } return strtoupper($n); })(($_POST['nome'] ?? '')),
              'data_nascimento' => trim($_POST['data_nascimento'] ?? ''),
              'email' => trim($_POST['email'] ?? ''),
              'telefone' => trim($_POST['telefone'] ?? ''),
              'cep' => trim($_POST['cep'] ?? ''),
              'endereco' => trim($_POST['endereco'] ?? ''),
              'numero' => trim($_POST['numero'] ?? ''),
              'complemento' => trim($_POST['complemento'] ?? ''),
              'bairro' => trim($_POST['bairro'] ?? ''),
              'cidade' => trim($_POST['cidade'] ?? ''),
              'estado' => trim($_POST['estado'] ?? ''),
              'ocupacao' => trim($_POST['ocupacao'] ?? ''),
              'tempo_trabalho' => trim($_POST['tempo_trabalho'] ?? ''),
              'renda_mensal' => self::parseRenda($_POST['renda_mensal'] ?? '0'),
              'cnh_arquivo_unico' => isset($_POST['cnh_arquivo_unico']) ? 1 : 0,
              'observacoes' => trim($_POST['observacoes'] ?? ''),
              'pix_tipo' => strtolower($pixTipo),
              'pix_chave' => $pixNorm,
              'id' => $clientId
            ]);
      } else {
        $stmt = $pdo->prepare('INSERT INTO clients (nome, cpf, data_nascimento, email, telefone, cep, endereco, numero, complemento, bairro, cidade, estado, ocupacao, tempo_trabalho, renda_mensal, cnh_arquivo_unico, observacoes, pix_tipo, pix_chave, cadastro_publico) VALUES (:nome,:cpf,:data_nascimento,:email,:telefone,:cep,:endereco,:numero,:complemento,:bairro,:cidade,:estado,:ocupacao,:tempo_trabalho,:renda_mensal,:cnh_arquivo_unico,:observacoes,:pix_tipo,:pix_chave,:cadastro_publico)');
        $stmt->execute([
          'nome' => (function($n){ $n = trim((string)$n); if ($n==='') return $n; if (function_exists('mb_strtoupper')) { return mb_strtoupper($n, 'UTF-8'); } return strtoupper($n); })(($_POST['nome'] ?? '')),
          'cpf' => $cpfNorm,
          'data_nascimento' => trim($_POST['data_nascimento'] ?? ''),
          'email' => trim($_POST['email'] ?? ''),
          'telefone' => trim($_POST['telefone'] ?? ''),
          'cep' => trim($_POST['cep'] ?? ''),
          'endereco' => trim($_POST['endereco'] ?? ''),
          'numero' => trim($_POST['numero'] ?? ''),
          'complemento' => trim($_POST['complemento'] ?? ''),
          'bairro' => trim($_POST['bairro'] ?? ''),
          'cidade' => trim($_POST['cidade'] ?? ''),
          'estado' => trim($_POST['estado'] ?? ''),
          'ocupacao' => trim($_POST['ocupacao'] ?? ''),
          'tempo_trabalho' => trim($_POST['tempo_trabalho'] ?? ''),
          'renda_mensal' => self::parseRenda($_POST['renda_mensal'] ?? '0'), 
          'cnh_arquivo_unico' => isset($_POST['cnh_arquivo_unico']) ? 1 : 0,
          'observacoes' => trim($_POST['observacoes'] ?? ''),
          'pix_tipo' => strtolower($pixTipo),
          'pix_chave' => $pixNorm
        ]);
        $clientId = (int)$pdo->lastInsertId();
      }
      $indicado = (int)($_POST['indicado_por_id'] ?? 0);
      $refN = $_POST['ref_nome'] ?? [];
      $refR = $_POST['ref_relacao'] ?? [];
      $refT = $_POST['ref_telefone'] ?? [];
      $refs = [];
      for ($i=0; $i<3; $i++) {
        $n = trim((string)($refN[$i] ?? ''));
        $rel = trim((string)($refR[$i] ?? ''));
        $t = trim((string)($refT[$i] ?? ''));
        $nUp = (function($x){ $x = trim((string)$x); if ($x==='') return $x; return function_exists('mb_strtoupper') ? mb_strtoupper($x,'UTF-8') : strtoupper($x); })($n);
        if ($n !== '' || $rel !== '' || $t !== '') { $refs[] = ['nome'=>$nUp, 'relacao'=>$rel, 'telefone'=>$t]; }
      }
      for ($i=0; $i<count($refs); $i++) { $tok = (string)($refs[$i]['token'] ?? ''); if (!preg_match('/^[a-f0-9]{64}$/', $tok)) { $refs[$i]['token'] = bin2hex(random_bytes(32)); } }
      try {
        $hasInd = $pdo->query("SHOW COLUMNS FROM clients LIKE 'indicado_por_id'")->fetch();
        $hasRefs = $pdo->query("SHOW COLUMNS FROM clients LIKE 'referencias'")->fetch();
        if ($hasInd) { $pdo->prepare('UPDATE clients SET indicado_por_id=:ind WHERE id=:id')->execute(['ind'=>$indicado?:null, 'id'=>$clientId]); }
        if ($hasRefs) { $pdo->prepare('UPDATE clients SET referencias=:refs WHERE id=:id')->execute(['refs'=>json_encode($refs), 'id'=>$clientId]); }
      } catch (\Throwable $e) { /* no-op */ }
      $holerites = [];
      if (!empty($_FILES['holerites']['name'][0])) {
        $maxHol = 5;
        $totalHol = count($_FILES['holerites']['name']);
        for ($i=0; $i<$totalHol && $i<$maxHol; $i++) {
          $file = [
            'name' => $_FILES['holerites']['name'][$i],
            'type' => $_FILES['holerites']['type'][$i],
            'tmp_name' => $_FILES['holerites']['tmp_name'][$i],
            'error' => $_FILES['holerites']['error'][$i],
            'size' => $_FILES['holerites']['size'][$i]
          ];
          try { $holerites[] = Upload::save($file, $clientId, 'holerites'); } catch (\Throwable $e) { \App\Helpers\Audit::log('upload_error','clients',$clientId,$e->getMessage()); }
        }
        $stmt = $pdo->prepare('UPDATE clients SET doc_holerites = :j WHERE id = :id');
        $stmt->execute(['j' => json_encode($holerites), 'id' => $clientId]);
      }
      if (isset($_POST['cnh_arquivo_unico'])) {
        if (!empty($_FILES['cnh_unico']['name'])) {
          try { $path = Upload::save($_FILES['cnh_unico'], $clientId, 'documentos'); $stmt = $pdo->prepare('UPDATE clients SET doc_cnh_frente = :f WHERE id = :id'); $stmt->execute(['f' => $path, 'id' => $clientId]); } catch (\Throwable $e) { \App\Helpers\Audit::log('upload_error','clients',$clientId,$e->getMessage()); }
        }
      } else {
        if (!empty($_FILES['cnh_frente']['name'])) {
          try { $path = Upload::save($_FILES['cnh_frente'], $clientId, 'documentos'); $pdo->prepare('UPDATE clients SET doc_cnh_frente = :f WHERE id = :id')->execute(['f'=>$path,'id'=>$clientId]); } catch (\Throwable $e) { \App\Helpers\Audit::log('upload_error','clients',$clientId,$e->getMessage()); }
        }
        if (!empty($_FILES['cnh_verso']['name'])) {
          try { $path = Upload::save($_FILES['cnh_verso'], $clientId, 'documentos'); $pdo->prepare('UPDATE clients SET doc_cnh_verso = :v WHERE id = :id')->execute(['v'=>$path,'id'=>$clientId]); } catch (\Throwable $e) { \App\Helpers\Audit::log('upload_error','clients',$clientId,$e->getMessage()); }
        }
      }
      if (!empty($_FILES['selfie']['name'])) {
        try { $path = Upload::save($_FILES['selfie'], $clientId, 'documentos'); $pdo->prepare('UPDATE clients SET doc_selfie = :s WHERE id = :id')->execute(['s'=>$path,'id'=>$clientId]); } catch (\Throwable $e) { \App\Helpers\Audit::log('upload_error','clients',$clientId,$e->getMessage()); }
      }
      Audit::log('create', 'clients', $clientId, 'Cliente criado');
      $createdId = $clientId;
      $createdRefs = $refs;
      $showSuccessModal = true;
      $title = 'Novo Cliente';
      $content = __DIR__ . '/../Views/clientes_novo.php';
      include __DIR__ . '/../Views/layout.php';
      return;
    }
    $title = 'Novo Cliente';
    $content = __DIR__ . '/../Views/clientes_novo.php';
    include __DIR__ . '/../Views/layout.php';
  }
  public static function cadastroPublico(): void {
    $pdo = Connection::get();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $nome = trim($_POST['nome'] ?? '');
      $cpf = trim($_POST['cpf'] ?? '');
      $cpfNorm = preg_replace('/\D/', '', (string)$cpf);
      $data_nascimento = trim($_POST['data_nascimento'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $telefone = trim($_POST['telefone'] ?? '');
      $cep = trim($_POST['cep'] ?? '');
      $endereco = trim($_POST['endereco'] ?? '');
      $numero = trim($_POST['numero'] ?? '');
      $bairro = trim($_POST['bairro'] ?? '');
      $cidade = trim($_POST['cidade'] ?? '');
      $estado = trim($_POST['estado'] ?? '');
      $ocupacao = trim($_POST['ocupacao'] ?? '');
      $tempo = trim($_POST['tempo_trabalho'] ?? '');
      $renda = self::parseRenda($_POST['renda_mensal'] ?? '0');
      $observacoes = trim($_POST['observacoes'] ?? '');
      $pixTipo = trim((string)($_POST['pix_tipo'] ?? ''));
      $pixChaveIn = trim((string)($_POST['pix_chave'] ?? ''));
      [$pixOk, $pixNorm, $pixErr] = self::validarENormalizarPix($pixTipo, $pixChaveIn, $cpfNorm);
      if ($nome === '' || $cpfNorm === '' || $data_nascimento === '' || $email === '' || $telefone === '' || $cep === '' || $endereco === '' || $numero === '' || $bairro === '' || $cidade === '' || $estado === '' || $ocupacao === '' || $tempo === '' || $renda <= 0) {
        $error = 'Preencha todos os campos obrigatórios.';
        $title = 'Cadastro de Cliente';
        $content = __DIR__ . '/../Views/cadastro_publico.php';
        include __DIR__ . '/../Views/layout.php';
        return;
      }
      if (!$pixOk) {
        $error = $pixErr ?: 'Chave PIX inválida.';
        $title = 'Cadastro de Cliente';
        $content = __DIR__ . '/../Views/cadastro_publico.php';
        include __DIR__ . '/../Views/layout.php';
        return;
      }
      $dup = $pdo->prepare("SELECT COUNT(*) AS c FROM clients WHERE REPLACE(REPLACE(REPLACE(cpf,'.',''),'-',''),' ','') = :cpf");
      $dup->execute(['cpf'=>$cpfNorm]);
      if (((int)($dup->fetch()['c'] ?? 0)) > 0) {
        $error = 'CPF já cadastrado.';
        $title = 'Cadastro de Cliente';
        $content = __DIR__ . '/../Views/cadastro_publico.php';
        include __DIR__ . '/../Views/layout.php';
        return;
      }
      $cnhUnico = isset($_POST['cnh_arquivo_unico']);
      $hasUnico = !empty($_FILES['cnh_unico']['name'] ?? '');
      $hasFrente = !empty($_FILES['cnh_frente']['name'] ?? '');
      $hasVerso = !empty($_FILES['cnh_verso']['name'] ?? '');
      $hasSelfie = !empty($_FILES['selfie']['name'] ?? '');
      $hasHol = !empty($_FILES['holerites']['name'][0] ?? '');
      $okCnh = $cnhUnico ? $hasUnico : ($hasFrente && $hasVerso);
      if (!$hasHol || !$okCnh || !$hasSelfie) {
        $error = 'Envie CNH/RG (frente e verso ou arquivo único), pelo menos um holerite e Selfie.';
        $title = 'Cadastro de Cliente';
        $content = __DIR__ . '/../Views/cadastro_publico.php';
        include __DIR__ . '/../Views/layout.php';
        return;
      }
      $stmt = $pdo->prepare('INSERT INTO clients (nome, cpf, data_nascimento, email, telefone, cep, endereco, numero, complemento, bairro, cidade, estado, ocupacao, tempo_trabalho, renda_mensal, cnh_arquivo_unico, observacoes, pix_tipo, pix_chave) VALUES (:nome,:cpf,:data_nascimento,:email,:telefone,:cep,:endereco,:numero,:complemento,:bairro,:cidade,:estado,:ocupacao,:tempo_trabalho,:renda_mensal,:cnh_arquivo_unico,:observacoes,:pix_tipo,:pix_chave)');
      $stmt->execute([
        'nome'=>(function($n){ $n = trim((string)$n); if ($n==='') return $n; if (function_exists('mb_strtoupper')) { return mb_strtoupper($n, 'UTF-8'); } return strtoupper($n); })($nome), 'cpf'=>$cpfNorm, 'data_nascimento'=>$data_nascimento, 'email'=>$email, 'telefone'=>$telefone,
        'cep'=>$cep, 'endereco'=>$endereco, 'numero'=>$numero, 'complemento'=>trim($_POST['complemento'] ?? ''), 'bairro'=>$bairro, 'cidade'=>$cidade, 'estado'=>$estado,
        'ocupacao'=>$ocupacao, 'tempo_trabalho'=>$tempo, 'renda_mensal'=>$renda, 'cnh_arquivo_unico' => $cnhUnico ? 1 : 0, 'observacoes' => $observacoes, 'pix_tipo'=>strtolower($pixTipo), 'pix_chave'=>$pixNorm, 'cadastro_publico' => 1
      ]);
      $clientId = (int)$pdo->lastInsertId();
      $refN = $_POST['ref_nome'] ?? [];
      $refR = $_POST['ref_relacao'] ?? [];
      $refT = $_POST['ref_telefone'] ?? [];
      $refs = [];
      for ($i=0; $i<3; $i++) { $n = trim((string)($refN[$i] ?? '')); $rel = trim((string)($refR[$i] ?? '')); $t = trim((string)($refT[$i] ?? '')); $nUp = (function($x){ $x = trim((string)$x); if ($x==='') return $x; return function_exists('mb_strtoupper') ? mb_strtoupper($x,'UTF-8') : strtoupper($x); })($n); if ($n !== '' || $rel !== '' || $t !== '') { $refs[] = ['nome'=>$nUp, 'relacao'=>$rel, 'telefone'=>$t]; } }
      for ($i=0; $i<count($refs); $i++) { $tok = (string)($refs[$i]['token'] ?? ''); if (!preg_match('/^[a-f0-9]{64}$/', $tok)) { $refs[$i]['token'] = bin2hex(random_bytes(32)); } }
      for ($i=0; $i<count($refs); $i++) { $tok = (string)($refs[$i]['token'] ?? ''); if (!preg_match('/^[a-f0-9]{64}$/', $tok)) { $refs[$i]['token'] = bin2hex(random_bytes(32)); } }
      try { $hasRefs = $pdo->query("SHOW COLUMNS FROM clients LIKE 'referencias'")->fetch(); if ($hasRefs) { $pdo->prepare('UPDATE clients SET referencias=:r WHERE id=:id')->execute(['r'=>json_encode($refs),'id'=>$clientId]); } } catch (\Throwable $e) {}
      $holerites = [];
      if (!empty($_FILES['holerites']['name'][0])) {
        $maxHol = 5;
        $totalHol = count($_FILES['holerites']['name']);
        for ($i=0; $i<$totalHol && $i<$maxHol; $i++) {
          $file = [
            'name' => $_FILES['holerites']['name'][$i],
            'type' => $_FILES['holerites']['type'][$i],
            'tmp_name' => $_FILES['holerites']['tmp_name'][$i],
            'error' => $_FILES['holerites']['error'][$i],
            'size' => $_FILES['holerites']['size'][$i]
          ];
          try { $holerites[] = Upload::save($file, $clientId, 'holerites'); } catch (\Throwable $e) { \App\Helpers\Audit::log('upload_error','clients',$clientId,$e->getMessage()); }
        }
        $pdo->prepare('UPDATE clients SET doc_holerites = :j WHERE id = :id')->execute(['j'=>json_encode($holerites),'id'=>$clientId]);
      }
      if ($cnhUnico) {
        if (!empty($_FILES['cnh_unico']['name'])) {
          try { $path = Upload::save($_FILES['cnh_unico'], $clientId, 'documentos'); $pdo->prepare('UPDATE clients SET doc_cnh_frente = :f WHERE id = :id')->execute(['f'=>$path,'id'=>$clientId]); } catch (\Throwable $e) { \App\Helpers\Audit::log('upload_error','clients',$clientId,$e->getMessage()); }
        }
      } else {
        if (!empty($_FILES['cnh_frente']['name'])) {
          try { $path = Upload::save($_FILES['cnh_frente'], $clientId, 'documentos'); $pdo->prepare('UPDATE clients SET doc_cnh_frente = :f WHERE id = :id')->execute(['f'=>$path,'id'=>$clientId]); } catch (\Throwable $e) { \App\Helpers\Audit::log('upload_error','clients',$clientId,$e->getMessage()); }
        }
        if (!empty($_FILES['cnh_verso']['name'])) {
          try { $path = Upload::save($_FILES['cnh_verso'], $clientId, 'documentos'); $pdo->prepare('UPDATE clients SET doc_cnh_verso = :v WHERE id = :id')->execute(['v'=>$path,'id'=>$clientId]); } catch (\Throwable $e) { \App\Helpers\Audit::log('upload_error','clients',$clientId,$e->getMessage()); }
        }
      }
      if (!empty($_FILES['selfie']['name'])) {
        try { $path = Upload::save($_FILES['selfie'], $clientId, 'documentos'); $pdo->prepare('UPDATE clients SET doc_selfie = :s WHERE id = :id')->execute(['s'=>$path,'id'=>$clientId]); } catch (\Throwable $e) { \App\Helpers\Audit::log('upload_error','clients',$clientId,$e->getMessage()); }
      }
      \App\Helpers\Audit::log('create_public','clients',$clientId,'Cadastro público');
      $_SESSION['toast'] = 'Cadastro enviado com sucesso';
      $title = 'Cadastro de Cliente';
      $content = __DIR__ . '/../Views/cadastro_publico_sucesso.php';
      include __DIR__ . '/../Views/layout.php';
      return;
    }
    $title = 'Cadastro de Cliente';
    $content = __DIR__ . '/../Views/cadastro_publico.php';
    include __DIR__ . '/../Views/layout.php';
  }
  public static function lista(): void {
    $pdo = Connection::get();
    try { $col = $pdo->query("SHOW COLUMNS FROM clients LIKE 'criterios_status'")->fetch(); if (!$col) { $pdo->exec("ALTER TABLE clients ADD COLUMN criterios_status ENUM('pendente','aprovado','reprovado') DEFAULT 'pendente', ADD COLUMN criterios_data DATETIME NULL, ADD COLUMN criterios_user_id INT NULL"); } } catch (\Throwable $e) {}
    $q = trim($_GET['q'] ?? '');
    $ini = trim($_GET['data_ini'] ?? '');
    $fim = trim($_GET['data_fim'] ?? '');
    $periodo = trim($_GET['periodo'] ?? '');
    if ($periodo !== '' && $periodo !== 'custom') {
      $today = date('Y-m-d');
      if ($periodo === 'hoje') { $ini = $today; $fim = $today; }
      elseif ($periodo === 'ultimos7') { $ini = date('Y-m-d', strtotime('-6 days')); $fim = $today; }
      elseif ($periodo === 'ultimos30') { $ini = date('Y-m-d', strtotime('-29 days')); $fim = $today; }
      elseif ($periodo === 'mes_atual') { $ini = date('Y-m-01'); $fim = $today; }
    }
    $ps = trim($_GET['prova_status'] ?? '');
    $cs = trim($_GET['cpf_status'] ?? '');
    $baseSql = 'FROM clients WHERE 1=1';
    $params = [];
    if ($q !== '') { $baseSql .= ' AND (nome LIKE :q OR cpf LIKE :q)'; $params['q'] = '%'.$q.'%'; }
    if ($ini !== '') { $baseSql .= ' AND DATE(created_at) >= :ini'; $params['ini'] = $ini; }
    if ($fim !== '') { $baseSql .= ' AND DATE(created_at) <= :fim'; $params['fim'] = $fim; }
    if ($ps !== '' && in_array($ps, ['aprovado','reprovado','pendente'], true)) { $baseSql .= ' AND prova_vida_status = :ps'; $params['ps'] = $ps; }
    if ($cs !== '' && in_array($cs, ['aprovado','reprovado','pendente'], true)) { $baseSql .= ' AND cpf_check_status = :cs'; $params['cs'] = $cs; }

    $perSel = (int)($_GET['per_page'] ?? 25);
    $allowed = [25,50,100];
    if (!in_array($perSel, $allowed, true)) { $perSel = 25; }
    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * $perSel;

    $countStmt = $pdo->prepare('SELECT COUNT(*) AS total ' . $baseSql);
    $countStmt->execute($params);
    $total = (int)($countStmt->fetch()['total'] ?? 0);

    $rowsStmt = $pdo->prepare('SELECT id, nome, cpf, prova_vida_status, cpf_check_status, criterios_status, created_at, (SELECT COUNT(*) FROM loans WHERE loans.client_id = clients.id) AS loans_count ' . $baseSql . ' ORDER BY created_at DESC LIMIT ' . (int)$perSel . ' OFFSET ' . (int)$offset);
    $rowsStmt->execute($params);
    $rows = $rowsStmt->fetchAll();
    $pagesTotal = $perSel > 0 ? max(1, (int)ceil($total / $perSel)) : 1;
    $_PAGINACAO = ['total'=>$total,'per_page'=>$perSel,'page'=>$page,'pages_total'=>$pagesTotal];
    if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
      header('Content-Type: application/json');
      echo json_encode(['rows'=>$rows,'pagination'=>$_PAGINACAO]);
      return;
    }
    $title = 'Clientes';
    $content = __DIR__ . '/../Views/clientes_lista.php';
    include __DIR__ . '/../Views/layout.php';
  }
  public static function validar(int $id): void {
    $pdo = Connection::get();
    $stmt = $pdo->prepare('SELECT * FROM clients WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $client = $stmt->fetch();
    if (!$client) { header('Location: /'); return; }
    $refs = json_decode($client['referencias'] ?? '[]', true); if (!is_array($refs)) $refs = [];
    $changedRefs = false;
    for ($i=0; $i<count($refs); $i++) {
      if (!empty($refs[$i]['nome'])) {
        $orig = (string)$refs[$i]['nome'];
        $upper = function_exists('mb_strtoupper') ? mb_strtoupper($orig,'UTF-8') : strtoupper($orig);
        if ($upper !== $orig) { $refs[$i]['nome'] = $upper; $changedRefs = true; }
      }
      if (!isset($refs[$i]['public']) || !is_array($refs[$i]['public'])) { $refs[$i]['public'] = ['status'=>'pendente']; $changedRefs = true; }
      if (!isset($refs[$i]['operador']) || !is_array($refs[$i]['operador'])) { $refs[$i]['operador'] = ['status'=>'pendente']; $changedRefs = true; }
      if (!isset($refs[$i]['token']) || !preg_match('/^[a-f0-9]{12,64}$/', (string)($refs[$i]['token'] ?? ''))) { $refs[$i]['token'] = bin2hex(random_bytes(6)); $changedRefs = true; }
    }
    if ($changedRefs) { $pdo->prepare('UPDATE clients SET referencias=:r WHERE id=:id')->execute(['r'=>json_encode($refs), 'id'=>$id]); $client['referencias'] = json_encode($refs); }
    $refs = json_decode($client['referencias'] ?? '[]', true); if (!is_array($refs)) $refs = [];
    $changedRefs = false;
    for ($i=0; $i<count($refs); $i++) {
      if (!isset($refs[$i]['status'])) { $refs[$i]['status'] = 'pendente'; $changedRefs = true; }
      if (!isset($refs[$i]['token']) || !preg_match('/^[a-f0-9]{64}$/', (string)($refs[$i]['token'] ?? ''))) { $refs[$i]['token'] = bin2hex(random_bytes(32)); $changedRefs = true; }
    }
    if ($changedRefs) { $pdo->prepare('UPDATE clients SET referencias=:r WHERE id=:id')->execute(['r'=>json_encode($refs), 'id'=>$id]); $client['referencias'] = json_encode($refs); }
    try { $hasCrit = $pdo->query("SHOW COLUMNS FROM clients LIKE 'criterios_status'")->fetch(); if (!$hasCrit) { $pdo->exec("ALTER TABLE clients ADD COLUMN criterios_status ENUM('pendente','aprovado','reprovado') DEFAULT 'pendente', ADD COLUMN criterios_data DATETIME NULL, ADD COLUMN criterios_user_id INT NULL"); } } catch (\Throwable $e) {}
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (isset($_POST['action']) && $_POST['action'] === 'aprovar_prova') {
        $pdo->prepare("UPDATE clients SET prova_vida_status='aprovado', prova_vida_data=NOW(), prova_vida_user_id=:u WHERE id=:id")->execute(['u'=>$_SESSION['user_id'],'id'=>$id]);
        Audit::log('aprovar_prova_vida','clients',$id,null);
        header('Location: /clientes/'.$id.'/validar');
        exit;
      }
      if (isset($_POST['action']) && $_POST['action'] === 'reprovar_prova') {
        $motivo = trim($_POST['motivo'] ?? '');
        $pdo->prepare("UPDATE clients SET prova_vida_status='reprovado', prova_vida_data=NOW(), prova_vida_user_id=:u, observacoes=CONCAT(IFNULL(observacoes,''),' ',:m) WHERE id=:id")->execute(['u'=>$_SESSION['user_id'],'m'=>$motivo,'id'=>$id]);
        Audit::log('reprovar_prova_vida','clients',$id,$motivo);
        header('Location: /clientes/'.$id.'/validar');
        exit;
      }
      if (isset($_POST['action']) && $_POST['action'] === 'aprovar_cpf') {
        $pdo->prepare("UPDATE clients SET cpf_check_status='aprovado', cpf_check_data=NOW(), cpf_check_user_id=:u WHERE id=:id")->execute(['u'=>$_SESSION['user_id'],'id'=>$id]);
        Audit::log('aprovar_cpf','clients',$id,null);
        header('Location: /clientes/'.$id.'/validar');
        exit;
      }
      if (isset($_POST['action']) && $_POST['action'] === 'reprovar_cpf') {
        $motivo = trim($_POST['motivo'] ?? '');
        $pdo->prepare("UPDATE clients SET cpf_check_status='reprovado', cpf_check_data=NOW(), cpf_check_user_id=:u, observacoes=CONCAT(IFNULL(observacoes,''),' ',:m) WHERE id=:id")->execute(['u'=>$_SESSION['user_id'],'m'=>$motivo,'id'=>$id]);
        Audit::log('reprovar_cpf','clients',$id,$motivo);
        header('Location: /clientes/'.$id.'/validar');
        exit;
      }
      if (isset($_POST['action']) && $_POST['action'] === 'consultar_cpf_api') {
        $cpf = preg_replace('/\D/', '', (string)($client['cpf'] ?? ''));
        $token = ConfigRepo::get('api_cpf_cnpj_token', 'bccbaf9e9af13fc49739b8a43fff0fe8');
        $pacote = ConfigRepo::get('api_cpf_cnpj_pacote', '8');
        $resp = null; $pdfLocal = null; $pdfUrl = null; $redirectUrl = null;
        try {
          $endpoint = 'https://api.cpfcnpj.com.br/' . rawurlencode((string)$token) . '/' . rawurlencode((string)$pacote) . '/' . rawurlencode($cpf);
          $ch = curl_init($endpoint);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_TIMEOUT, 60);
          curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
          $out = curl_exec($ch);
          $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          if ($out === false) { throw new \RuntimeException('cURL error: ' . curl_error($ch)); }
          curl_close($ch);
          $resp = json_decode($out, true);
          if (!is_array($resp)) { $resp = ['raw' => $out]; }
          if ($http >= 400) {
            $pdo->prepare('INSERT INTO cpf_checks (client_id, json_response, checked_by_user_id) VALUES (:cid, :j, :u)')
                ->execute(['cid'=>$id, 'j'=>json_encode(['http_status'=>$http, 'error'=>$resp]), 'u'=>$_SESSION['user_id'] ?? null]);
            header('Location: /clientes/'.$id.'/validar');
            exit;
          }
          $pdfUrl = $resp['pdf'] ?? ($resp['pdf_url'] ?? ($resp['situacaoComprovanteUrl'] ?? null));
          $redirectUrl = $resp['situacaoComprovanteUrl'] ?? ($resp['url'] ?? ($resp['comprovante_url'] ?? null));
          $pdfB64 = $resp['situacaoComprovantePdf'] ?? ($resp['situacaoComprovantePDF'] ?? ($resp['comprovante_pdf_base64'] ?? ($resp['comprovante_pdf'] ?? null)));
          if ($pdfB64) {
            $dir = dirname(__DIR__,2) . '/uploads/' . $id . '/cpf_checks';
            if (!is_dir($dir)) { mkdir($dir, 0755, true); }
            $pdfLocal = $dir . '/cpf_' . date('Ymd_His') . '.pdf';
            $raw = (string)$pdfB64;
            $pos = strpos($raw, ',');
            if ($pos !== false) { $raw = substr($raw, $pos+1); }
            $data = base64_decode($raw, true);
            if ($data !== false) { file_put_contents($pdfLocal, $data); }
          } elseif ($pdfUrl) {
            $dir = dirname(__DIR__,2) . '/uploads/' . $id . '/cpf_checks';
            if (!is_dir($dir)) { mkdir($dir, 0755, true); }
            $pdfLocal = $dir . '/cpf_' . date('Ymd_His') . '.pdf';
            $fp = fopen($pdfLocal, 'w');
            $chp = curl_init($pdfUrl);
            curl_setopt($chp, CURLOPT_FILE, $fp);
            curl_setopt($chp, CURLOPT_TIMEOUT, 60);
            curl_setopt($chp, CURLOPT_FOLLOWLOCATION, true);
            curl_exec($chp);
            curl_close($chp);
            fclose($fp);
          }
          $pdo->prepare('INSERT INTO cpf_checks (client_id, json_response, pdf_path, checked_by_user_id) VALUES (:cid, :j, :p, :u)')
              ->execute(['cid'=>$id, 'j'=>json_encode($resp), 'p'=>$pdfLocal?str_replace(dirname(__DIR__,2),'',$pdfLocal):null, 'u'=>$_SESSION['user_id'] ?? null]);
          Audit::log('consulta_cpf_api','clients',$id,'Consulta CPF via API');
        } catch (\Throwable $e) {
          $pdo->prepare('INSERT INTO cpf_checks (client_id, json_response, checked_by_user_id) VALUES (:cid, :j, :u)')
              ->execute(['cid'=>$id, 'j'=>json_encode(['error'=>$e->getMessage()]), 'u'=>$_SESSION['user_id'] ?? null]);
        }
        header('Location: /clientes/'.$id.'/validar');
        exit;
      }
      if (isset($_POST['action']) && $_POST['action'] === 'aprovar_criterios') {
        $motivo = trim($_POST['motivo'] ?? '');
        if ($motivo === '') { $_SESSION['toast'] = 'Informe o motivo para aprovar os critérios de empréstimo.'; header('Location: /clientes/'.$id.'/validar'); exit; }
        $pdo->prepare("UPDATE clients SET criterios_status='aprovado', criterios_data=NOW(), criterios_user_id=:u, observacoes=CONCAT(IFNULL(observacoes,''),' ',:m) WHERE id=:id")->execute(['u'=>$_SESSION['user_id'],'m'=>$motivo,'id'=>$id]);
        Audit::log('aprovar_criterios','clients',$id,$motivo);
        header('Location: /clientes/'.$id.'/validar');
        exit;
      }
      if (isset($_POST['action']) && $_POST['action'] === 'reprovar_criterios') {
        $pdo->prepare("UPDATE clients SET criterios_status='reprovado', criterios_data=NOW(), criterios_user_id=:u WHERE id=:id")->execute(['u'=>$_SESSION['user_id'],'id'=>$id]);
        Audit::log('reprovar_criterios','clients',$id,null);
        header('Location: /clientes/'.$id.'/validar');
        exit;
      }
      if (isset($_POST['action']) && $_POST['action'] === 'referencia_checar') {
        $idx = (int)($_POST['idx'] ?? -1);
        $status = trim($_POST['status'] ?? 'pendente');
        $stmtR = $pdo->prepare('SELECT referencias FROM clients WHERE id=:id'); $stmtR->execute(['id'=>$id]); $refJson = $stmtR->fetchColumn(); $arr = json_decode($refJson ?? '[]', true); if (!is_array($arr)) $arr = [];
        if ($idx >= 0 && $idx < count($arr)) {
          if (!isset($arr[$idx]['operador']) || !is_array($arr[$idx]['operador'])) { $arr[$idx]['operador'] = []; }
          $arr[$idx]['operador']['status'] = in_array($status, ['pendente','aprovado','reprovado'], true) ? $status : ($arr[$idx]['operador']['status'] ?? 'pendente');
          $arr[$idx]['operador']['checked_at'] = date('Y-m-d H:i:s');
          $arr[$idx]['operador']['checked_by_user_id'] = $_SESSION['user_id'] ?? null;
          $pdo->prepare('UPDATE clients SET referencias=:r WHERE id=:id')->execute(['r'=>json_encode($arr), 'id'=>$id]);
          \App\Helpers\Audit::log('referencia_checada','clients',$id,'idx='.$idx.' status='.$arr[$idx]['operador']['status'].' source=manual');
          $_SESSION['toast'] = 'Referência checada';
        }
        header('Location: /clientes/'.$id.'/validar');
        exit;
      }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'salvar_pdf_comprovante') {
      $last = $pdo->prepare('SELECT * FROM cpf_checks WHERE client_id=:id ORDER BY checked_at DESC, id DESC LIMIT 1');
      $last->execute(['id'=>$id]);
      $row = $last->fetch();
      if ($row) {
        $json = json_decode($row['json_response'] ?? '{}', true);
        $b64 = $json['situacaoComprovantePdf'] ?? ($json['situacaoComprovantePDF'] ?? ($json['comprovante_pdf_base64'] ?? ($json['comprovante_pdf'] ?? null)));
        if ($b64) {
          $dir = dirname(__DIR__,2) . '/uploads/' . $id . '/cpf_checks';
          if (!is_dir($dir)) { mkdir($dir, 0755, true); }
          $pdfLocal = $dir . '/cpf_' . date('Ymd_His') . '.pdf';
          $raw = (string)$b64;
          $pos = strpos($raw, ',');
          if ($pos !== false) { $raw = substr($raw, $pos+1); }
          $data = base64_decode($raw, true);
          if ($data !== false) {
            file_put_contents($pdfLocal, $data);
            $pdo->prepare('UPDATE cpf_checks SET pdf_path=:p WHERE id=:cid')
                ->execute(['p'=>str_replace(dirname(__DIR__,2),'',$pdfLocal), 'cid'=>$row['id']]);
          }
        }
      }
      header('Location: /clientes/'.$id.'/validar');
      exit;
    }
    $last = $pdo->prepare('SELECT * FROM cpf_checks WHERE client_id=:id ORDER BY checked_at DESC, id DESC LIMIT 1');
    $last->execute(['id'=>$id]);
    $cpf_last = $last->fetch();
    $title = 'Validar Cliente';
    $content = __DIR__ . '/../Views/clientes_validar.php';
    include __DIR__ . '/../Views/layout.php';
  }
  public static function referenciaPublica(int $clientId, int $idx, string $token): void {
    $pdo = Connection::get();
    $stmt = $pdo->prepare('SELECT * FROM clients WHERE id=:id'); $stmt->execute(['id'=>$clientId]); $client = $stmt->fetch();
    if (!$client) { http_response_code(404); echo 'Cliente não encontrado'; return; }
    $refs = json_decode($client['referencias'] ?? '[]', true); if (!is_array($refs)) $refs = [];
    if ($idx < 0 || $idx >= count($refs)) { http_response_code(404); echo 'Referência inválida'; return; }
    $ref = $refs[$idx]; $tok = (string)($ref['token'] ?? ''); if ($tok !== $token) { http_response_code(403); echo 'Token inválido'; return; }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $voto = trim($_POST['voto'] ?? '');
      if (in_array($voto, ['aprovo','reprovo'], true)) {
        if (!isset($refs[$idx]['public']) || !is_array($refs[$idx]['public'])) { $refs[$idx]['public'] = []; }
        $refs[$idx]['public']['status'] = $voto === 'aprovo' ? 'aprovado' : 'reprovado';
        $refs[$idx]['public']['checked_at'] = date('Y-m-d H:i:s');
        $refs[$idx]['public']['checked_ip'] = $_SERVER['REMOTE_ADDR'] ?? null;
        $pdo->prepare('UPDATE clients SET referencias=:r WHERE id=:id')->execute(['r'=>json_encode($refs), 'id'=>$clientId]);
        \App\Helpers\Audit::log('referencia_voto_publico','clients',$clientId,'idx='.$idx.' status='.$refs[$idx]['public']['status']);
        $msgOk = 'Resposta registrada. Obrigado!';
        include __DIR__ . '/../Views/referencia_publica.php';
        return;
      }
    }
    include __DIR__ . '/../Views/referencia_publica.php';
  }
  public static function editar(int $id): void {
    $pdo = Connection::get();
    $stmt = $pdo->prepare('SELECT * FROM clients WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $client = $stmt->fetch();
    if (!$client) { header('Location: /clientes'); return; }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $req = ['nome','cpf','data_nascimento','email','telefone','cep','endereco','numero','bairro','cidade','estado','ocupacao','tempo_trabalho','renda_mensal'];
      $missing = [];
      foreach ($req as $k) { $v = trim((string)($_POST[$k] ?? '')); if ($v === '') { $missing[] = $k; } }
      $existingHol = json_decode($client['doc_holerites'] ?? '[]', true); if (!is_array($existingHol)) $existingHol = [];
      $hasNewHol = !empty($_FILES['holerites']['name'][0]);
      $hasFrenteExisting = !empty($client['doc_cnh_frente']);
      $hasVersoExisting = !empty($client['doc_cnh_verso']);
      $hasSelfieExisting = !empty($client['doc_selfie']);
      $hasFrenteNew = !empty($_FILES['cnh_frente']['name'] ?? '');
      $hasVersoNew = !empty($_FILES['cnh_verso']['name'] ?? '');
      $hasSelfieNew = !empty($_FILES['selfie']['name'] ?? '');
      $cnhUnicoPost = isset($_POST['cnh_arquivo_unico']);
      $okCnh = $cnhUnicoPost ? ($hasFrenteExisting || $hasFrenteNew) : (($hasFrenteExisting || $hasFrenteNew) && ($hasVersoExisting || $hasVersoNew));
      $okSelfie = ($hasSelfieExisting || $hasSelfieNew);
      if (!empty($missing) || (!$hasNewHol && count($existingHol) === 0) || !$okCnh || !$okSelfie) {
        $error = 'Preencha todos os campos obrigatórios, envie ao menos um holerite e garanta CNH/RG (frente e verso ou arquivo único) e Selfie.';
        $title = 'Editar Cliente';
        $content = __DIR__ . '/../Views/clientes_editar.php';
        include __DIR__ . '/../Views/layout.php';
        return;
      }
      $cpfNorm = preg_replace('/\D/', '', (string)($_POST['cpf'] ?? ''));
      $dup = $pdo->prepare("SELECT COUNT(*) AS c FROM clients WHERE REPLACE(REPLACE(REPLACE(cpf,'.',''),'-',''),' ','') = :cpf AND id <> :id");
      $dup->execute(['cpf'=>$cpfNorm, 'id'=>$id]);
      if (((int)($dup->fetch()['c'] ?? 0)) > 0) {
        $error = 'CPF já cadastrado.';
        $title = 'Editar Cliente';
        $content = __DIR__ . '/../Views/clientes_editar.php';
        include __DIR__ . '/../Views/layout.php';
        return;
      }
      $pixTipo = trim((string)($_POST['pix_tipo'] ?? ''));
      $pixChaveIn = trim((string)($_POST['pix_chave'] ?? ''));
      $cpfNorm = preg_replace('/\D/', '', (string)($_POST['cpf'] ?? ''));
      $pixValid = true; $pixNorm = null; $pixErr = null;
      if ($pixTipo !== '') { [$pixValid, $pixNorm, $pixErr] = self::validarENormalizarPix($pixTipo, $pixChaveIn, $cpfNorm); }
      if ($pixTipo !== '' && !$pixValid) {
        $error = $pixErr ?: 'Chave PIX inválida.';
        $title = 'Editar Cliente';
        $content = __DIR__ . '/../Views/clientes_editar.php';
        include __DIR__ . '/../Views/layout.php';
        return;
      }
      $sql = 'UPDATE clients SET nome=:nome, cpf=:cpf, data_nascimento=:data_nascimento, email=:email, telefone=:telefone, cep=:cep, endereco=:endereco, numero=:numero, complemento=:complemento, bairro=:bairro, cidade=:cidade, estado=:estado, ocupacao=:ocupacao, tempo_trabalho=:tempo_trabalho, renda_mensal=:renda_mensal, observacoes=:observacoes, pix_tipo=:pix_tipo, pix_chave=:pix_chave WHERE id=:id';
      $pdo->prepare($sql)->execute([
        'nome' => (function($n){ $n = trim((string)$n); if ($n==='') return $n; if (function_exists('mb_strtoupper')) { return mb_strtoupper($n, 'UTF-8'); } return strtoupper($n); })(($_POST['nome'] ?? '')),
        'cpf' => $cpfNorm,
        'data_nascimento' => trim($_POST['data_nascimento'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'telefone' => trim($_POST['telefone'] ?? ''),
        'cep' => trim($_POST['cep'] ?? ''),
        'endereco' => trim($_POST['endereco'] ?? ''),
        'numero' => trim($_POST['numero'] ?? ''),
        'complemento' => trim($_POST['complemento'] ?? ''),
        'bairro' => trim($_POST['bairro'] ?? ''),
        'cidade' => trim($_POST['cidade'] ?? ''),
        'estado' => trim($_POST['estado'] ?? ''),
        'ocupacao' => trim($_POST['ocupacao'] ?? ''),
        'tempo_trabalho' => trim($_POST['tempo_trabalho'] ?? ''),
        'renda_mensal' => self::parseRenda($_POST['renda_mensal'] ?? '0'),
        'observacoes' => trim($_POST['observacoes'] ?? ''),
        'pix_tipo' => $pixTipo !== '' ? strtolower($pixTipo) : null,
        'pix_chave' => $pixTipo !== '' ? $pixNorm : null,
        'id' => $id
      ]);
      $indicado = (int)($_POST['indicado_por_id'] ?? 0);
      $refN = $_POST['ref_nome'] ?? [];
      $refR = $_POST['ref_relacao'] ?? [];
      $refT = $_POST['ref_telefone'] ?? [];
      $refs = [];
      for ($i=0; $i<3; $i++) { $n = trim((string)($refN[$i] ?? '')); $rel = trim((string)($refR[$i] ?? '')); $t = trim((string)($refT[$i] ?? '')); $nUp = (function($x){ $x = trim((string)$x); if ($x==='') return $x; return function_exists('mb_strtoupper') ? mb_strtoupper($x,'UTF-8') : strtoupper($x); })($n); if ($n !== '' || $rel !== '' || $t !== '') { $refs[] = ['nome'=>$nUp, 'relacao'=>$rel, 'telefone'=>$t]; } }
      try {
        $hasInd = $pdo->query("SHOW COLUMNS FROM clients LIKE 'indicado_por_id'")->fetch();
        $hasRefs = $pdo->query("SHOW COLUMNS FROM clients LIKE 'referencias'")->fetch();
        if ($hasInd) { $pdo->prepare('UPDATE clients SET indicado_por_id=:ind WHERE id=:id')->execute(['ind'=>$indicado?:null, 'id'=>$id]); }
        if ($hasRefs) { $pdo->prepare('UPDATE clients SET referencias=:refs WHERE id=:id')->execute(['refs'=>json_encode($refs), 'id'=>$id]); }
      } catch (\Throwable $e) { /* no-op */ }
      $cnhUnico = isset($_POST['cnh_arquivo_unico']) ? 1 : 0;
      $pdo->prepare('UPDATE clients SET cnh_arquivo_unico = :u WHERE id = :id')->execute(['u'=>$cnhUnico,'id'=>$id]);
      if ($cnhUnico) {
        if (!empty($_FILES['cnh_frente']['name'])) {
          try { $path = Upload::save($_FILES['cnh_frente'], $id, 'documentos'); $pdo->prepare('UPDATE clients SET doc_cnh_frente = :f, doc_cnh_verso = NULL WHERE id = :id')->execute(['f'=>$path,'id'=>$id]); } catch (\Throwable $e) { \App\Helpers\Audit::log('upload_error','clients',$id,$e->getMessage()); }
        }
      } else {
        if (!empty($_FILES['cnh_frente']['name'])) {
          try { $path = Upload::save($_FILES['cnh_frente'], $id, 'documentos'); $pdo->prepare('UPDATE clients SET doc_cnh_frente = :f WHERE id = :id')->execute(['f'=>$path,'id'=>$id]); } catch (\Throwable $e) { \App\Helpers\Audit::log('upload_error','clients',$id,$e->getMessage()); }
        }
        if (!empty($_FILES['cnh_verso']['name'])) {
          try { $path = Upload::save($_FILES['cnh_verso'], $id, 'documentos'); $pdo->prepare('UPDATE clients SET doc_cnh_verso = :v WHERE id = :id')->execute(['v'=>$path,'id'=>$id]); } catch (\Throwable $e) { \App\Helpers\Audit::log('upload_error','clients',$id,$e->getMessage()); }
        }
      }
      if (!empty($_FILES['selfie']['name'])) {
        try { $path = Upload::save($_FILES['selfie'], $id, 'documentos'); $pdo->prepare('UPDATE clients SET doc_selfie = :s WHERE id = :id')->execute(['s'=>$path,'id'=>$id]); } catch (\Throwable $e) { \App\Helpers\Audit::log('upload_error','clients',$id,$e->getMessage()); }
      }
      if (!empty($_FILES['holerites']['name'][0])) {
        $existing = json_decode($client['doc_holerites'] ?? '[]', true);
        if (!is_array($existing)) $existing = [];
        $maxHol = 5;
        // Mantém no máximo 3 holerites no total (existentes + novos)
        for ($i=0; $i<count($_FILES['holerites']['name']); $i++) {
          if (count($existing) >= $maxHol) { break; }
          $file = [
            'name' => $_FILES['holerites']['name'][$i],
            'type' => $_FILES['holerites']['type'][$i],
            'tmp_name' => $_FILES['holerites']['tmp_name'][$i],
            'error' => $_FILES['holerites']['error'][$i],
            'size' => $_FILES['holerites']['size'][$i]
          ];
          try { $existing[] = Upload::save($file, $id, 'holerites'); } catch (\Throwable $e) { \App\Helpers\Audit::log('upload_error','clients',$id,$e->getMessage()); }
        }
        // Garante o limite no armazenamento
        $existing = array_slice($existing, 0, $maxHol);
        $pdo->prepare('UPDATE clients SET doc_holerites = :j WHERE id = :id')->execute(['j'=>json_encode($existing),'id'=>$id]);
      }
      \App\Helpers\Audit::log('update','clients',$id,'Cliente atualizado');
      header('Location: /clientes');
      return;
    }
    $title = 'Editar Cliente';
    $content = __DIR__ . '/../Views/clientes_editar.php';
    include __DIR__ . '/../Views/layout.php';
  }
  public static function ver(int $id): void {
    $pdo = Connection::get();
    $stmt = $pdo->prepare('SELECT * FROM clients WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $client = $stmt->fetch();
    if (!$client) { header('Location: /clientes'); return; }
    $indicador = null;
    if (!empty($client['indicado_por_id'])) {
      $si = $pdo->prepare('SELECT id, nome, telefone FROM clients WHERE id=:id');
      $si->execute(['id'=>$client['indicado_por_id']]);
      $indicador = $si->fetch();
    }
    $lc = $pdo->prepare('SELECT COUNT(*) AS c FROM loans WHERE client_id=:id');
    $lc->execute(['id'=>$id]);
    $temEmprestimos = ((int)($lc->fetch()['c'] ?? 0)) > 0;
    $title = 'Visualizar Cliente';
    $content = __DIR__ . '/../Views/clientes_ver.php';
    include __DIR__ . '/../Views/layout.php';
  }
  public static function buscar(): void {
    $pdo = Connection::get();
    try { $col = $pdo->query("SHOW COLUMNS FROM clients LIKE 'criterios_status'")->fetch(); if (!$col) { $pdo->exec("ALTER TABLE clients ADD COLUMN criterios_status ENUM('pendente','aprovado','reprovado') DEFAULT 'pendente', ADD COLUMN criterios_data DATETIME NULL, ADD COLUMN criterios_user_id INT NULL"); } } catch (\Throwable $e) {}
    $q = trim($_GET['q'] ?? '');
    $limit = 20;
    $sql = "SELECT id, nome, cpf, telefone FROM clients WHERE (deleted_at IS NULL) AND (is_draft = 0)";
    $params = [];
    if ($q !== '') {
      $params['q'] = '%'.$q.'%';
      $sql .= ' AND (nome LIKE :q OR cpf LIKE :q OR telefone LIKE :q)';
    }
    $sql .= ' ORDER BY nome ASC LIMIT ' . (int)$limit;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    header('Content-Type: application/json');
    echo json_encode($rows);
  }
  public static function buscarPorId(int $id): void {
    $pdo = Connection::get();
    $stmt = $pdo->prepare('SELECT id, nome, cpf, telefone, renda_mensal, tempo_trabalho FROM clients WHERE id=:id');
    $stmt->execute(['id'=>$id]);
    $row = $stmt->fetch();
    header('Content-Type: application/json');
    echo json_encode($row ?: null);
  }
  public static function gerarLinksDraft(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Método não permitido'; return; }
    $pdo = Connection::get();
    $nome = trim($_POST['nome'] ?? '');
    $cpf = preg_replace('/\D/', '', (string)($_POST['cpf'] ?? ''));
    $nasc = trim($_POST['data_nascimento'] ?? '');
    $refN = $_POST['ref_nome'] ?? [];
    $refR = $_POST['ref_relacao'] ?? [];
    $refT = $_POST['ref_telefone'] ?? [];
    if ($nome === '' || $cpf === '' || $nasc === '') { http_response_code(400); echo json_encode(['error'=>'Campos obrigatórios: nome, cpf, data_nascimento']); return; }
    $exists = $pdo->prepare("SELECT * FROM clients WHERE REPLACE(REPLACE(REPLACE(cpf,'.',''),'-',''),' ','') = :cpf");
    $exists->execute(['cpf'=>$cpf]);
    $cli = $exists->fetch();
    if ($cli && (int)($cli['is_draft'] ?? 0) === 0) { http_response_code(409); echo json_encode(['error'=>'CPF já cadastrado']); return; }
    $clientId = (int)($cli['id'] ?? 0);
    if ($clientId === 0) {
      $stmt = $pdo->prepare('INSERT INTO clients (nome, cpf, data_nascimento, is_draft, prova_vida_status, cpf_check_status) VALUES (:nome,:cpf,:data_nascimento,1,\'pendente\',\'pendente\')');
      $stmt->execute(['nome'=> (function($n){ $n = trim((string)$n); if ($n==='') return $n; return function_exists('mb_strtoupper') ? mb_strtoupper($n,'UTF-8') : strtoupper($n); })($nome), 'cpf'=>$cpf, 'data_nascimento'=>$nasc]);
      $clientId = (int)$pdo->lastInsertId();
    }
    $refs = [];
    for ($i=0; $i<3; $i++) {
      $n = trim((string)($refN[$i] ?? ''));
      $rel = trim((string)($refR[$i] ?? ''));
      $t = trim((string)($refT[$i] ?? ''));
      if ($n === '' && $rel === '' && $t === '') { $refs[] = ['nome'=>'','relacao'=>'','telefone'=>'','token'=>null]; continue; }
      $nUp = (function($x){ $x = trim((string)$x); if ($x==='') return $x; return function_exists('mb_strtoupper') ? mb_strtoupper($x,'UTF-8') : strtoupper($x); })($n);
      $refs[] = ['nome'=>$nUp, 'relacao'=>$rel, 'telefone'=>$t, 'token'=>bin2hex(random_bytes(32))];
    }
    $pdo->prepare('UPDATE clients SET referencias=:r WHERE id=:id')->execute(['r'=>json_encode($refs), 'id'=>$clientId]);
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
    $links = [];
    for ($i=0; $i<3; $i++) { $tok = (string)($refs[$i]['token'] ?? ''); $links[$i] = $tok ? ($scheme . $host . '/referencia/' . $clientId . '/' . $i . '/' . $tok) : null; }
    header('Content-Type: application/json');
    echo json_encode(['client_id'=>$clientId, 'tokens'=>array_map(function($r){ return $r['token'] ?? null; }, $refs), 'links'=>$links]);
  }
}