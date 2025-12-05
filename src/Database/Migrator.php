<?php
namespace App\Database;

class Migrator {
  public static function run(): void {
    Bootstrap::ensureDatabase();
    $pdo = Connection::get();
    $sql = [];
    $sql[] = "CREATE TABLE IF NOT EXISTS config (id INT PRIMARY KEY AUTO_INCREMENT, chave VARCHAR(100) UNIQUE NOT NULL, valor VARCHAR(1000) NOT NULL, descricao TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)";
    $sql[] = "CREATE TABLE IF NOT EXISTS clients (id INT PRIMARY KEY AUTO_INCREMENT, nome VARCHAR(255) NOT NULL, cpf VARCHAR(14) UNIQUE NOT NULL, data_nascimento DATE NOT NULL, email VARCHAR(255), telefone VARCHAR(20), cep VARCHAR(9), endereco VARCHAR(255), numero VARCHAR(20), complemento VARCHAR(100), bairro VARCHAR(100), cidade VARCHAR(100), estado VARCHAR(2), ocupacao VARCHAR(100), tempo_trabalho VARCHAR(50), renda_mensal DECIMAL(10,2), renda_liquida DECIMAL(10,2), doc_holerites JSON, doc_cnh_frente VARCHAR(255), doc_cnh_verso VARCHAR(255), doc_selfie VARCHAR(255), cnh_arquivo_unico BOOLEAN DEFAULT 0, prova_vida_status ENUM('pendente','aprovado','reprovado') DEFAULT 'pendente', prova_vida_data DATETIME, prova_vida_user_id INT, cpf_check_status ENUM('pendente','aprovado','reprovado') DEFAULT 'pendente', cpf_check_data DATETIME, cpf_check_user_id INT, observacoes TEXT, pix_tipo ENUM('cpf','email','telefone','aleatoria') NULL, pix_chave VARCHAR(255) NULL, cadastro_token VARCHAR(64) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL, cadastro_publico BOOLEAN DEFAULT 0, UNIQUE KEY uniq_cadastro_token (cadastro_token), INDEX idx_cpf (cpf), INDEX idx_nome (nome))";
    $sql[] = "CREATE TABLE IF NOT EXISTS cpf_checks (id INT PRIMARY KEY AUTO_INCREMENT, client_id INT NOT NULL, json_response TEXT NOT NULL, pdf_path VARCHAR(255), checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, checked_by_user_id INT, FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE, INDEX idx_client (client_id))";
    $sql[] = "CREATE TABLE IF NOT EXISTS loans (id INT PRIMARY KEY AUTO_INCREMENT, client_id INT NOT NULL, valor_principal DECIMAL(10,2) NOT NULL, num_parcelas INT NOT NULL, taxa_juros_mensal DECIMAL(5,2) NOT NULL, valor_parcela DECIMAL(10,2) NOT NULL, valor_total DECIMAL(10,2) NOT NULL, total_juros DECIMAL(10,2) NOT NULL, data_primeiro_vencimento DATE NOT NULL, dias_primeiro_periodo INT, juros_proporcional_primeiro_mes DECIMAL(10,2) DEFAULT 0, cet_percentual DECIMAL(5,2), contrato_html TEXT, contrato_pdf_path VARCHAR(255), contrato_token VARCHAR(64) UNIQUE, contrato_assinado_em DATETIME, contrato_assinante_nome VARCHAR(255), contrato_assinante_ip VARCHAR(45), contrato_assinante_user_agent TEXT, transferencia_valor DECIMAL(10,2), transferencia_data DATE, transferencia_comprovante_path VARCHAR(255), transferencia_user_id INT, transferencia_em DATETIME, boletos_gerados BOOLEAN DEFAULT 0, boletos_api_response JSON, boletos_gerados_em DATETIME, status ENUM('calculado','aguardando_contrato','aguardando_assinatura','aguardando_transferencia','aguardando_boletos','concluido') DEFAULT 'calculado', observacoes TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, created_by_user_id INT, deleted_at TIMESTAMP NULL, FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT, INDEX idx_client (client_id), INDEX idx_status (status), INDEX idx_token (contrato_token))";
    $sql[] = "CREATE TABLE IF NOT EXISTS loan_parcelas (id INT PRIMARY KEY AUTO_INCREMENT, loan_id INT NOT NULL, numero_parcela INT NOT NULL, valor DECIMAL(10,2) NOT NULL, data_vencimento DATE NOT NULL, juros_embutido DECIMAL(10,2) NOT NULL, amortizacao DECIMAL(10,2) NOT NULL, saldo_devedor DECIMAL(10,2) NOT NULL, boleto_id VARCHAR(100), boleto_url TEXT, boleto_codigo_barras VARCHAR(100), boleto_linha_digitavel VARCHAR(100), status ENUM('pendente','pago','vencido','cancelado') DEFAULT 'pendente', pago_em DATETIME, valor_pago DECIMAL(10,2), FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE, INDEX idx_loan (loan_id), INDEX idx_vencimento (data_vencimento), INDEX idx_status (status))";
    $sql[] = "CREATE TABLE IF NOT EXISTS billing_queue (id INT PRIMARY KEY AUTO_INCREMENT, parcela_id INT NOT NULL, loan_id INT NOT NULL, client_id INT NOT NULL, status ENUM('aguardando','processando','sucesso','erro') NOT NULL DEFAULT 'aguardando', try_count INT NOT NULL DEFAULT 0, last_error TEXT NULL, api_response JSON NULL, payment_id VARCHAR(100) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, processed_at DATETIME NULL, UNIQUE KEY uniq_parcela (parcela_id), INDEX idx_status (status), INDEX idx_loan (loan_id))";
    $sql[] = "CREATE TABLE IF NOT EXISTS billing_logs (id INT PRIMARY KEY AUTO_INCREMENT, queue_id INT NULL, action VARCHAR(50) NOT NULL, http_code INT NULL, request_json JSON NULL, response_json JSON NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_action (action), INDEX idx_queue (queue_id))";
    $sql[] = "CREATE TABLE IF NOT EXISTS audit_log (id INT PRIMARY KEY AUTO_INCREMENT, user_id INT, tabela VARCHAR(50), registro_id INT, acao VARCHAR(50) NOT NULL, descricao TEXT, ip VARCHAR(45), user_agent TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_user (user_id), INDEX idx_tabela (tabela), INDEX idx_acao (acao), INDEX idx_created (created_at))";
    $sql[] = "CREATE TABLE IF NOT EXISTS users (id INT PRIMARY KEY AUTO_INCREMENT, username VARCHAR(100) UNIQUE NOT NULL, password VARCHAR(255) NOT NULL, nome VARCHAR(100) NOT NULL, user_notes TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)";
    foreach ($sql as $q) { $pdo->exec($q); }
    try {
      $hasLytex = $pdo->query("SHOW TABLES LIKE 'lytex_queue'")->fetch();
      $hasBilling = $pdo->query("SHOW TABLES LIKE 'billing_queue'")->fetch();
      if ($hasLytex && $hasBilling) {
        $countBilling = $pdo->query("SELECT COUNT(*) AS c FROM billing_queue")->fetch();
        $countLytex = $pdo->query("SELECT COUNT(*) AS c FROM lytex_queue")->fetch();
        if (((int)($countBilling['c'] ?? 0)) === 0 && ((int)($countLytex['c'] ?? 0)) > 0) {
          $pdo->exec("INSERT INTO billing_queue (id, parcela_id, loan_id, client_id, status, try_count, last_error, api_response, payment_id, created_at, updated_at, processed_at) SELECT id, parcela_id, loan_id, client_id, status, try_count, last_error, api_response, payment_id, created_at, updated_at, processed_at FROM lytex_queue");
        }
      }
    } catch (\Throwable $e) {}
    try {
      $col = $pdo->query("SHOW COLUMNS FROM billing_queue LIKE 'status'")->fetch();
      if ($col) { $pdo->exec("ALTER TABLE billing_queue MODIFY COLUMN status ENUM('aguardando','processando','sucesso','erro') NOT NULL DEFAULT 'aguardando'"); }
      $pdo->exec("UPDATE billing_queue SET status='aguardando' WHERE status='queued'");
      $pdo->exec("UPDATE billing_queue SET status='processando' WHERE status='processing'");
      $pdo->exec("UPDATE billing_queue SET status='sucesso' WHERE status='success'");
      $pdo->exec("UPDATE billing_queue SET status='erro' WHERE status='error'");
      $pdo->exec("UPDATE billing_queue SET status='aguardando', payment_id=NULL, processed_at=NULL WHERE status='sucesso'");
      $hasRun = $pdo->query("SHOW COLUMNS FROM billing_logs LIKE 'run_id'")->fetch();
      if (!$hasRun) { $pdo->exec("ALTER TABLE billing_logs ADD COLUMN run_id VARCHAR(64) NULL, ADD INDEX idx_run (run_id)"); }
      $hasNote = $pdo->query("SHOW COLUMNS FROM billing_logs LIKE 'note'")->fetch();
      if (!$hasNote) { $pdo->exec("ALTER TABLE billing_logs ADD COLUMN note VARCHAR(255) NULL"); }
      $hasCol = $pdo->query("SHOW COLUMNS FROM clients LIKE 'lytex_client_id'")->fetch();
      if (!$hasCol) { $pdo->exec("ALTER TABLE clients ADD COLUMN lytex_client_id VARCHAR(64) NULL"); }
    } catch (\Throwable $e) {}
    try {
      $col = $pdo->query("SHOW COLUMNS FROM config LIKE 'valor'")->fetch();
      $type = strtolower($col['Type'] ?? '');
      if ($type !== '' && preg_match('/varchar\((\d+)\)/', $type, $m) && (int)$m[1] < 1000) {
        $pdo->exec("ALTER TABLE config MODIFY COLUMN valor VARCHAR(1000) NOT NULL");
      }
    } catch (\Throwable $e) {}
    $exists = $pdo->query("SELECT COUNT(*) AS c FROM config")->fetch();
    if ((int)$exists['c'] === 0) {
      $pdo->exec("INSERT INTO config (chave, valor, descricao) VALUES ('multa_percentual','2','Multa por atraso (%)'),('juros_mora_percentual_dia','0.033','Juros de mora por dia (%)'),('taxa_juros_padrao_mensal','2.5','Taxa de juros padrão mensal (%)'),('empresa_nome','Clear Securitizadora S/A','Nome da empresa'),('empresa_cnpj','00.000.000/0001-00','CNPJ da empresa'),('empresa_endereco','Endereço completo','Endereço da empresa')");
    }
    try {
      $loanCols = $pdo->query("SHOW COLUMNS FROM loans")->fetchAll();
      $haveMetodo = false; foreach ($loanCols as $col) { if (($col['Field'] ?? '') === 'boletos_metodo') { $haveMetodo = true; break; } }
      if (!$haveMetodo) { $pdo->exec("ALTER TABLE loans ADD COLUMN boletos_metodo ENUM('api','manual') NULL AFTER boletos_api_response"); }
    } catch (\Throwable $e) {}
    try {
      $pdo->exec("ALTER TABLE loans MODIFY COLUMN status ENUM('calculado','aguardando_contrato','aguardando_assinatura','aguardando_transferencia','aguardando_boletos','ativo','cancelado','concluido') DEFAULT 'calculado'");
    } catch (\Throwable $e) {}
    try {
      $colDb = $pdo->query("SHOW COLUMNS FROM loans LIKE 'data_base'")->fetch();
      if (!$colDb) { $pdo->exec("ALTER TABLE loans ADD COLUMN data_base DATE NULL"); }
    } catch (\Throwable $e) {}
    $cols = $pdo->query("SHOW COLUMNS FROM clients")->fetchAll();
    $haveIndicador = false; $haveReferencias = false; $haveCriterios = false; $haveCadastroPublico = false; $haveDraft = false; $havePixTipo = false; $havePixChave = false; $haveCadastroToken = false; $haveRendaLiquida = false; $haveRotFrente=false; $haveRotVerso=false; $haveRotSelfie=false; $haveRotHol=false;
    foreach ($cols as $col) {
      $f = ($col['Field'] ?? '');
      if ($f === 'indicado_por_id') { $haveIndicador = true; }
      if ($f === 'referencias') { $haveReferencias = true; }
      if ($f === 'criterios_status') { $haveCriterios = true; }
      if ($f === 'cadastro_publico') { $haveCadastroPublico = true; }
      if ($f === 'is_draft') { $haveDraft = true; }
      if ($f === 'pix_tipo') { $havePixTipo = true; }
      if ($f === 'pix_chave') { $havePixChave = true; }
      if ($f === 'cadastro_token') { $haveCadastroToken = true; }
      if ($f === 'renda_liquida') { $haveRendaLiquida = true; }
      if ($f === 'doc_cnh_frente_rot') { $haveRotFrente = true; }
      if ($f === 'doc_cnh_verso_rot') { $haveRotVerso = true; }
      if ($f === 'doc_selfie_rot') { $haveRotSelfie = true; }
      if ($f === 'doc_holerites_rot') { $haveRotHol = true; }
    }
    if (!$haveIndicador) { $pdo->exec("ALTER TABLE clients ADD COLUMN indicado_por_id INT NULL, ADD INDEX idx_indicado (indicado_por_id)"); }
    if (!$haveReferencias) { $pdo->exec("ALTER TABLE clients ADD COLUMN referencias JSON NULL"); }
    if (!$haveCriterios) { $pdo->exec("ALTER TABLE clients ADD COLUMN criterios_status ENUM('pendente','aprovado','reprovado') DEFAULT 'pendente', ADD COLUMN criterios_data DATETIME NULL, ADD COLUMN criterios_user_id INT NULL"); }
    if (!$haveCadastroPublico) { $pdo->exec("ALTER TABLE clients ADD COLUMN cadastro_publico BOOLEAN DEFAULT 0"); }
    if (!$haveDraft) { $pdo->exec("ALTER TABLE clients ADD COLUMN is_draft BOOLEAN DEFAULT 0"); }
    if (!$havePixTipo) { $pdo->exec("ALTER TABLE clients ADD COLUMN pix_tipo ENUM('cpf','email','telefone','aleatoria') NULL AFTER observacoes"); }
    if (!$havePixChave) { $pdo->exec("ALTER TABLE clients ADD COLUMN pix_chave VARCHAR(255) NULL AFTER pix_tipo"); }
    if (!$haveCadastroToken) { $pdo->exec("ALTER TABLE clients ADD COLUMN cadastro_token VARCHAR(64) NULL, ADD UNIQUE KEY uniq_cadastro_token (cadastro_token)"); }
    if (!$haveRendaLiquida) { $pdo->exec("ALTER TABLE clients ADD COLUMN renda_liquida DECIMAL(10,2) NULL"); }
    if (!$haveRotFrente) { $pdo->exec("ALTER TABLE clients ADD COLUMN doc_cnh_frente_rot INT DEFAULT 0"); }
    if (!$haveRotVerso) { $pdo->exec("ALTER TABLE clients ADD COLUMN doc_cnh_verso_rot INT DEFAULT 0"); }
    if (!$haveRotSelfie) { $pdo->exec("ALTER TABLE clients ADD COLUMN doc_selfie_rot INT DEFAULT 0"); }
    if (!$haveRotHol) { $pdo->exec("ALTER TABLE clients ADD COLUMN doc_holerites_rot JSON NULL"); }
    $usersCount = $pdo->query("SELECT COUNT(*) AS c FROM users")->fetch();
    if ((int)($usersCount['c'] ?? 0) === 0) {
      $ins = $pdo->prepare('INSERT INTO users (username, password, nome) VALUES (:u, :p, :n)');
      $ins->execute(['u'=>'operador1','p'=>password_hash('senha123', PASSWORD_DEFAULT),'n'=>'Operador 1']);
      $ins->execute(['u'=>'operador2','p'=>password_hash('senha456', PASSWORD_DEFAULT),'n'=>'Operador 2']);
      $ins->execute(['u'=>'operador3','p'=>password_hash('senha789', PASSWORD_DEFAULT),'n'=>'Operador 3']);
    }
    $userCols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll();
    $haveRole = false; $haveNotes = false; foreach ($userCols as $col) { $f = ($col['Field'] ?? ''); if ($f === 'role') { $haveRole = true; } if ($f === 'user_notes') { $haveNotes = true; } }
    if (!$haveRole) { $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'admin'"); }
    if (!$haveNotes) { $pdo->exec("ALTER TABLE users ADD COLUMN user_notes TEXT NULL"); }
    // Set user ID 1 as superadmin, others as admin
    try {
      $pdo->exec("UPDATE users SET role='superadmin' WHERE id=1");
      $pdo->exec("UPDATE users SET role='admin' WHERE id<>1");
    } catch (\Throwable $e) {}
    try { $pdo->exec("CREATE TABLE IF NOT EXISTS loans_archive LIKE loans"); } catch (\Throwable $e) {}
    try { $pdo->exec("CREATE TABLE IF NOT EXISTS loan_parcelas_archive LIKE loan_parcelas"); } catch (\Throwable $e) {}
  }
}