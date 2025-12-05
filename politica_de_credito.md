# Política de Crédito e Score de Pagamento

## Objetivo
- Definir um score de comportamento de pagamento para orientar o valor do próximo empréstimo, prazo e taxa.
- Promover decisões consistentes, auditáveis e transparentes, baseadas em pontualidade e severidade de atrasos.

## Escopo
- Aplica-se a clientes com histórico de parcelas em `loan_parcelas` e empréstimos em `loans`.
- Integra-se com critérios já existentes (`cpf_check_status`, `prova_vida_status`, `criterios_status`).

## Fontes de Dados (Atual)
- Tabela `loans` com metadados e status: `src/Database/Migrator.php:12`, atualização de status: `src/Database/Migrator.php:62`.
- Tabela `loan_parcelas` com status, vencimento e pagamento: `src/Database/Migrator.php:13`.
- Critérios do cliente disponíveis: `src/Database/Migrator.php:84`.
- Atualizações de parcela por webhook (pago / vencido): `src/Services/WebhookQueueService.php:175`, `src/Services/WebhookQueueService.php:198`.
- Atualizações de parcela via interface do empréstimo e marcação de vencidas: `src/Controllers/LoansController.php:179`, `src/Controllers/LoansController.php:215`.

## Definições
- Parcela em dia: `status='pago'` com `pago_em <= data_vencimento`.
- Dias de atraso (DPD):
  - Para `pago`: `max(0, DATEDIFF(pago_em, data_vencimento))`.
  - Para `vencido`: `DATEDIFF(CURDATE(), data_vencimento)`.
- Faixas de atraso: 1–7, 8–30, 31–60, 61+ dias.
- Recência: eventos ocorridos nos últimos 6 meses têm peso 2×; 6–12 meses peso 1×; >12 meses peso 0.5×.

## Score de Pagamento (Versão 1 — Regras Simples)
- Pontos por parcela (antes do peso de recência):
  - Em dia: +2
  - 1–7 dias: +0.5
  - 8–30 dias: −1
  - 31–60 dias: −3
  - 61+ dias: −5
- Bônus e penalidades por empréstimo:
  - Empréstimo concluído sem atraso 30+: +10
  - Renegociação concluída: −5
  - Qualquer atraso 60+ no ciclo: −10
  - Evento de perda/baixa: −30 e teto do score em 20 por 12 meses
- Recência:
  - Multiplicar pontos de parcelas e penalidades por 2× nos últimos 6 meses; 1× de 6–12 meses; 0.5× >12 meses.
- Normalização:
  - `score = clamp(0, 100, 50 + soma_pontos_ajustada)`.
- Salvaguardas de dados escassos:
  - <3 parcelas históricas: score conservador 55 e limitar aumentos.

## Ajustes para Ciclos Curtos (5–6 parcelas)
- Peso das primeiras parcelas: as parcelas 1 e 2 são mais preditivas. Aplicar peso 1.5× para seus pontos/penalidades.
- Peso por ciclo: último ciclo concluído (5–6 parcelas) pesa 3×; ciclo anterior 1.5×; ciclos mais antigos 1×.
- Bônus por ciclo perfeito: +12 pontos se todas as parcelas do ciclo estiverem em dia (sem 8+ dias de atraso).
- Penalidades por severidade no ciclo:
  - Qualquer parcela com 31–60 dias: penalidade adicional −12 no ciclo.
  - Qualquer parcela com 61+ dias: penalidade adicional −20 no ciclo e travar aumentos por 1 ciclo.
- Requisitos para aumento:
  - Exigir que o último ciclo esteja concluído sem atrasos 8+ dias.
  - Para aumentos acima de 15%, exigir 2 ciclos consecutivos sem atrasos 8+ dias.
- Detecção precoce:
  - Se a parcela 1 ou 2 tiver atraso 8+ dias, bloquear aumento no próximo ciclo e aplicar redução de 10–20% (ajustar pelo score global).
- Capacidade de pagamento (pressão da parcela): utilizar `clients.renda_liquida` (ou `renda_mensal` se a primeira estiver nula) e `loans.valor_parcela`.
  - `valor_parcela / renda_liquida <= 0.25`: elegível a aumento conforme score.
  - `> 0.25 e <= 0.35`: preferir manter; aumentos apenas se score ≥ 80 e último ciclo perfeito.
  - `> 0.35`: não aumentar; considerar redução de valor ou prazo mais curto.

