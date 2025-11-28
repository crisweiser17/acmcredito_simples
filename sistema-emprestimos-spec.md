# SISTEMA DE GESTÃO DE EMPRÉSTIMOS
## Especificação Técnica Completa

---

## STACK TECNOLÓGICA

- **Backend:** PHP 8+
- **Banco de Dados:** MySQL 8+
- **Frontend:** JavaScript (Vanilla), TailwindCSS com Preline.co
- **Autenticação:** Sessões PHP
- **Uploads:** Sistema de arquivos local

---

## 1. ESTRUTURA DO BANCO DE DADOS

### 1.1 Tabela: config
Armazena configurações do sistema.

```sql
CREATE TABLE config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    chave VARCHAR(100) UNIQUE NOT NULL,
    valor VARCHAR(255) NOT NULL,
    descricao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Registros iniciais
INSERT INTO config (chave, valor, descricao) VALUES
('multa_percentual', '2', 'Multa por atraso no pagamento (%)'),
('juros_mora_percentual_dia', '0.033', 'Juros de mora por dia de atraso (%)'),
('taxa_juros_padrao_mensal', '2.5', 'Taxa de juros padrão mensal (%)'),
('empresa_nome', 'Clear Securitizadora S/A', 'Nome da empresa'),
('empresa_cnpj', '00.000.000/0001-00', 'CNPJ da empresa'),
('empresa_endereco', 'Endereço completo', 'Endereço da empresa');
```

### 1.2 Tabela: clients
Armazena dados completos dos clientes.

```sql
CREATE TABLE clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    -- Dados Pessoais
    nome VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    data_nascimento DATE NOT NULL,
    email VARCHAR(255),
    telefone VARCHAR(20),
    
    -- Endereço
    cep VARCHAR(9),
    endereco VARCHAR(255),
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    
    -- Dados Profissionais
    ocupacao VARCHAR(100),
    tempo_trabalho VARCHAR(50),
    renda_mensal DECIMAL(10,2),
    
    -- Documentos (paths dos arquivos)
    doc_holerites JSON,
    doc_cnh_frente VARCHAR(255),
    doc_cnh_verso VARCHAR(255),
    doc_selfie VARCHAR(255),
    cnh_arquivo_unico BOOLEAN DEFAULT 0,
    
    -- Validações
    prova_vida_status ENUM('pendente', 'aprovado', 'reprovado') DEFAULT 'pendente',
    prova_vida_data DATETIME,
    prova_vida_user_id INT,
    
    cpf_check_status ENUM('pendente', 'aprovado', 'reprovado') DEFAULT 'pendente',
    cpf_check_data DATETIME,
    cpf_check_user_id INT,
    
    -- Observações
    observacoes TEXT,
    
    -- Controle
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_cpf (cpf),
    INDEX idx_nome (nome)
);
```

### 1.3 Tabela: cpf_checks
Armazena resultados de consultas CPF na Receita Federal.

```sql
CREATE TABLE cpf_checks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    json_response TEXT NOT NULL,
    pdf_path VARCHAR(255),
    checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    checked_by_user_id INT,
    
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    INDEX idx_client (client_id)
);
```

### 1.4 Tabela: loans
Armazena dados dos empréstimos.

```sql
CREATE TABLE loans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    
    -- Dados do Empréstimo
    valor_principal DECIMAL(10,2) NOT NULL,
    num_parcelas INT NOT NULL,
    taxa_juros_mensal DECIMAL(5,2) NOT NULL,
    valor_parcela DECIMAL(10,2) NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    total_juros DECIMAL(10,2) NOT NULL,
    
    -- Cálculos do primeiro período
    data_primeiro_vencimento DATE NOT NULL,
    dias_primeiro_periodo INT,
    juros_proporcional_primeiro_mes DECIMAL(10,2) DEFAULT 0,
    cet_percentual DECIMAL(5,2),
    
    -- Contrato
    contrato_html TEXT,
    contrato_pdf_path VARCHAR(255),
    contrato_token VARCHAR(64) UNIQUE,
    contrato_assinado_em DATETIME,
    contrato_assinante_nome VARCHAR(255),
    contrato_assinante_ip VARCHAR(45),
    contrato_assinante_user_agent TEXT,
    
    -- Transferência de Fundos
    transferencia_valor DECIMAL(10,2),
    transferencia_data DATE,
    transferencia_comprovante_path VARCHAR(255),
    transferencia_user_id INT,
    transferencia_em DATETIME,
    
    -- Boletos
    boletos_gerados BOOLEAN DEFAULT 0,
    boletos_api_response JSON,
    boletos_gerados_em DATETIME,
    
    -- Status e Controle
    status ENUM(
        'calculado',
        'aguardando_contrato',
        'aguardando_assinatura',
        'aguardando_transferencia',
        'aguardando_boletos',
        'concluido'
    ) DEFAULT 'calculado',
    
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by_user_id INT,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    INDEX idx_client (client_id),
    INDEX idx_status (status),
    INDEX idx_token (contrato_token)
);
```

### 1.5 Tabela: loan_parcelas
Armazena as parcelas de cada empréstimo (tabela Price).

```sql
CREATE TABLE loan_parcelas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    loan_id INT NOT NULL,
    
    -- Dados da Parcela
    numero_parcela INT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_vencimento DATE NOT NULL,
    juros_embutido DECIMAL(10,2) NOT NULL,
    amortizacao DECIMAL(10,2) NOT NULL,
    saldo_devedor DECIMAL(10,2) NOT NULL,
    
    -- Dados do Boleto
    boleto_id VARCHAR(100),
    boleto_url TEXT,
    boleto_codigo_barras VARCHAR(100),
    boleto_linha_digitavel VARCHAR(100),
    
    -- Status de Pagamento
    status ENUM('pendente', 'pago', 'vencido', 'cancelado') DEFAULT 'pendente',
    pago_em DATETIME,
    valor_pago DECIMAL(10,2),
    
    FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE,
    INDEX idx_loan (loan_id),
    INDEX idx_vencimento (data_vencimento),
    INDEX idx_status (status)
);
```

