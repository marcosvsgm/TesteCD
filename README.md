# Sistema de Vendas - Teste DC Tecnologia

Sistema simples de vendas desenvolvido em Laravel para o teste da vaga de desenvolvedor júnior da DC Tecnologia. O projeto inclui autenticação, cadastro de vendas com itens e parcelas, filtros por período e entidades relacionadas, além de geração de PDF com resumo da venda.

## Sobre o projeto

O sistema foi pensado para ser simples, organizado e funcional, com foco em:

- Login com autenticação padrão do Laravel
- Usuários representando vendedores
- Cadastro, edição, listagem e exclusão de vendas
- Itens de venda com cálculo automático de total
- Parcelas com vencimento, valor e status
- Filtros por cliente, vendedor, forma de pagamento e intervalo de datas
- PDF com resumo completo da venda
- CRUDs auxiliares de clientes, produtos e formas de pagamento
- Dashboard com indicadores e últimas vendas

## Tecnologias usadas

- PHP 8.3
- Laravel 13
- Blade
- Bootstrap 5
- JavaScript com jQuery
- MySQL
- DomPDF via `barryvdh/laravel-dompdf`
- Vite

## Como instalar localmente

1. Clone o repositório:

```bash
git clone https://github.com/seu-usuario/dc-tecnologia-vendas.git
cd dc-tecnologia-vendas
```

2. Instale as dependências PHP:

```bash
composer install
```

3. Crie o arquivo de ambiente:

```bash
cp .env.example .env
```

No Windows PowerShell, se preferir:

```powershell
Copy-Item .env.example .env
```

4. Gere a chave da aplicação:

```bash
php artisan key:generate
```

5. Configure o banco no arquivo `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=dc_tecnologia_sales
DB_USERNAME=postgres
DB_PASSWORD=
```

6. Rode migrations e seeders:

```bash
php artisan migrate --seed
```

7. Instale as dependências frontend:

```bash
npm install
```

8. Gere os assets para desenvolvimento local:

```bash
npm run build
```

9. Suba o servidor local:

```bash
php artisan serve
```

O sistema ficará disponível em `http://127.0.0.1:8000`.

## Como rodar migrations e seeders

Para recriar toda a base com os dados de exemplo:

```bash
php artisan migrate:fresh --seed
```

O seeder principal é `InitialDataSeeder` e foi preparado para uso local e produção com os mesmos dados iniciais.

## Dados de acesso

- Vendedor padrão:
  - Nome: `Marcos Souza`
  - E-mail: `vendedor@teste.com`
  - Senha: `12345678`

## Dados de exemplo incluídos

- Clientes:
  - João Silva
  - Maria Oliveira
  - Empresa Alpha LTDA
  - Cliente Avulso

- Produtos:
  - Notebook Dell - R$ 3.500,00
  - Mouse Sem Fio - R$ 80,00
  - Teclado Mecânico - R$ 250,00
  - Monitor 24 Polegadas - R$ 900,00
  - Impressora Epson - R$ 1.200,00

- Formas de pagamento:
  - Dinheiro
  - Pix
  - Cartão de Crédito
  - Cartão de Débito
  - Boleto

Também são criadas vendas de exemplo com itens e parcelas para testar listagem, edição, exclusão, filtros e geração de PDF.

## Como gerar PDF

Na listagem de vendas, use o botão `PDF` de qualquer registro. O sistema gera e baixa automaticamente um resumo contendo:

- Cliente
- Vendedor
- Data da venda
- Itens
- Forma de pagamento
- Parcelas
- Total da venda

