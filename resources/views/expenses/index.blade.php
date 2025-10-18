@extends('layouts.app')

@section('title', 'Expense Overview')

@push('head')
    <style>
        .grid {
            display: grid;
            gap: 1.5rem;
        }
        @media (min-width: 768px) {
            .grid.cols-3 {
                grid-template-columns: repeat(3, 1fr);
            }
            .grid.cols-2 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        .metric {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        .metric span {
            font-size: 0.9rem;
            color: #64748b;
        }
        .metric strong {
            font-size: 1.4rem;
        }
        form.filter {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }
        form.filter label {
            font-weight: 600;
        }
        form.filter select {
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #cbd5f5;
            font: inherit;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table thead {
            background: #e2e8f0;
        }
        table th, table td {
            text-align: left;
            padding: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
        }
        table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
    </style>
@endpush

@section('content')
    <div class="card" style="margin-bottom: 1.5rem;">
        <form method="get" class="filter">
            <label for="period">Showing</label>
            <select id="period" name="period" onchange="this.form.submit()">
                @foreach ($periods as $period)
                    <option value="{{ $period }}" @selected($period === $selectedPeriod)>{{ ucfirst($period) }}</option>
                @endforeach
            </select>
            <span style="color:#64748b;">Total: <strong>₹{{ number_format($totalForSelectedPeriod, 2) }}</strong></span>
        </form>
        <div class="grid cols-3" style="margin-bottom: 1.5rem;">
            <div class="metric">
                <span>Today</span>
                <strong>₹{{ number_format($totals['day'], 2) }}</strong>
            </div>
            <div class="metric">
                <span>This Week</span>
                <strong>₹{{ number_format($totals['week'], 2) }}</strong>
            </div>
            <div class="metric">
                <span>This Month</span>
                <strong>₹{{ number_format($totals['month'], 2) }}</strong>
            </div>
        </div>
        <div>
            <canvas id="typeChart" height="220"></canvas>
        </div>
    </div>

    <div class="card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 1rem;">
            <h2 style="margin:0; font-size:1.1rem;">Recent expenses</h2>
            <a class="button" href="{{ route('expenses.create') }}">Add expense</a>
        </div>
        @if ($expenses->isEmpty())
            <p style="margin:0;">No expenses recorded for the selected period yet.</p>
        @else
            <table>
                <thead>
                <tr>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($expenses as $expense)
                    <tr>
                        <td>{{ $expense->description ?? '—' }}</td>
                        <td>{{ ucfirst($expense->type) }}</td>
                        <td>₹{{ number_format((float) $expense->amount, 2) }}</td>
                        <td>{{ $expense->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" integrity="sha384-7d1pxKK8bMJhqaN1bPn/TlGXqxk6W4qYpgixsJioPNTAQz0mIHurxlh2jy+yjWSu" crossorigin="anonymous"></script>
    <script>
        const typeCtx = document.getElementById('typeChart');
        if (typeCtx) {
            const typeData = @json(array_values($typeBreakdown));
            const typeLabels = @json(array_map(fn ($label) => ucfirst($label), array_keys($typeBreakdown)));
            const palette = ['#0ea5e9', '#22c55e', '#f97316'];

            new Chart(typeCtx, {
                type: 'pie',
                data: {
                    labels: typeLabels,
                    datasets: [{
                        data: typeData,
                        backgroundColor: palette,
                        borderWidth: 0,
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: context => {
                                    const value = context.parsed || 0;
                                    return `${context.label}: ₹${value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
@endpush