### 1.6 Tabela: audit_log
Registra todas as ações importantes do sistema.

```sql
CREATE TABLE audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    tabela VARCHAR(50),
    registro_id INT,
    acao VARCHAR(50) NOT NULL,
    descricao TEXT,
    ip VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user (user_id),
    INDEX idx_tabela (tabela),
    INDEX idx_acao (acao),
    INDEX idx_created (created_at)
);
```

---

## 2. AUTENTICAÇÃO E USUÁRIOS

### 2.1 Usuários Hardcoded

Criar arquivo `config/users.php`:

```php
<?php
return [
    [
        'id' => 1,
        'username' => 'operador1',
        'password' => password_hash('senha123', PASSWORD_DEFAULT),
        'nome' => 'Operador 1'
    ],
    [
        'id' => 2,
        'username' => 'operador2',
        'password' => password_hash('senha456', PASSWORD_DEFAULT),
        'nome' => 'Operador 2'
    ],
    [
        'id' => 3,
        'username' => 'operador3',
        'password' => password_hash('senha789', PASSWORD_DEFAULT),
        'nome' => 'Operador 3'
    ]
];
```

### 2.2 Sistema de Login

**Tela: `/login`**
- Formulário com username e password
- Validação contra array hardcoded
- Criação de sessão com `$_SESSION['user_id']` e `$_SESSION['user_nome']`
- Registro em audit_log (acao='login')

**Middleware de Autenticação:**
- Verificar sessão em todas as rotas exceto `/login` e `/assinar/{token}`
- Redirecionar para login se não autenticado

---

## 3. MÓDULO 0: CONFIGURAÇÕES

### Tela: `/config`

Interface para editar configurações do sistema.

**Campos editáveis:**
- Taxa de Juros Padrão Mensal (%)
- Multa por Atraso (%)
- Juros de Mora ao Dia (%)
- Nome da Empresa
- CNPJ da Empresa
- Endereço da Empresa

**Funcionalidade:**
- Listar todas configurações da tabela `config`
- Permitir edição inline ou modal
- Botão "Salvar Configurações"
- UPDATE na tabela config
- Registrar em audit_log

---

## 4. MÓDULO 1: CADASTRO DE CLIENTE

### 4.1 Tela: `/clientes/novo`

Formulário dividido em blocos:

#### Bloco 1: Dados Pessoais
- Nome Completo (text, required)
- CPF (text com mask 000.000.000-00, required, unique)
- Data de Nascimento (date, required)
- Email (email)
- Telefone (text com mask (00) 00000-0000)

#### Bloco 2: Endereço
- CEP (text com mask 00000-000)
- Botão "Buscar" → integração com ViaCEP
- Endereço (autocomplete via CEP)
- Número (text)
- Complemento (text)
- Bairro (autocomplete via CEP)
- Cidade (autocomplete via CEP)
- Estado (select, autocomplete via CEP)

**Integração ViaCEP:**
```javascript
fetch(`https://viacep.com.br/ws/${cep}/json/`)
  .then(res => res.json())
  .then(data => {
    // Preencher campos: endereco, bairro, cidade, estado
  });
```

#### Bloco 3: Dados Profissionais
- Ocupação (text)
- Tempo de Trabalho (text, ex: "2 anos")
- Renda Mensal (number com mask BRL)

#### Bloco 4: Documentos
- **Holerites:**
  - Upload múltiplo (accept: .pdf, .jpg, .png)
  - Placeholder: "Envie os 3 últimos holerites"
  - Salvar array de paths em JSON

- **CNH/RG:**
  - Toggle: "Documento com frente e verso no mesmo arquivo?"
  - Se SIM: 1 upload
  - Se NÃO: 2 uploads (Frente / Verso)

- **Selfie:**
  - Upload único (accept: .jpg, .png)

#### Bloco 5: Observações
- Textarea (opcional)

**Botão: "Salvar Cliente"**
- Validações frontend: CPF válido, campos obrigatórios preenchidos
- Validações backend: CPF único, formatos corretos
- Upload de arquivos para `/uploads/{client_id}/` (criar ID antes)
- INSERT em `clients`
- Registrar em `audit_log` (acao='create', tabela='clients')
- Redirecionar para `/clientes/{id}/validar`

---

### 4.2 Tela: `/clientes/{id}/validar`

Painel de validação com 2 cards:

#### Card 1: Prova de Vida

**Layout:**
- Grid 2 colunas responsivo
- Coluna esquerda: Imagem CNH (frente)
- Coluna direita: Imagem Selfie
- Ambas imagens ampliáveis (lightbox)

**Status atual:** Badge colorido (Pendente/Aprovado/Reprovado)

**Ações:**
- Botão "✅ Aprovar Prova de Vida"
  - UPDATE `clients` SET `prova_vida_status='aprovado'`, `prova_vida_data=NOW()`, `prova_vida_user_id={session_user_id}`
  - Registrar em audit_log
  
- Botão "❌ Reprovar Prova de Vida"
  - UPDATE `clients` SET `prova_vida_status='reprovado'`, `prova_vida_data=NOW()`, `prova_vida_user_id={session_user_id}`
  - Modal para inserir motivo (salvar em observacoes)
  - Registrar em audit_log

#### Card 2: Consulta CPF

**Status atual:** Badge colorido (Pendente/Aprovado/Reprovado)

**Botão: "Consultar CPF na Receita Federal"**

Ao clicar:
1. GET `https://api.cpfcnpj.com.br/[versao]/{cpf}?package=8`
2. Header: `Authorization: Bearer {token}` (token do .env)
3. Salvar JSON completo em `cpf_checks.json_response`
4. Decodificar `situacaoComprovantePdf` (base64) e salvar em `/uploads/{client_id}/cpf_comprovante.pdf`
5. INSERT em `cpf_checks`

