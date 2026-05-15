<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Resumo da Venda #{{ $sale->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1, h2 { margin: 0 0 10px; }
        .section { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <div class="section">
        <h1>Resumo da Venda #{{ $sale->id }}</h1>
        <p>Cliente: {{ $sale->customer?->name ?? 'Sem cliente' }}</p>
        <p>Vendedor: {{ $sale->seller->name }}</p>
        <p>Data da venda: {{ $sale->sale_date->format('d/m/Y') }}</p>
        <p>Forma de pagamento: {{ $sale->payment_method_names }}</p>
    </div>

    <div class="section">
        <h2>Itens</h2>
        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Valor unitário</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($item->total, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Parcelas</h2>
        <table>
            <thead>
                <tr>
                    <th>Forma de pagamento</th>
                    <th>Vencimento</th>
                    <th>Valor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->installments as $installment)
                    <tr>
                        <td>{{ $installment->paymentMethod?->name ?? 'N/A' }}</td>
                        <td>{{ $installment->due_date->format('d/m/Y') }}</td>
                        <td>R$ {{ number_format($installment->amount, 2, ',', '.') }}</td>
                        <td>{{ $installment->status === 'paid' ? 'Paga' : 'Pendente' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Total da venda</h2>
        <p>R$ {{ number_format($sale->total, 2, ',', '.') }}</p>
    </div>
</body>
</html>
