<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    private const PERIODS = ['day', 'week', 'month'];

    public function index(Request $request): View
    {
        $selectedPeriod = $this->resolvePeriod($request->query('period', 'month'));
        $selectedType = $this->resolveType($request->query('type', 'all'));
        $periodStart = $this->periodStart($selectedPeriod);

        $expenses = Expense::query()
            ->where('created_at', '>=', $periodStart)
            ->when($selectedType !== 'all', fn ($query) => $query->where('type', $selectedType))
            ->orderByDesc('created_at')
            ->get();

        $typeBreakdown = $this->groupByType($expenses);
        $typeCounts = $this->countByType($expenses);
        $typeAverages = $this->averageByType($typeBreakdown, $typeCounts);
        $totalForSelectedPeriod = $expenses->sum('amount');
        $typeOptions = $this->typeOptions();

        $totals = [
            'day' => $this->sumForPeriod('day', $selectedType),
            'week' => $this->sumForPeriod('week', $selectedType),
            'month' => $this->sumForPeriod('month', $selectedType),
        ];

        return view('expenses.index', [
            'selectedPeriod' => $selectedPeriod,
            'selectedType' => $selectedType,
            'periods' => self::PERIODS,
            'typeBreakdown' => $typeBreakdown,
            'typeCounts' => $typeCounts,
            'typeAverages' => $typeAverages,
            'chartPayload' => $this->prepareChartPayload($typeBreakdown, $typeCounts, $typeAverages),
            'totalForSelectedPeriod' => $totalForSelectedPeriod,
            'totals' => $totals,
            'expenses' => $expenses->take(10),
            'typeOptions' => $typeOptions,
        ]);
    }

    public function create(): View
    {
        return view('expenses.create', [
            'types' => Expense::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'description' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:' . implode(',', Expense::TYPES)],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        Expense::create([
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'amount' => $data['amount'],
        ]);

        return redirect()
            ->route('expenses.create')
            ->with('status', 'Expense recorded successfully.');
    }

    private function resolvePeriod(string $period): string
    {
        return in_array($period, self::PERIODS, true) ? $period : 'month';
    }

    private function periodStart(string $period): Carbon
    {
        $now = Carbon::now();

        return match ($period) {
            'day' => $now->copy()->startOfDay(),
            'week' => $now->copy()->startOfWeek(),
            default => $now->copy()->startOfMonth(),
        };
    }

    private function resolveType(string $type): string
    {
        return $type === 'all' || in_array($type, Expense::TYPES, true)
            ? $type
            : 'all';
    }

    private function typeOptions(): array
    {
        $options = ['all' => 'All Types'];

        foreach (Expense::TYPES as $type) {
            $options[$type] = ucfirst($type);
        }

        return $options;
    }

    private function sumForPeriod(string $period, string $type): float
    {
        $start = $this->periodStart($period);

        return (float) Expense::query()
            ->where('created_at', '>=', $start)
            ->when($type !== 'all', fn ($query) => $query->where('type', $type))
            ->sum('amount');
    }

    private function groupByType(Collection $expenses): array
    {
        $breakdown = [];

        foreach (Expense::TYPES as $type) {
            $breakdown[$type] = 0.0;
        }

        foreach ($expenses as $expense) {
            $breakdown[$expense->type] += (float) $expense->amount;
        }

        return $breakdown;
    }

    private function countByType(Collection $expenses): array
    {
        $counts = [];

        foreach (Expense::TYPES as $type) {
            $counts[$type] = 0;
        }

        foreach ($expenses as $expense) {
            $counts[$expense->type]++;
        }

        return $counts;
    }

    /**
     * @param  array<string, float>  $amounts
     * @param  array<string, int>  $counts
     * @return array<string, float>
     */
    private function averageByType(array $amounts, array $counts): array
    {
        $averages = [];

        foreach (Expense::TYPES as $type) {
            $count = $counts[$type] ?? 0;
            $total = $amounts[$type] ?? 0.0;

            $averages[$type] = $count > 0 ? round($total / $count, 2) : 0.0;
        }

        return $averages;
    }

    /**
     * @param  array<string, float>  $amounts
     * @param  array<string, int>  $counts
     * @param  array<string, float>  $averages
     * @return array{labels: array<int, string>, metrics: array<string, array{label: string, unit: string, data: array<int, float>}>, currency: string}
     */
    private function prepareChartPayload(array $amounts, array $counts, array $averages): array
    {
        $labels = array_map('ucfirst', array_keys($amounts));

        return [
            'labels' => $labels,
            'metrics' => [
                'amount' => [
                    'label' => 'Total Amount',
                    'unit' => 'currency',
                    'data' => array_map('floatval', array_values($amounts)),
                ],
                'transactions' => [
                    'label' => 'Transactions',
                    'unit' => 'count',
                    'data' => array_map('intval', array_values($counts)),
                ],
                'average' => [
                    'label' => 'Average Amount',
                    'unit' => 'currency',
                    'data' => array_map('floatval', array_values($averages)),
                ],
            ],
            'currency' => 'INR',
        ];
    }
}