**Exibir dados:**
- Nome
- CPF
- Data de Nascimento
- Situação (Regular/Irregular com badge)
- Data da Consulta
- Botão "📄 Ver Comprovante PDF" (abrir em nova aba)

**Ações:**
- Botão "✅ Aprovar Consulta CPF"
  - UPDATE `clients` SET `cpf_check_status='aprovado'`, `cpf_check_data=NOW()`, `cpf_check_user_id={session_user_id}`
  - Registrar em audit_log
  
- Botão "❌ Reprovar Consulta CPF"
  - UPDATE `clients` SET `cpf_check_status='reprovado'`...
  - Modal para motivo
  - Registrar em audit_log

**Indicador Visual:**
- Só permitir prosseguir se ambas validações = 'aprovado'
- Mostrar checklist: ✓ Prova de Vida | ✓ Consulta CPF

---

## 5. MÓDULO 2: EMPRÉSTIMO

### 5.1 Tela: `/emprestimos/calculadora`

#### Calculadora Bidirecional (Sistema Price - Juros Compostos)

**Inputs (todos interligados):**
1. Valor do Empréstimo (R$)
2. Número de Parcelas
3. Taxa de Juros Mensal (%) - carrega de config, editável
4. Data do Primeiro Vencimento (date picker)

**Cálculos Automáticos (JavaScript):**

```javascript
// 1. Dias até primeiro vencimento
const hoje = new Date();
const dataVenc = new Date(dataPrimeiroVencimento);
const diasPrimeiroPeriodo = Math.floor((dataVenc - hoje) / (1000*60*60*24));

// 2. Juros proporcional
let jurosProp = 0;
if (diasPrimeiroPeriodo > 30) {
    jurosProp = valorPrincipal * (taxaMensal/100) * (diasPrimeiroPeriodo/30);
}

// 3. Valor da Parcela (Price)
const i = taxaMensal / 100;
const n = numParcelas;
const principal = valorPrincipal + jurosProp;
const PMT = principal * (i * Math.pow(1+i, n)) / (Math.pow(1+i, n) - 1);

// 4. Valor Total
const valorTotal = PMT * numParcelas;

// 5. Total de Juros
const totalJuros = valorTotal - valorPrincipal;

// 6. CET (simplificado)
const cetMensal = (Math.pow(valorTotal/valorPrincipal, 1/numParcelas) - 1) * 100;
const cetAnual = (Math.pow(1 + cetMensal/100, 12) - 1) * 100;
```

**Exibição:**
- Campos calculados em destaque (readonly, formatados em BRL)
- Seção expansível: "📊 Ver Tabela de Amortização Completa"
  - Tabela com todas as parcelas:
    - Nº | Vencimento | Valor | Juros | Amortização | Saldo Devedor

**Seleção de Cliente:**
- Select com autocomplete (busca por nome ou CPF)
- FILTRO: apenas clientes com `prova_vida_status='aprovado' AND cpf_check_status='aprovado'`
- Se lista vazia: mostrar mensagem "Nenhum cliente aprovado disponível"

**Botão: "Gerar Solicitação de Empréstimo"**

Validações:
- Cliente selecionado
- Todos os campos preenchidos
- Valor > 0, Parcelas > 0

Ações:
1. INSERT em `loans`:
   - Todos os valores calculados
   - `status='calculado'`
   - `created_by_user_id={session_user_id}`
   
2. INSERT em `loan_parcelas` (loop para cada parcela):
   - Calcular tabela Price completa
   - Campos: numero_parcela, valor, data_vencimento, juros_embutido, amortizacao, saldo_devedor

3. Registrar em audit_log (acao='create_loan')

4. Redirecionar para `/emprestimos/{id}`

---

### 5.2 Tela: `/emprestimos/{id}`

#### Cabeçalho
- Nome do Cliente
- Resumo: R$ {principal} → R$ {total} em {num_parcelas}x de R$ {parcela}
- Status atual (badge grande e colorido)

#### Fluxo Sequencial

Mostrar cards de acordo com o status atual, sugerindo sempre a próxima ação.

---

#### ETAPA 1: Gerar Contrato

**Condição:** `status IN ('calculado', 'aguardando_contrato')`

**Card: Geração de Contrato**

Botão: "📄 Gerar Contrato"

Ao clicar:
1. Carregar template HTML do contrato (`templates/contrato.html`)
2. Fazer merge de dados:
   - **Empresa:** config (nome, CNPJ, endereço)
   - **Cliente:** clients (todos os dados pessoais)
   - **Empréstimo:** loans (valor, parcelas, taxa, CET, total juros)
   - **Multa/Juros:** config (multa_percentual, juros_mora_percentual_dia)
   - **Cronograma:** loan_parcelas (tabela completa)
3. Salvar HTML em `loans.contrato_html`
4. UPDATE `loans.status = 'aguardando_assinatura'`
5. Exibir preview do contrato em modal ou nova seção

**Botão: "🔗 Gerar Link de Assinatura"**

Ao clicar:
1. Gerar token único:
   ```php
   $token = bin2hex(random_bytes(32));
   UPDATE loans SET contrato_token = $token WHERE id = $loan_id;
   ```
2. Criar URL: `https://seudominio.com/assinar/{token}`
3. Copiar para clipboard (JavaScript)
4. Exibir link na tela com botão "Copiar Link"

---

#### Página Pública: `/assinar/{token}`

**Layout simples (sem autenticação, sem menu):**

Validações:
- Token existe?
- Contrato já foi assinado? (se sim, mostrar mensagem)

**Conteúdo:**
1. Cabeçalho: Logo da empresa
2. Título: "Contrato de Empréstimo Digital"
3. Contrato completo em HTML (readonly, estilizado)
4. Formulário de assinatura:
   - Nome Completo (input text, required)
   - Data (input date, readonly, value = hoje)
   - Checkbox: "□ Li e concordo com todos os termos deste contrato" (required)
   - Botão "✍️ Assinar Contrato" (disabled até marcar checkbox)

