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
    $s = str_replace('.', '', $s);
    $s = str_replace(',', '.', $s);
    $f = (float)$s;
    if ($f > 99999999.99) $f = 99999999.99;
    if ($f < 0) $f = 0.0;
    return round($f, 2);
  }
  public static function novo(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $pdo = Connection::get();
      $stmt = $pdo->prepare('INSERT INTO clients (nome, cpf, data_nascimento, email, telefone, cep, endereco, numero, complemento, bairro, cidade, estado, ocupacao, tempo_trabalho, renda_mensal, cnh_arquivo_unico, observacoes) VALUES (:nome,:cpf,:data_nascimento,:email,:telefone,:cep,:endereco,:numero,:complemento,:bairro,:cidade,:estado,:ocupacao,:tempo_trabalho,:renda_mensal,:cnh_arquivo_unico,:observacoes)');
      $stmt->execute([
        'nome' => trim($_POST['nome'] ?? ''),
        'cpf' => trim($_POST['cpf'] ?? ''),
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
        'observacoes' => trim($_POST['observacoes'] ?? '')
      ]);
      $clientId = (int)$pdo->lastInsertId();
      $holerites = [];
      if (!empty($_FILES['holerites']['name'][0])) {
        for ($i=0; $i<count($_FILES['holerites']['name']); $i++) {
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
      header('Location: /clientes/' . $clientId . '/validar');
      exit;
    }
    $title = 'Novo Cliente';
    $content = __DIR__ . '/../Views/clientes_novo.php';
    include __DIR__ . '/../Views/layout.php';
  }
  public static function lista(): void {
    $pdo = Connection::get();
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
    $sql = 'SELECT id, nome, cpf, prova_vida_status, cpf_check_status, created_at FROM clients WHERE 1=1';
    $params = [];
    if ($q !== '') { $sql .= ' AND (nome LIKE :q OR cpf LIKE :q)'; $params['q'] = '%'.$q.'%'; }
    if ($ini !== '') { $sql .= ' AND DATE(created_at) >= :ini'; $params['ini'] = $ini; }
    if ($fim !== '') { $sql .= ' AND DATE(created_at) <= :fim'; $params['fim'] = $fim; }
    if ($ps !== '' && in_array($ps, ['aprovado','reprovado','pendente'], true)) { $sql .= ' AND prova_vida_status = :ps'; $params['ps'] = $ps; }
    if ($cs !== '' && in_array($cs, ['aprovado','reprovado','pendente'], true)) { $sql .= ' AND cpf_check_status = :cs'; $params['cs'] = $cs; }
    $sql .= ' ORDER BY created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
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
  public static function editar(int $id): void {
    $pdo = Connection::get();
    $stmt = $pdo->prepare('SELECT * FROM clients WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $client = $stmt->fetch();
    if (!$client) { header('Location: /clientes'); return; }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $sql = 'UPDATE clients SET nome=:nome, cpf=:cpf, data_nascimento=:data_nascimento, email=:email, telefone=:telefone, cep=:cep, endereco=:endereco, numero=:numero, complemento=:complemento, bairro=:bairro, cidade=:cidade, estado=:estado, ocupacao=:ocupacao, tempo_trabalho=:tempo_trabalho, renda_mensal=:renda_mensal, observacoes=:observacoes WHERE id=:id';
      $pdo->prepare($sql)->execute([
        'nome' => trim($_POST['nome'] ?? ''),
        'cpf' => trim($_POST['cpf'] ?? ''),
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
        'id' => $id
      ]);
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
        for ($i=0; $i<count($_FILES['holerites']['name']); $i++) {
          $file = [
            'name' => $_FILES['holerites']['name'][$i],
            'type' => $_FILES['holerites']['type'][$i],
            'tmp_name' => $_FILES['holerites']['tmp_name'][$i],
            'error' => $_FILES['holerites']['error'][$i],
            'size' => $_FILES['holerites']['size'][$i]
          ];
          try { $existing[] = Upload::save($file, $id, 'holerites'); } catch (\Throwable $e) { \App\Helpers\Audit::log('upload_error','clients',$id,$e->getMessage()); }
        }
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
    $title = 'Visualizar Cliente';
    $content = __DIR__ . '/../Views/clientes_ver.php';
    include __DIR__ . '/../Views/layout.php';
  }
}