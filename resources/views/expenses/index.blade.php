@extends('layouts.app')

@section('title', 'Expense Overview')

@push('head')
    <style>
        .grid {
            display: grid;
            gap: 1.5rem;
        }
        .grid.flexible {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
        @media (min-width: 768px) {
            .grid.cols-3 {
                grid-template-columns: repeat(3, 1fr);
            }
            .grid.cols-2 {
                grid-template-columns: repeat(2, 1fr);
            }
            .grid.flexible {
                grid-template-columns: repeat(3, minmax(0, 1fr));
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
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }
        form.filter label {
            font-weight: 600;
            display: flex;
            flex-direction: column;
            font-size: 0.85rem;
        }
        form.filter select {
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #cbd5f5;
            font: inherit;
            margin-top: 0.35rem;
        }
        .chart-grid {
            display: grid;
            gap: 1.25rem;
        }
        .chart-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
            min-height: 320px;
        }
        .chart-card:hover {
            border-color: #93c5fd;
            box-shadow: 0 12px 30px -18px rgba(30, 64, 175, 0.45);
            transform: translateY(-2px);
        }
        .chart-card-header {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        @media (min-width: 768px) {
            .chart-card-header {
                flex-direction: row;
                align-items: flex-start;
                justify-content: space-between;
            }
        }
        .chart-card h3 {
            margin: 0;
            font-size: 1rem;
        }
        .chart-caption {
            margin: 0;
            color: #475569;
            font-size: 0.85rem;
        }
        .chart-controls {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .chart-controls label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            flex-direction: column;
        }
        .chart-controls select {
            margin-top: 0.35rem;
            padding: 0.4rem 0.65rem;
            border-radius: 0.5rem;
            border: 1px solid #cbd5f5;
            font: inherit;
            background: #fff;
        }
        .chart-surface {
            flex: 1;
            min-height: 220px;
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
            <label for="period">
                Date Range
                <select id="period" name="period" onchange="this.form.submit()">
                    @foreach ($periods as $period)
                        <option value="{{ $period }}" @selected($period === $selectedPeriod)>{{ ucfirst($period) }}</option>
                    @endforeach
                </select>
            </label>
            <label for="type">
                Type
                <select id="type" name="type" onchange="this.form.submit()">
                    @foreach ($typeOptions as $value => $label)
                        <option value="{{ $value }}" @selected($value === $selectedType)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <span style="color:#64748b;">Total: <strong>INR {{ number_format($totalForSelectedPeriod, 2) }}</strong></span>
        </form>
        <div class="grid cols-3" style="margin-bottom: 1.5rem;">
            <div class="metric">
                <span>Today</span>
                <strong>INR {{ number_format($totals['day'], 2) }}</strong>
            </div>
            <div class="metric">
                <span>This Week</span>
                <strong>INR {{ number_format($totals['week'], 2) }}</strong>
            </div>
            <div class="metric">
                <span>This Month</span>
                <strong>INR {{ number_format($totals['month'], 2) }}</strong>
            </div>
        </div>
        @php
            $chartCards = [
                [
                    'title' => 'Spending Breakdown',
                    'caption' => 'Compare total expenses by category',
                    'defaultMetric' => 'amount',
                    'defaultType' => 'pie',
                ],
                [
                    'title' => 'Transactions Share',
                    'caption' => 'See how frequently each type occurs',
                    'defaultMetric' => 'transactions',
                    'defaultType' => 'doughnut',
                ],
                [
                    'title' => 'Average Ticket Size',
                    'caption' => 'Average spend per recorded expense type',
                    'defaultMetric' => 'average',
                    'defaultType' => 'polarArea',
                ],
            ];
        @endphp
        <div class="chart-grid">
            @foreach ($chartCards as $index => $card)
                <div
                    class="chart-card"
                    data-chart-index="{{ $index }}"
                    data-default-type="{{ $card['defaultType'] }}"
                    data-default-metric="{{ $card['defaultMetric'] }}"
                >
                    <div class="chart-card-header">
                        <div>
                            <h3>{{ $card['title'] }}</h3>
                            <p class="chart-caption js-chart-highlight">{{ $card['caption'] }}</p>
                        </div>
                        <div class="chart-controls">
                            <label>
                                Chart
                                <select class="js-chart-type">
                                    <option value="pie">Pie</option>
                                    <option value="doughnut">Donut</option>
                                    <option value="polarArea">Poll</option>
                                </select>
                            </label>
                            <label>
                                Metric
                                <select class="js-chart-metric">
                                    <option value="amount">Total Amount</option>
                                    <option value="transactions">Transactions</option>
                                    <option value="average">Average Amount</option>
                                </select>
                            </label>
                        </div>
                    </div>
                    <div class="chart-surface">
                        <canvas height="240"></canvas>
                    </div>
                </div>
            @endforeach
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
                        <td>INR {{ number_format((float) $expense->amount, 2) }}</td>
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
        const chartPayload = @json($chartPayload);
        const palette = ['#0ea5e9', '#22c55e', '#f97316', '#6366f1', '#fbbf24', '#ec4899'];

        const formatters = {
            currency(value) {
                return `${chartPayload.currency} ${Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            },
            count(value) {
                const rounded = Number(value);
                return `${rounded} ${rounded === 1 ? 'entry' : 'entries'}`;
            },
        };

        function buildConfig(type, metricKey) {
            const metric = chartPayload.metrics[metricKey] ?? chartPayload.metrics.amount;
            const background = palette.slice(0, chartPayload.labels.length);
            const formatter = formatters[metric.unit] ?? (value => value);

            const config = {
                type,
                data: {
                    labels: chartPayload.labels,
                    datasets: [
                        {
                            data: metric.data,
                            backgroundColor: background,
                            borderWidth: type === 'polarArea' ? 1 : 0,
                            borderColor: type === 'polarArea' ? background : '#fff',
                            hoverOffset: 10,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                            },
                        },
                        tooltip: {
                            callbacks: {
                                label(context) {
                                    const label = context.label || '';
                                    const value = context.parsed ?? 0;
                                    return `${label}: ${formatter(value)}`;
                                },
                            },
                        },
                    },
                    layout: {
                        padding: 4,
                    },
                    scales: type === 'polarArea' ? {
                        r: {
                            ticks: {
                                display: false,
                            },
                            grid: {
                                circular: true,
                            },
                        },
                    } : {},
                },
            };

            if (type === 'doughnut') {
                config.options.cutout = '55%';
            }

            return { config, formatter, metric };
        }

        function renderHighlight(element, metric, formatter) {
            const data = metric.data;
            const total = data.reduce((sum, value) => sum + Number(value), 0);
            const peakIndex = data.reduce((best, value, idx, arr) => (value > arr[best] ? idx : best), 0);
            const topLabel = chartPayload.labels[peakIndex] ?? '—';

            element.textContent = `${metric.label}: ${formatter(total)} · Top: ${topLabel}`;
        }

        document.querySelectorAll('.chart-card').forEach((card) => {
            const canvas = card.querySelector('canvas');
            const typeSelect = card.querySelector('.js-chart-type');
            const metricSelect = card.querySelector('.js-chart-metric');
            const caption = card.querySelector('.js-chart-highlight');

            const initialType = card.dataset.defaultType || 'pie';
            const initialMetric = card.dataset.defaultMetric || 'amount';

            typeSelect.value = initialType;
            metricSelect.value = initialMetric;

            let chartInstance;

            const refresh = () => {
                const currentType = typeSelect.value;
                const currentMetric = metricSelect.value;
                const { config, formatter, metric } = buildConfig(currentType, currentMetric);

                if (chartInstance) {
                    chartInstance.destroy();
                }

                chartInstance = new Chart(canvas, config);
                renderHighlight(caption, metric, formatter);
            };

            typeSelect.addEventListener('change', refresh);
            metricSelect.addEventListener('change', refresh);

            refresh();
        });
    </script>
@endpush