**Ao clicar "Assinar Contrato":**

```php
// Capturar dados
$nome = $_POST['nome'];
$ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];

// Atualizar banco
UPDATE loans SET
    contrato_assinado_em = NOW(),
    contrato_assinante_nome = $nome,
    contrato_assinante_ip = $ip,
    contrato_assinante_user_agent = $user_agent,
    status = 'aguardando_transferencia'
WHERE contrato_token = $token;

// Gerar PDF
// Usar biblioteca: TCPDF ou mPDF
// Conteúdo: HTML do contrato + rodapé com dados da assinatura
$pdf_content = $contrato_html . "
    <div style='margin-top: 50px; border-top: 2px solid #000; padding-top: 20px;'>
        <p><strong>CONTRATO ASSINADO DIGITALMENTE</strong></p>
        <p>Assinado por: {$nome}</p>
        <p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>
        <p>IP: {$ip}</p>
    </div>
";
// Salvar em /uploads/{client_id}/contrato_assinado.pdf
// UPDATE loans.contrato_pdf_path

// Registrar em audit_log
INSERT INTO audit_log (user_id, tabela, registro_id, acao, descricao, ip, user_agent)
VALUES (NULL, 'loans', $loan_id, 'assinatura_contrato', 'Contrato assinado por ' . $nome, $ip, $user_agent);

// Exibir mensagem de sucesso
"✅ Contrato assinado com sucesso! Você receberá uma cópia por email."
```

**Na tela `/emprestimos/{id}` após assinatura:**
- Badge: "Contrato Assinado em {data}"
- Botão: "📄 Ver Contrato Assinado" (download PDF)
- Mostrar próximo card (Transferência)

---

#### ETAPA 2: Enviar Fundos

**Condição:** `status = 'aguardando_transferencia'`

**Card: Transferência de Fundos**

Exibir:
- "💰 Valor a Transferir: R$ {valor_principal}"
- Input: Data da Transferência (date, default = hoje)
- Upload: Comprovante de PIX/TED (accept: .pdf, .jpg, .png)

Botão: "💰 Confirmar Transferência"

Validações:
- Data informada
- Arquivo enviado

Ações:
```php
// Salvar comprovante
$path = "/uploads/{client_id}/comprovante_transferencia.{ext}";

// Atualizar banco
UPDATE loans SET
    transferencia_valor = $valor_principal,
    transferencia_data = $data,
    transferencia_comprovante_path = $path,
    transferencia_user_id = {session_user_id},
    transferencia_em = NOW(),
    status = 'aguardando_boletos'
WHERE id = $loan_id;

// Registrar em audit_log
INSERT INTO audit_log (...) VALUES ('transferencia_fundos', ...);
```

Feedback:
- Mensagem de sucesso
- Atualizar status visual
- Mostrar próximo card (Boletos)

---

#### ETAPA 3: Gerar Boletos

**Condição:** `status = 'aguardando_boletos'`

**Card: Geração de Cobranças**

**Resumo Financeiro:**
```
┌─────────────────────────────────┐
│ Principal:    R$ {principal}    │
│ Total Juros:  R$ {total_juros}  │
│ Total a Receber: R$ {total}     │
└─────────────────────────────────┘
```

**Configurações (carregadas de config):**
- Multa por Atraso: {multa_percentual}%
- Juros de Mora: {juros_mora_percentual_dia}% ao dia

**Tabela de Parcelas:**
| # | Vencimento | Valor | Juros | Amortização | Saldo | Status |
|---|------------|-------|-------|-------------|-------|--------|
| 1 | 15/01/2026 | R$ XX | R$ X  | R$ Y        | R$ Z  | Pendente |
| ... | ... | ... | ... | ... | ... | ... |

**Botões:**

1. **"🔄 Gerar Cobranças via API"** (primário, azul)
   
   Ação:
   - Verificar se API está configurada (verificar .env ou config)
   - Se NÃO configurada:
     - Modal: "⚠️ API de boletos ainda não configurada. Configure em /config/api-boletos"
   - Se SIM configurada:
     - Preparar payload:
       ```json
       {
         "cliente": {
           "nome": "...",
           "cpf": "...",
           "email": "..."
         },
         "parcelas": [
           {
             "numero": 1,
             "valor": 150.00,
             "vencimento": "2026-01-15",
             "multa": 2.0,
             "juros_dia": 0.033
           },
           ...
         ]
       }
       ```
     - POST para API de boletos
     - Salvar response em `loans.boletos_api_response` (JSON)
     - Para cada parcela retornada:
       ```php
       UPDATE loan_parcelas SET
           boleto_id = $response['parcelas'][$i]['id'],
           boleto_url = $response['parcelas'][$i]['url'],
           boleto_codigo_barras = $response['parcelas'][$i]['codigo_barras'],
           boleto_linha_digitavel = $response['parcelas'][$i]['linha_digitavel']
       WHERE loan_id = $loan_id AND numero_parcela = $i;
       ```
     - UPDATE `loans` SET `boletos_gerados = 1`, `boletos_gerados_em = NOW()`, `status = 'concluido'`
     - Registrar em audit_log
     - Exibir mensagem de sucesso com link para boletos

2. **"📝 Informar Geração Manual"** (secundário, cinza)
   
   Ação:
   - Modal com checkbox: "Confirmo que os boletos foram gerados manualmente"
   - Ao confirmar:
     - UPDATE `loans` SET `boletos_gerados = 1`, `boletos_gerados_em = NOW()`
     - Registrar em audit_log (acao='boletos_manuais')
     - Manter status = 'aguardando_boletos' (não avança automaticamente)

---

#### ETAPA 4: Finalizar

**Condição:** `status IN ('aguardando_boletos', 'concluido')`

**Card: Finalização do Empréstimo**