## Regras de Decisão por Faixa
- 80–100: aumentar até +20% se sem atraso 8+ dias nos últimos 6 meses e `cpf_check_status='aprovado'`, `criterios_status='aprovado'`.
- 60–79: manter valor; se houver 8–30 dias recentes, reduzir ~10% ou encurtar prazo.
- 40–59: reduzir 10–30%, reforçar documentação e critérios.
- <40: não aumentar; manter mínimo, considerar recusa ou garantias; revisão manual.
- Pricing: taxa ajustada por faixa (risk-based pricing) conforme apetites internos.

## Governança e Estabilidade
- Decaimento temporal: maior peso para eventos recentes; antigos impactam menos.
- Limite de variação: não aumentar >15–20% entre ciclos, mesmo com score alto.
- Histerese: buffer de ±3 pontos para evitar oscilações.
- Fairness/compliance: usar apenas comportamento de pagamento; não usar atributos sensíveis.
- Auditoria: registrar componentes do score usados em cada decisão.

## Integração no Sistema (Planejado)
- Novos campos propostos (não implementados):
  - `clients.score_pagamento INT` (0–100)
  - `clients.score_atualizado_em DATETIME`
  - `loans.score_usado INT`, `loans.politica_decisao JSON`
- Serviço de cálculo (ex.: `CreditScoreService`):
  - Consulta `loan_parcelas` por `client_id` e aplica regras de pontuação e pesos de recência.
  - Ponto de integração: após confirmação de pagamento/vencimento via webhook em `src/Services/WebhookQueueService.php:175` e `src/Services/WebhookQueueService.php:198`, ou na ação manual em `src/Controllers/LoansController.php:179`.
  - Recalcular também ao abrir a calculadora de empréstimos antes de gerar novo contrato.
- UI/Fluxo:
  - Exibir score no perfil do cliente e na calculadora (`src/Controllers/LoansController.php:9`), com recomendação de valor e faixa.
  - Bloqueio por critérios: a calculadora já filtra clientes aprovados (`src/Controllers/LoansController.php:13`). O score atua como orientador do valor/ prazo.
- Auditoria técnica:
  - Usar `\App\Helpers\Audit::log` para registrar score e motivos, seguindo padrões já usados em: `src/Controllers/LoansController.php:77`, `src/Controllers/LoansController.php:185`.

## Exemplo de Cálculo
- Cliente A: 6 parcelas pagas em dia nos últimos 6 meses.
  - Por parcela: +2 × 2× recência = +4; 6 × 4 = +24; Score ≈ 50 + 24 = 74.
  - Faixa: 60–79 → manter; se não houver atraso 8+, pode considerar +10% e prazo igual.
- Cliente B: 4 em dia e 2 com 8–30 dias (todos nos últimos 6 meses).
  - Em dia: 4 × (+2 × 2) = +16; atrasos 8–30: 2 × (−1 × 2) = −4; Soma = +12; Score ≈ 62.
  - Faixa: 60–79 → manter ou reduzir ~10% se pressão de parcela alta.
- Cliente C: 1 atraso 61+ recente, demais em dia.
  - Penalidade por parcela: −5 × 2 = −10; penalidade de ciclo: −10 × 2 = −20 (se aplicável); ganhos em dia podem somar +8; Score ≈ 50 + (8 − 30) = 28.
  - Faixa: <40 → não aumentar; possível recusa ou garantias.

## KPIs e Monitoramento
- Distribuição de score, taxa de inadimplência por faixa, lift (diferença de inad entre faixas), estabilidade mês a mês.
- Validação: ajustar pesos e thresholds com dados reais (KS/Gini simples), sem perder explicabilidade.

## Operacional
- Agendamento: tarefa diária para recalcular score de todos os clientes ativos.
- Antes de concessão: calcular/atualizar score e aplicar política de decisão.
- Logs e relatórios: incluir score e motivos em relatórios operacionais.

## Roadmap de Evolução
- Fase 1: implementar regras simples de pontuação e decisão aqui definidas.
- Fase 2: calibrar pesos com dados, ajustar thresholds e pricing.
- Fase 3: incorporar capacidade de pagamento (parcela/renda) e sazonalidade.
- Fase 4: avaliar modelo estatístico leve (logística/GBM) mantendo transparência.

---

Este documento descreve a política inicial e os pontos de integração no sistema atual. A implementação será feita de forma incremental, garantindo testes e auditoria em cada etapa.