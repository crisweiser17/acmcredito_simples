**Lytex**  
Ambiente de Produção: [https://api-pay.lytex.com.br/v2/auth/obtain\_token](https://api-pay.lytex.com.br/v2/auth/obtain_token)  
Criando token de acesso  
Com a integração criada e configurada dentro do painel da LyTex, agora nós vamos utilizar o **Postman** para gerar o token de acesso e também a primeira fatura. Primeiramente, é necessário montar o arquivo **JSON**. Você pode copiar e colar o código abaixo no seu Postman. Após colocar seus dados da integração, basta executá-la e você receberá uma resposta de nossa API com um parâmetro chamado **AccessToken**. O mesmo tem durabilidade de 5 minutos, também receberemos como parâmetros algumas informações referente a esse token.  
Para gerar esse token, utilizarmos a rota **/v2/auth/obtain\_token**  
Feito isso, e com o **AccessToken** salvo, podemos passar para o próximo passo: a geração de faturas.  
  JSON  
  {  
      "grantType": "clientCredentials",  
      "clientId": "SEU Client ID",  
      "clientSecret":"SEU CLIENT SECRET",  
 }

| Código HTTP | Descrição |
| :---- | :---- |
| **200** OK | Sua requisição foi bem sucedida. |
| **400** Bad Request | Algum parâmetro obrigatório não foi enviado ou é inválido. Neste caso a própria resposta indicará qual é o problema. |
| **401** Unauthorized | Não foi enviada API Key ou ela é inválida. |
| **403** Forbidden | Requisição não autorizada. Abuso da API ou uso de parâmetros não permitidos podem gerar este código. |
| **403** Forbidden / Cloudfront (GET) | Erros 403 em chamadas GET indicam que você está enviando um body junto da requisição. Você não deve enviar nenhuma informação no body em chamadas do tipo GET. |
| **404** Not Found | O endpoint ou o objeto solicitado não existe. |
| **429** Too Many Requests | Muitos pedidos em um determinado período de tempo. Mais em nossa seção sobre Rate Limiting. |
| **500** Internal Server Error | Algo deu errado no servidor da Lytex. |

Todos os endpoints da API recebem e respondem em JSON. Exemplo de resposta para HTTP 400:  
{  
  "message": "Requisição inválida",  
  "error": {  
    "\_original": {},  
    "details": \[  
      {  
          "message": "\\"dueDate\\" é obrigatório",  
          "path": \[  
              "dueDate"  
          \],  
          "type": "any.required",  
          "context": {  
              "label": "dueDate",  
              "key": "dueDate"  
          }  
      }  
    \]   
  }  
}

Limites da API  
Possuímos limites de solicitações em certos endpoints onde o excesso pode de certa forma comprometer o desempenho e o uso das APIs da LyTex. Medimos as requisições e podemos restringi-las quando a quantidade permitida é ultrapassada.  
Por padrão o limite é de 60 requisições a cada 10 segundos ou 720 requisições em 2 minutos.  
Se o limite for atingido ou ultrapassado, você receberá um erro HTTP 429 Too Many Requests e ficará bloqueado de fazer requisições por 2 minutos.  
Caso seja reincidente nos bloqueos, poderá ter um bloqueio por 24 horas ou por tempo indeterminado

Webhooks  
Os Webhooks da plataforma Lytex oferecem uma maneira eficiente e flexível de integrar seus aplicativos e sistemas aos eventos em tempo real. Com os Webhooks, você pode receber notificações instantâneas sobre atividades específicas, permitindo uma resposta ágil e automatizada.  
Configuráveis e fáceis de usar, nossos Webhooks permitem que você personalize as ações em resposta a uma variedade de eventos na plataforma Lytex. Seja atualizando dados, acionando processos automatizados ou notificando sua aplicação, os Webhooks da Lytex capacitam você a criar integrações robustas e dinâmicas.  
Simplifique sua integração e mantenha seus sistemas sempre atualizados com os Webhooks da plataforma Lytex.  
Criação de Cobrança  
Quando uma cobrança é criada por meio de recorrência ou link de pagamento na plataforma Lytex, o evento de criação de cobrança é acionado. Implementamos um Webhook exclusivo para este evento, garantindo que você receba todos os dados relevantes assim que a cobrança for gerada.Ao utilizar nosso Webhook de criação de cobrança, você receberá automaticamente todas as informações associadas à cobrança, desde os dados pessoais do cliente até os detalhes específicos dos métodos de pagamento utilizados. Isso inclui informações como nome, endereço, método de pagamento e qualquer outra informação relevante para essas transações, permitindo uma integração perfeita com seus sistemas e processos existentes. Abaixo exemplo detalhado com todos os campos retornados no evento.  
O exemplo de requisição vai ser o mesmo das outras, porém não vai ter data de pagamento, data de cancelamento e valor pago.  
    {  
      "webhookType": "createInvoice",  
      "signature": "Rpd1LaF0BFeSQDl6yC5ylTIvnel/8/mkf6/dLOciglop="  
      "data": {  
        "invoiceId": "65df1d1b1c008a70b5d38345",  
        "status": "waitingPayment",  
        "invoiceValue": 5500,  
        "client": {  
          "name": " LEONARDO ALEXANDRE DA SILVA",  
          "cpfCnpj": "12345678901",  
          "cellphone": "3333333333",  
          "email": "naotem@email.com.br"  
          "address": {  
            "street": "RECIFE",  
            "zone": "SELECIONE",  
            "city": "RECIFE",  
            "state": "PE",  
            "number": "430",  
            "complement": "",  
            "zip": "50860390"  
          },  
        },  
        "referenceId": "b44f8cbb-5afc-477c-95a2-0303030303030"  
        "\_installmentId": "65df1d165df1d1b1c3823e",  
        "\_subscriptionId": "65df1d1b1c65df1d1b1c",  
        "\_paymentLinkId": "65df1d1b11d1b1c65df13e",  
        "customFields": {  
          "name": "value"  
        },  
        "dueDate":"2024-03-07"  
      },  
    }   
Liquidação de Cobrança  
O evento de liquidação de cobrança é um momento crucial na jornada financeira de qualquer negócio. Na plataforma Lytex, implementamos um Webhook especialmente projetado para este evento, garantindo que você receba todos os dados relevantes assim que o pagamento da cobrança for identificado.  
Ao utilizar nosso evento Webhook de liquidação de cobrança, você receberá automaticamente todas as informações associadas à cobrança, desde os dados pessoais do cliente até os detalhes específicos do pagamento. Isso inclui informações como nome, endereço, método de pagamento utilizado e qualquer outra informação relevante para a transação.  
  {  
    "webhookType": "liquidateInvoice",  
    "signature": "Rpd1LaF0BFeSQDl6yC5ylTIvnel/8/mkf6/dLOciglop="  
    "data": {  
      "invoiceId": "65df1d1b1c008a70b5d38345",  
      "status": "paid",  
      "payedAt": "2024-03-07T19:57:04.416Z",  
      "payedValue": 6000,  
      "invoiceValue": 5500,  
      "discount": 0,  
      "mulct": 250,  
      "interest": 250,  
      "paymentMethod": "pix",  
      "client": {  
        "name": " LEONARDO ALEXANDRE DA SILVA",  
        "cpfCnpj": "12345678901",  
        "cellphone": "3333333333",  
        "email": "naotem@email.com.br"  
        "address": {  
          "street": "RECIFE",  
          "zone": "SELECIONE",  
          "city": "RECIFE",  
          "state": "PE",  
          "number": "430",  
          "complement": "",  
          "zip": "50860390"  
        },  
      },  
      "referenceId": "b44f8cbb-5afc-477c-95a2-0303030303030"  
      "\_installmentId": "65df1d165df1d1b1c3823e",  
      "\_subscriptionId": "65df1d1b1c65df1d1b1c",  
      "\_paymentLinkId": "65df1d1b11d1b1c65df13e",  
      "customFields": {  
        "name": "value"  
      },  
      "creditAt": "2024-03-07",  
      "dueDate": "2024-03-07",  
      "rates": 1000  
    },  
  }   
**Agendamento de Pagamento**  
**O evento de agendamento de pagamento é acionado quando é detectado que o pagamento está em processo, porém ainda não foi finalizado. Este pagamento pode ser liquidado no mesmo dia ou em uma data agendada pelo cliente. Na plataforma Lytex, desenvolvemos um Webhook especialmente dedicado a este evento, assegurando que você receba todas as informações pertinentes assim que o processo de pagamento for identificado.**  
**Ao utilizar nosso Webhook para o evento de agendamento de pagamento, você receberá automaticamente todos os dados associados à transação, desde as informações pessoais do cliente até os detalhes específicos do agendamento. Isso engloba dados como nome, endereço, método de pagamento utilizado e qualquer outra informação relevante para a transação em questão.**  
  {  
    "webhookType": "scheduleInvoicePayment",  
    "signature": "QNcQe5HL9bXNaXWzrEjDm4RtS6dvHpD4p9SPu9m2b4r="  
    "data": {  
      "invoiceId": "65cc9eb1b06537f1e3c27234",  
      "status": "processing",  
      "scheduleDate": "2024-03-08T02:59:59.999Z",  
      "scheduledAt": "2024-03-07T19:24:03.000Z",  
      "payedValue": 9990,  
      "paymentMethod": "boleto",  
      "invoiceValue": 9990,  
      "client": {  
        "name": "EDSON NASCIMENTO BONFIM",  
        "cpfCnpj": "12345678901",  
        "cellphone": "3333333333",  
        "email": "sem@gmail.com"  
        "address": {  
          "street": "CURITIBA",  
          "zone": "SANTA CANDIDA",  
          "city": "CURITIBA",  
          "state": "PR",  
          "number": "758",  
          "complement": "BL 06 AP 24",  
          "zip": "82720460"  
        },  
      },  
      "referenceId": "39528175-480d-4bbe-90e9-f2cef2b223455"  
      "\_installmentId": "65df1d165df1d1b1c3823e",  
      "\_subscriptionId": "65df1d1b1c65df1d1b1c",  
      "\_paymentLinkId": "65df1d1b11d1b1c65df13e",  
      "customFields": {  
        "name": "value"  
      }  
    },  
    "dueDate":"2024-03-07"  
  }   
**Cancelamento de Cobrança**  
**O evento de cancelamento de pagamento é acionado quando ocorre a revogação ou anulação de uma transação financeira previamente agendada ou realizada. Na plataforma Lytex, implementamos um Webhook exclusivo para este evento, garantindo que você seja prontamente notificado assim que um pagamento for cancelado.**  
**Ao utilizar nosso Webhook de cancelamento de pagamento, você receberá automaticamente todas as informações relevantes associadas à transação cancelada. Isso inclui detalhes como o ID da cobrança, os dados do cliente, o valor cancelado e qualquer outra informação crucial para entender o motivo e o contexto do cancelamento.**  
**Com essa funcionalidade, você pode agir rapidamente em resposta a cancelamentos de pagamento, atualizando registros em seu sistema ou realizando quaisquer outras ações necessárias para garantir uma gestão financeira eficaz e uma experiência positiva para seus clientes.**  
  {  
    "webhookType": "cancelInvoice",  
    "signature": "Rpd1LaF0BFeSQDl6yC5ylTIvnel/8/mkf6/dLOcig3ed="  
    "data": {  
      "invoiceId": "65df1d1b1c008a70b5d3823e",  
      "status": "canceled",  
      "canceledAt": "2024-03-07T19:57:04.416Z",  
      "invoiceValue": 5500,  
      "client": {  
        "name": " LEONARDO ALEXANDRE DA SILVA",  
        "cpfCnpj": "12345678901",  
        "cellphone": "3333333333",  
        "email": "naotem@email.com.br"  
        "address": {  
          "street": "RECIFE",  
          "zone": "SELECIONE",  
          "city": "RECIFE",  
          "state": "PE",  
          "number": "430",  
          "complement": "",  
          "zip": "50860390"  
        },  
      },  
      "referenceId": "b44f8cbb-5afc-477c-95a2-72289a1c0953",  
      "\_installmentId": "65df1d165df1d1b1c3823e",  
      "\_subscriptionId": "65df1d1b1c65df1d1b1c",  
      "\_paymentLinkId": "65df1d1b11d1b1c65df13e",  
      "customFields": {  
        "name": "value"  
      }  
    },  
    "dueDate":"2024-03-07"  
  } 

**Criação de Cobrança (Boleto/Pix/Cartão)**  
Receber cobranças é a principal maneira de adicionar fundos à sua conta no LyTex. Através delas, você pode receber pagamentos via boleto, cartão de crédito e Pix. Este guia inicial irá orientá-lo sobre como configurar um fluxo para cobranças via boleto.  
Em nossa API em um único endpoint você consegue gerar uma cobrança com todos métodos de pagamento, sendo, Boleto, Pix e Cartão de crédito, o que facilitada no momento de consumir nossas APIs.  
Quando você cria uma cobrança e define os métodos que deseja receber, eles serão gerados automaticamente. É importante ressaltar que as taxas relativas ao pagamento do Boleto, Pix e Cartão de crédito serão deduzidas da sua conta somente se a cobrança for paga.  
Abaixo exemplo de criação de uma cobrança onde já realizamos o cadastro de um cliente previamente, no atributo \_clienteId enviaremos o código do cliente retornado no endpoint de clientes.  
  {  
    "clientId": "12345678901234567890asdf",  
    "value": 10000,  
    "dueDate": "2024-05-28",  
    "paymentMethods": {  
      "pix":  {  
        "enable": true  
      },  
      "boleto": {  
        "enable": true  
      },  
      "creditCard": {  
        "enable": true,  
        "parcels": 1  
      }  
    }  
  }  
Já neste exemplo de criação de uma cobrança não temos o cliente cadastrado previamente, sendo assim, realizaremos o envio dos dados do cliente para que o mesmo seja criado juntamente a criação da cobrança.  
  {  
    "client": {  
      "type": "pf",  
      "name": "João Silva",  
      "cpfCnpj": "08608853051",  
      "email": "joaosilva@lytex.com.br",  
      "cellphone":  "31999999999"  
    },  
    "items":\[{  
      "name": "Produto Exemplo",  
      "quantity" : 1,  
      "value":  300  
    }\],  
    "dueDate": "2024-05-28",  
    "paymentMethods": {  
      "pix":  {  
        "enable": true  
      },  
      "boleto": {  
        "enable": true  
      },  
      "creditCard": {  
        "enable": true,  
        "parcels": 1  
      }  
    }  
  }  
No atributo paymentMethods são enviados os métodos de pagamento que você deseja receber a cobrança, e eles podem ser enviados como true para habilitar e false para desabilitar o tipo de pagamento. No atributo creditCard (recebimento via cartão de crédito) caso habilitado, você deve enviar o número de parcelas que o cliente pode receber dessa cobrança, podendo ser de 1 a 12 parcelas, somente no cartão de crédito.  
Referência completa do endpoint de Cobranças:  
[https://docs-pay.lytex.com.br/documentacao/v2\#tag/Faturas/operation/InvoicesController\_createInvoice](https://docs-pay.lytex.com.br/documentacao/v2#tag/Faturas/operation/InvoicesController_createInvoice)  
**Clientes**  
Como explicado no passo anterior, para criar uma cobrança você precisa de uma cliente, esse cliente pode ser criado tanto utilizando no endpoint de cadastro de clientes ou passando os dados do cliente na própria rota de cobrança. Neste passo, vamos utilizar o endpoint de cliente, abaixo exemplo de uma requisição  
{  
  "type": "pf",  
  "name": "João Silva",  
  "cpfCnpj": "08608853051",  
  "email": "joaosilva@lytex.com.br",  
  "cellphone":  "31999999999"  
}

Endpoint pra criacao de clientes  
[https://api-pay.lytex.com.br/v2/clients](https://api-pay.lytex.com.br/v2/clients)

Exemplo criacao de cliente  
{  
  "type": "pj",  
  "name": "Lytex Soluções",  
  "treatmentPronoun": "you",  
  "cpfCnpj": "34778583000106",  
  "email": "suporte@lytex.com.br",  
  "cellphone": "3198874108",  
  "address": {  
    "street": "Rua Doutor Moacir Bairro",  
    "zone": "Centro",  
    "city": "Cel. Fabriciano",  
    "state": "MG",  
    "number": "325",  
    "complement": "1º andar",  
    "zip": "35170002"  
  },  
  "referenceId": "string"  
}

type (pf ou pj). PF. \= pessoa fisica. PJ \= pessoa juridica.

Pra listar clientes e ver se ja existe  
{  
  "results": \[  
    {  
      "\_id": "string",  
      "name": "string",  
      "email": "string",  
      "type": "pf",  
      "cpfCnpj": "string",  
      "cellphone": "string",  
      "treatmentPronoun": "you",  
      "createdAt": "2019-08-24T14:15:22Z"  
    }  
  \],  
  "paginate": {  
    "perPage": 10,  
    "page": 1,  
    "pages": 1,  
    "total": 0  
  }  
}

Detalhamento de um Cliente (Retorna detalhamento de um cliente)  
[https://api-pay.lytex.com.br/v2/clients/{id}](https://api-pay.lytex.com.br/v2/clients/%7Bid%7D)

{  
  "\_recipientId": null,  
  "name": "string",  
  "treatmentPronoun": "you",  
  "type": "pf",  
  "cpfCnpj": "string",  
  "email": "string",  
  "cellphone": "string",  
  "address": {  
    "street": "string",  
    "zone": "string",  
    "city": "string",  
    "state": "string",  
    "number": "string",  
    "complement": "string",  
    "zip": "string"  
  },  
  "groups": \[  
    {  
      "\_groupId": null,  
      "\_clientGroupId": null  
    }  
  \],  
  "referenceId": "string",  
  "lastInactiveDocument": "2019-08-24T14:15:22Z",  
  "inscricaoMunicipal": "string",  
  "inscricaoEstadual": "string",  
  "\_id": "string",  
  "createdAt": "2019-08-24T14:15:22Z",  
  "updatedAt": "2019-08-24T14:15:22Z"  
}

Apagar um cliente  
[https://api-pay.lytex.com.br/v2/clients/{id}](https://api-pay.lytex.com.br/v2/clients/%7Bid%7D)

{  
  "\_recipientId": null,  
  "name": "string",  
  "treatmentPronoun": "you",  
  "type": "pf",  
  "cpfCnpj": "string",  
  "email": "string",  
  "cellphone": "string",  
  "address": {  
    "street": "string",  
    "zone": "string",  
    "city": "string",  
    "state": "string",  
    "number": "string",  
    "complement": "string",  
    "zip": "string"  
  },  
  "groups": \[  
    {  
      "\_groupId": null,  
      "\_clientGroupId": null  
    }  
  \],  
  "referenceId": "string",  
  "lastInactiveDocument": "2019-08-24T14:15:22Z",  
  "inscricaoMunicipal": "string",  
  "inscricaoEstadual": "string",  
  "\_id": "string",  
  "createdAt": "2019-08-24T14:15:22Z",  
  "updatedAt": "2019-08-24T14:15:22Z"  
}

Autorizacao  
 **OAuth2: lytexAuth**  
**Flow type:** clientCredentials  
**Token URL:** https://sandbox-api-pay.lytex.com.br/v2/auth/obtain\_token  
**Refresh URL:** https://sandbox-api-pay.lytex.com.br/v2/auth/refresh\_token  