Checklist de validações:
- ✓ Contrato assinado em {data}
- ✓ Fundos transferidos em {data} por {usuario}
- ✓ Boletos gerados em {data}

Botão: "✅ Marcar como Concluído"

Validações:
```php
if (!$loan->contrato_assinado_em || 
    !$loan->transferencia_em || 
    !$loan->boletos_gerados) {
    return error("Todas as etapas devem ser concluídas");
}
```

Ações:
- UPDATE `loans` SET `status = 'concluido'`
- Registrar em audit_log (acao='loan_concluido')
- Mensagem: "🎉 Empréstimo finalizado com sucesso!"
- Redirecionar para `/dashboard` ou `/emprestimos`

---

## 6. MÓDULO 3: ADMIN/CRUD

### Tela: `/admin`

Interface administrativa com tabs para gerenciar todas as tabelas.

**Tabs:**
1. Clientes
2. Empréstimos
3. Parcelas
4. Consultas CPF
5. Configurações
6. Log de Auditoria

**Funcionalidades por tab (exceto Log):**
- Listagem com DataTables.js:
  - Busca global
  - Ordenação por coluna
  - Paginação
  - Filtros customizados
- Ações por registro:
  - 👁️ Ver detalhes (modal ou página)
  - ✏️ Editar (modal com formulário)
  - 🗑️ Excluir (confirmação + soft delete)
- Todas as ações registram em audit_log

**Tab: Log de Auditoria** (readonly)
- Filtros:
  - Usuário (select)
  - Tabela (select)
  - Ação (select)
  - Período (date range)
- Colunas: Data/Hora | Usuário | Tabela | Registro ID | Ação | Descrição | IP
- Export para CSV/Excel

---

## 7. DASHBOARD

### Tela: `/dashboard` (página inicial após login)

**Cards de Resumo (KPIs):**
```
┌─────────────────────┐  ┌─────────────────────┐  ┌─────────────────────┐  ┌─────────────────────┐
│ 👥 Total Clientes   │  │ 💼 Empréstimos      │  │ 💰 Valor em Carteira│  │ ⚠️ Inadimplência    │
│       150           │  │    Ativos: 45       │  │    R$ 1.250.000     │  │       3.5%          │
│                     │  │    Concluídos: 105  │  │                     │  │                     │
└─────────────────────┘  └─────────────────────┘  └─────────────────────┘  └─────────────────────┘
```

**Seção: Últimos Empréstimos**
- Tabela com 10 últimos registros:
  - ID | Cliente | Valor | Parcelas | Status | Ações
- Botão "Ver Todos"

**Seção: Ações Rápidas**
- Botão: "➕ Novo Cliente"
- Botão: "💵 Novo Empréstimo"
- Botão: "⚙️ Configurações"

**Seção: Validações Pendentes**
- Lista de clientes com validações pendentes:
  - Nome | CPF | Pendências (badges) | Ação (botão "Validar")

---

## 8. ARMAZENAMENTO DE ARQUIVOS

### Estrutura de Diretórios

```
/uploads/
├── {client_id}/
│   ├── holerites/
│   │   ├── {uuid}_holerite1.pdf
│   │   ├── {uuid}_holerite2.pdf
│   │   └── {uuid}_holerite3.pdf
│   ├── documentos/
│   │   ├── {uuid}_cnh_frente.jpg
│   │   ├── {uuid}_cnh_verso.jpg
│   │   └── {uuid}_selfie.jpg
│   ├── comprovantes/
│   │   ├── cpf_comprovante.pdf
│   │   └── {uuid}_transferencia.pdf
│   └── contratos/
│       └── contrato_assinado.pdf
```

### Regras de Upload

1. **Validações:**
   - Tamanho máximo: 10MB por arquivo
   - Tipos permitidos:
     - Documentos: .pdf, .jpg, .jpeg, .png
   - Validar mime-type real (não confiar apenas na extensão)

2. **Segurança:**
   - Renomear todos os arquivos com UUID: `{uuid}_{original_name}`
   - Não executar arquivos uploadados
   - Armazenar fora do document root se possível
   - Servir arquivos via script PHP com autenticação

3. **Exemplo de função de upload:**
```php
function uploadFile($file, $client_id, $tipo) {
    // Validar arquivo
    $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        throw new Exception('Tipo de arquivo não permitido');
    }
    
    if ($file['size'] > 10 * 1024 * 1024) { // 10MB
        throw new Exception('Arquivo muito grande');
    }
    
    // Gerar path
    $uuid = bin2hex(random_bytes(16));
    $filename = $uuid . '_' . basename($file['name']);
    $dir = "/uploads/{$client_id}/{$tipo}/";
    
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $path = $dir . $filename;
    
    // Mover arquivo
    if (!move_uploaded_file($file['tmp_name'], $path)) {
        throw new Exception('Erro ao salvar arquivo');
    }
    
    return $path;
}
```

---

## 9. INTEGRAÇÕES DE API

### 9.1 ViaCEP (Busca de Endereço)

**Endpoint:** `https://viacep.com.br/ws/{cep}/json/`

**Uso:**
```javascript
async function buscarCEP(cep) {
    const cepLimpo = cep.replace(/\D/g, '');
    
    if (cepLimpo.length !== 8) return;
    
    try {
        const response = await fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`);
        const data = await response.json();
        
        if (data.erro) {
            alert('CEP não encontrado');
            return;
        }
        
        // Preencher campos
        document.getElementById('endereco').value = data.logradouro;
        document.getElementById('bairro').value = data.bairro;
        document.getElementById('cidade').value = data.localidade;
        document.getElementById('estado').value = data.uf;
        
    } catch (error) {
        console.error('Erro ao buscar CEP:', error);
    }
}
```

### 9.2 CPF/CNPJ API (Consulta CPF)

**Endpoint:** `https://api.cpfcnpj.com.br/[versao]/{cpf}?package=8`

**Autenticação:** Bearer Token (armazenar no .env)

