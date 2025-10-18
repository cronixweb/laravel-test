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
        $tenantId = (int) $request->user()->tenant_id;
        $selectedPeriod = $this->resolvePeriod($request->query('period', 'month'));
        $periodStart = $this->periodStart($selectedPeriod);

        $expenses = Expense::query()
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', $periodStart)
            ->orderByDesc('created_at')
            ->get();

        $typeBreakdown = $this->groupByType($expenses);
        $totalForSelectedPeriod = $expenses->sum('amount');

        $totals = [
            'day' => $this->sumForPeriod('day', $tenantId),
            'week' => $this->sumForPeriod('week', $tenantId),
            'month' => $this->sumForPeriod('month', $tenantId),
        ];

        return view('expenses.index', [
            'selectedPeriod' => $selectedPeriod,
            'periods' => self::PERIODS,
            'typeBreakdown' => $typeBreakdown,
            'totalForSelectedPeriod' => $totalForSelectedPeriod,
            'totals' => $totals,
            'expenses' => $expenses->take(10),
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
            'tenant_id' => $request->user()->tenant_id,
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

    private function sumForPeriod(string $period, int $tenantId): float
    {
        $start = $this->periodStart($period);

        return (float) Expense::query()
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', $start)
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
}
