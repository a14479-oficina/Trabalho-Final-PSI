# DevBank - Caixa Multibanco

## Acesso

**Iniciar Sessão:** `/admin/login.php` (único ponto de entrada)

| Tipo | Email | Password | Redireciona para |
|---|---|---|---|
| Admin | `admin@admin` | `admin` | Painel de administração |
| Cliente | `ana.silva@escola.pt` | `gpsi12` | Área do cliente |
| Cliente | `rui.santos@escola.pt` | `gpsi12` | Área do cliente |

**Multibanco:** `/atm/index.php` (autenticação por cartão + PIN)

## Clientes

| Nome | NIF | Email | Password |
|---|---|---|---|
| Ana Silva | 254123987 | `ana.silva@escola.pt` | `gpsi12` |
| Rui Santos | 210987654 | `rui.santos@escola.pt` | `gpsi12` |

## Contas Bancárias

| ID | Cliente | Número da Conta | Tipo |
|---|---|---|---|
| 1 | Ana Silva | PT5000010001234567890 | Corrente |
| 2 | Ana Silva | PT5000010001234567891 | Poupança |
| 3 | Rui Santos | PT5000020009876543210 | Corrente |

## Cartões de Débito (Multibanco)

| Número do Cartão | PIN | Conta Associada | Validade |
|---|---|---|---|
| 5044123456789012 | `1234` | Ana Silva - Corrente (ID 1) | 2030-12-31 |
| 5044987654321098 | `1234` | Rui Santos - Corrente (ID 3) | 2029-08-31 |

## Funcionalidades ATM

| Operação | Descrição |
|---|---|
| Consultar Saldo | Ver saldo da conta |
| Levantamento | Levantar dinheiro |
| Pagamentos | Pagar **Luz**, **Água** ou **Lixo** (inserir referência e valor) |
| Transferências | Transferir entre contas |

## Base de Dados (phpMyAdmin)

Acesso: `http://localhost:8081`

| Campo | Valor |
|---|---|
| Servidor | `devbank-mysql` |
| User | `root` |
| Password | `root` |
| Base de dados | `devbank_db` |

## Setup Inicial

```bash
docker exec devbank-php php /var/www/html/setup.php
```

Para redefinir hashes na base de dados existente:

```bash
docker exec devbank-php php /var/www/html/fix_hashes.php
```