**Exemplo de uso:**
```php
function consultarCPF($cpf) {
    $cpf_limpo = preg_replace('/\D/', '', $cpf);
    $token = getenv('CPFCNPJ_API_TOKEN'); // Do .env
    
    $url = "https://api.cpfcnpj.com.br/5.0/{$cpf_limpo}?package=8";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$token}"
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        throw new Exception('Erro na consulta CPF');
    }
    
    $data = json_decode($response, true);
    
    // Salvar PDF
    if (isset($data['situacaoComprovantePdf'])) {
        $pdf_base64 = $data['situacaoComprovantePdf'];
        $pdf_content = base64_decode($pdf_base64);
        $pdf_path = "/uploads/{$client_id}/cpf_comprovante.pdf";
        file_put_contents($pdf_path, $pdf_content);
        
        $data['pdf_path'] = $pdf_path;
    }
    
    return $data;
}
```

### 9.3 API de Boletos (A DEFINIR)

**Placeholder para integração futura:**

Preparar estrutura para aceitar configuração de API:
- Asaas
- Pagarme
- Mercado Pago
- Outro

**Tela de configuração:** `/config/api-boletos`
- Select: Provedor (Asaas, Pagarme, etc)
- Inputs: API Key, Webhook URL, etc
- Salvar em tabela config ou arquivo .env

**Interface genérica:**
```php
interface BoletoAPIInterface {
    public function gerarBoletos($cliente, $parcelas);
    public function consultarBoleto($boleto_id);
    public function cancelarBoleto($boleto_id);
}

// Implementar para cada provedor
class AsaasAPI implements BoletoAPIInterface { ... }
class PagarmeAPI implements BoletoAPIInterface { ... }
```

---

## 10. SEGURANÇA

### 10.1 Variáveis de Ambiente (.env)

Criar arquivo `.env` na raiz do projeto:

```env
# Banco de Dados
DB_HOST=localhost
DB_NAME=sistema_emprestimos
DB_USER=root
DB_PASS=senha_segura

# APIs
CPFCNPJ_API_TOKEN=bccbaf9e9af13fc49739b8a43fff0fe8
BOLETOS_API_KEY=
BOLETOS_API_PROVIDER=

# App
APP_URL=https://seudominio.com
APP_ENV=production
APP_DEBUG=false

# Upload
UPLOAD_MAX_SIZE=10485760
```

**IMPORTANTE:** Adicionar `.env` ao `.gitignore`

### 10.2 Prepared Statements (PDO)

**Sempre usar prepared statements para prevenir SQL Injection:**

```php
// ❌ ERRADO
$sql = "SELECT * FROM clients WHERE cpf = '$cpf'";

// ✅ CORRETO
$sql = "SELECT * FROM clients WHERE cpf = :cpf";
$stmt = $pdo->prepare($sql);
$stmt->execute(['cpf' => $cpf]);
```

### 10.3 Validação de Uploads

```php
function validarUpload($file) {
    // Validar extensão
    $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed_ext)) {
        throw new Exception('Extensão não permitida');
    }
    
    // Validar mime-type REAL
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed_mime = [
        'application/pdf',
        'image/jpeg',
        'image/png'
    ];
    
    if (!in_array($mime, $allowed_mime)) {
        throw new Exception('Tipo de arquivo não permitido');
    }
    
    // Validar tamanho
    if ($file['size'] > 10 * 1024 * 1024) {
        throw new Exception('Arquivo muito grande (máx 10MB)');
    }
    
    return true;
}
```

### 10.4 HTTPS

**Obrigatório em produção.**

Redirecionar HTTP para HTTPS:
```php
if ($_SERVER['HTTPS'] !== 'on' && getenv('APP_ENV') === 'production') {
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('Location: ' . $redirect, true, 301);
    exit();
}
```

### 10.5 Headers de Segurança

```php
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
```

---

## 11. LOG DE AUDITORIA

### Ações que devem ser registradas:

1. **Autenticação:**
   - login
   - logout
   - falha_login

2. **Clientes:**
   - create
   - update
   - delete
   - aprovar_prova_vida
   - reprovar_prova_vida
   - aprovar_cpf
   - reprovar_cpf
   - consultar_cpf

3. **Empréstimos:**
   - create_loan
   - gerar_contrato
   - gerar_link_assinatura
   - assinatura_contrato (sem user_id, salvar IP do cliente)
   - transferencia_fundos
   - gerar_boletos_api
   - boletos_manuais
   - loan_concluido
   - update
   - delete

4. **Configurações:**
   - update_config

5. **Admin:**
   - admin_view
   - admin_edit
   - admin_delete

### Função auxiliar:

```php
function registrarAuditoria($acao, $tabela, $registro_id, $descricao = null) {
    global $pdo;
    
    $user_id = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $sql = "INSERT INTO audit_log 
            (user_id, tabela, registro_id, acao, descricao, ip, user_agent) 
            VALUES 
            (:user_id, :tabela, :registro_id, :acao, :descricao, :ip, :user_agent)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'user_id' => $user_id,
        'tabela' => $tabela,
        'registro_id' => $registro_id,
        'acao' => $acao,
        'descricao' => $descricao,
        'ip' => $ip,
        'user_agent' => $user_agent
    ]);
}

// Uso:
registrarAuditoria('create', 'clients', $client_id, 'Cliente João Silva cadastrado');
```

---

## 12. TEMPLATE DE CONTRATO

### Arquivo: `templates/contrato.html`

Criar template HTML com placeholders para merge de dados:

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Contrato de Empréstimo</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; }
        .section { margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .assinatura { margin-top: 50px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CONTRATO DE EMPRÉSTIMO PESSOAL</h1>
        <p>Contrato Nº: {{LOAN_ID}}</p>
    </div>

    <div class="section">
        <h2>1. PARTES</h2>
        <p><strong>CREDOR:</strong> {{EMPRESA_NOME}}, CNPJ {{EMPRESA_CNPJ}}, 
        com sede em {{EMPRESA_ENDERECO}}.</p>
        
        <p><strong>DEVEDOR:</strong> {{CLIENTE_NOME}}, CPF {{CLIENTE_CPF}}, 
        nascido(a) em {{CLIENTE_NASCIMENTO}}, residente em {{CLIENTE_ENDERECO}}.</p>
    </div>

    <div class="section">
        <h2>2. DO OBJETO</h2>
        <p>O CREDOR concede ao DEVEDOR um empréstimo no valor de 
        <strong>R$ {{VALOR_PRINCIPAL}}</strong> ({{VALOR_PRINCIPAL_EXTENSO}}), 
        a ser pago em <strong>{{NUM_PARCELAS}} parcelas</strong> mensais de 
        <strong>R$ {{VALOR_PARCELA}}</strong>.</p>
    </div>

    <div class="section">
        <h2>3. DAS CONDIÇÕES FINANCEIRAS</h2>
        <ul>
            <li>Taxa de Juros Mensal: {{TAXA_JUROS}}% ao mês</li>
            <li>CET (Custo Efetivo Total): {{CET}}% ao ano</li>
            <li>Valor Total a Pagar: R$ {{VALOR_TOTAL}}</li>
            <li>Total de Juros: R$ {{TOTAL_JUROS}}</li>
            <li>Data do Primeiro Vencimento: {{DATA_PRIMEIRO_VENCIMENTO}}</li>
        </ul>
    </div>

    <div class="section">
        <h2>4. DO CRONOGRAMA DE PAGAMENTO</h2>
        <table>
            <thead>
                <tr>
                    <th>Parcela</th>
                    <th>Vencimento</th>
                    <th>Valor</th>
                    <th>Juros</th>
                    <th>Amortização</th>
                    <th>Saldo Devedor</th>
                </tr>
            </thead>
            <tbody>
                {{TABELA_PARCELAS}}
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>5. DAS PENALIDADES</h2>
        <p>Em caso de atraso no pagamento de qualquer parcela, serão aplicados:</p>
        <ul>
            <li>Multa de {{MULTA_PERCENTUAL}}% sobre o valor da parcela</li>
            <li>Juros de mora de {{JUROS_MORA_DIA}}% ao dia</li>
        </ul>
    </div>

    <div class="section">
        <h2>6. DAS DISPOSIÇÕES GERAIS</h2>
        <p>[Cláusulas contratuais padrão...]</p>
    </div>

    <div class="assinatura">
        <p>{{CIDADE}}, {{DATA_CONTRATO}}</p>
        <br><br>
        <p>_________________________________________</p>
        <p>{{EMPRESA_NOME}}<br>CREDOR</p>
        <br><br>
        <p>_________________________________________</p>
        <p>{{CLIENTE_NOME}}<br>DEVEDOR</p>
    </div>
</body>
</html>
```

### Função de merge:

```php
function gerarContratoHTML($loan_id) {
    global $pdo;
    
    // Buscar dados
    $sql = "SELECT l.*, c.*, cfg.* 
            FROM loans l 
            JOIN clients c ON l.client_id = c.id
            CROSS JOIN (SELECT * FROM config) cfg
            WHERE l.id = :loan_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['loan_id' => $loan_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Buscar parcelas
    $sql_parcelas = "SELECT * FROM loan_parcelas WHERE loan_id = :loan_id ORDER BY numero_parcela";
    $stmt_parcelas = $pdo->prepare($sql_parcelas);
    $stmt_parcelas->execute(['loan_id' => $loan_id]);
    $parcelas = $stmt_parcelas->fetchAll(PDO::FETCH_ASSOC);
    
    // Gerar tabela HTML de parcelas
    $tabela_parcelas = '';
    foreach ($parcelas as $p) {
        $tabela_parcelas .= "<tr>
            <td>{$p['numero_parcela']}</td>
            <td>" . date('d/m/Y', strtotime($p['data_vencimento'])) . "</td>
            <td>R$ " . number_format($p['valor'], 2, ',', '.') . "</td>
            <td>R$ " . number_format($p['juros_embutido'], 2, ',', '.') . "</td>
            <td>R$ " . number_format($p['amortizacao'], 2, ',', '.') . "</td>
            <td>R$ " . number_format($p['saldo_devedor'], 2, ',', '.') . "</td>
        </tr>";
    }
    
    // Carregar template
    $template = file_get_contents('templates/contrato.html');
    
    // Fazer merge
    $placeholders = [
        '{{LOAN_ID}}' => str_pad($loan_id, 6, '0', STR_PAD_LEFT),
        '{{EMPRESA_NOME}}' => $data['empresa_nome'],
        '{{EMPRESA_CNPJ}}' => $data['empresa_cnpj'],
        '{{EMPRESA_ENDERECO}}' => $data['empresa_endereco'],
        '{{CLIENTE_NOME}}' => $data['nome'],
        '{{CLIENTE_CPF}}' => $data['cpf'],
        '{{CLIENTE_NASCIMENTO}}' => date('d/m/Y', strtotime($data['data_nascimento'])),
        '{{CLIENTE_ENDERECO}}' => "{$data['endereco']}, {$data['numero']}, {$data['cidade']}/{$data['estado']}",
        '{{VALOR_PRINCIPAL}}' => number_format($data['valor_principal'], 2, ',', '.'),
        '{{NUM_PARCELAS}}' => $data['num_parcelas'],
        '{{VALOR_PARCELA}}' => number_format($data['valor_parcela'], 2, ',', '.'),
        '{{TAXA_JUROS}}' => number_format($data['taxa_juros_mensal'], 2, ',', '.'),
        '{{CET}}' => number_format($data['cet_percentual'], 2, ',', '.'),
        '{{VALOR_TOTAL}}' => number_format($data['valor_total'], 2, ',', '.'),
        '{{TOTAL_JUROS}}' => number_format($data['total_juros'], 2, ',', '.'),
        '{{DATA_PRIMEIRO_VENCIMENTO}}' => date('d/m/Y', strtotime($data['data_primeiro_vencimento'])),
        '{{TABELA_PARCELAS}}' => $tabela_parcelas,
        '{{MULTA_PERCENTUAL}}' => $data['multa_percentual'],
        '{{JUROS_MORA_DIA}}' => $data['juros_mora_percentual_dia'],
        '{{CIDADE}}' => $data['cidade'],
        '{{DATA_CONTRATO}}' => date('d/m/Y'),
    ];
    
    $html = str_replace(array_keys($placeholders), array_values($placeholders), $template);
    
    return $html;
}
```

---

## 13. INTERFACE (UI/UX)

### 13.1 Framework CSS: TailwindCSS + Preline.co

Usar componentes Preline para:
- Formulários
- Tabelas (DataTables)
- Modais
- Navegação
- Cards
- Badges
- Botões

### 13.2 Layout Base

**Sidebar + Content:**
```html
<div class="flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-800 text-white min-h-screen">
        <div class="p-4">
            <h1 class="text-xl font-bold">Sistema Empréstimos</h1>
            <p class="text-sm text-gray-400">Olá, {{USER_NOME}}</p>
        </div>
        <nav>
            <a href="/dashboard" class="block px-4 py-2 hover:bg-gray-700">Dashboard</a>
            <a href="/clientes" class="block px-4 py-2 hover:bg-gray-700">Clientes</a>
            <a href="/emprestimos" class="block px-4 py-2 hover:bg-gray-700">Empréstimos</a>
            <a href="/admin" class="block px-4 py-2 hover:bg-gray-700">Admin</a>
            <a href="/config" class="block px-4 py-2 hover:bg-gray-700">Configurações</a>
            <a href="/logout" class="block px-4 py-2 hover:bg-gray-700">Sair</a>
        </nav>
    </aside>
    
    <!-- Content -->
    <main class="flex-1 p-8">
        <!-- Conteúdo da página -->
    </main>
</div>
```

### 13.3 Máscaras de Input

Usar biblioteca: `IMask.js` ou `Cleave.js`

```javascript
// CPF
IMask(document.getElementById('cpf'), {
    mask: '000.000.000-00'
});

// Telefone
IMask(document.getElementById('telefone'), {
    mask: '(00) 00000-0000'
});

// CEP
IMask(document.getElementById('cep'), {
    mask: '00000-000'
});

// Moeda (BRL)
IMask(document.getElementById('valor'), {
    mask: 'R$ num',
    blocks: {
        num: {
            mask: Number,
            thousandsSeparator: '.',
            radix: ',',
            scale: 2
        }
    }
});
```

### 13.4 Validações Frontend

Usar biblioteca: `Validator.js` ou validação nativa HTML5

```html
<!-- Exemplo -->
<input type="text" 
       id="cpf" 
       required 
       pattern="\d{3}\.\d{3}\.\d{3}-\d{2}"
       title="Digite um CPF válido">
```

---

## 14. ESTRUTURA DE ARQUIVOS DO PROJETO

```
/sistema-emprestimos/
├── config/
│   ├── database.php
│   ├── users.php
│   └── app.php
├── public/
│   ├── index.php
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
├── src/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   └── Helpers/
├── templates/
│   └── contrato.html
├── uploads/
│   └── .gitkeep
├── .env
├── .gitignore
├── composer.json
└── README.md
```

---

## 15. BIBLIOTECAS RECOMENDADAS

### PHP
- **PDO:** Banco de dados (nativo)
- **TCPDF ou mPDF:** Geração de PDF
- **vlucas/phpdotenv:** Gerenciar .env

### JavaScript
- **IMask.js:** Máscaras de input
- **DataTables.js:** Tabelas interativas
- **SweetAlert2:** Modais/alertas bonitos
- **Axios:** Requisições AJAX

### CSS
- **TailwindCSS:** Framework CSS
- **Preline.co:** Componentes prontos

---

## 16. DEPLOY E PRODUÇÃO

### Checklist:

1. ✅ Configurar .env com credenciais reais
2. ✅ APP_ENV=production e APP_DEBUG=false
3. ✅ HTTPS configurado (SSL)
4. ✅ Backup automático do banco de dados
5. ✅ Permissões de pasta corretas (uploads 755)
6. ✅ .gitignore configurado (.env, /uploads/, /vendor/)
7. ✅ Logs de erro configurados
8. ✅ Rate limiting em APIs
9. ✅ Monitoramento de uptime

---

## 17. PRÓXIMOS PASSOS (PÓS-MVP)

Funcionalidades futuras a considerar:

1. **Notificações:**
   - Email/SMS em cada etapa
   - Lembrete de vencimento de parcelas

2. **Relatórios:**
   - Export Excel/PDF
   - Gráficos de performance

3. **Webhooks:**
   - Receber confirmação de pagamento de boletos

4. **Permissões:**
   - Diferentes níveis de acesso (admin, operador, visualizador)

5. **API REST:**
   - Para integração com outros sistemas

6. **Mobile:**
   - App ou PWA para clientes

---

## 18. CONSIDERAÇÕES FINAIS

Este sistema deve ser desenvolvido priorizando:
- **Segurança:** dados sensíveis (CPF, RG, documentos)
- **Auditabilidade:** log completo de todas as ações
- **Usabilidade:** interface intuitiva para operadores
- **Conformidade:** LGPD (Lei Geral de Proteção de Dados)

**IMPORTANTE:** Antes de entrar em produção, consultar um advogado para validar:
- Template de contrato
- Processo de assinatura digital
- Armazenamento de dados pessoais
- Conformidade com regulamentações financeiras

---

**FIM DA ESPECIFICAÇÃO**

Desenvolvedor: siga esta especificação para criar um sistema robusto, seguro e funcional.
