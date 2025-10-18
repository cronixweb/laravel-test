<?php

namespace Tests\Feature;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_expense(): void
    {
        $response = $this->post(route('expenses.store'), [
            'description' => 'Lunch with team',
            'type' => 'personal',
            'amount' => '150.25',
        ]);

        $response
            ->assertRedirect(route('expenses.create'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('expenses', [
            'description' => 'Lunch with team',
            'type' => 'personal',
        ]);

        $expense = Expense::first();

        $this->assertNotNull($expense);
        $this->assertSame('150.25', (string) $expense->amount);
    }

    public function test_overview_page_displays_breakdown_and_totals(): void
    {
        Carbon::setTestNow(Carbon::parse('2024-01-15 12:00:00'));

        Expense::create([
            'description' => 'Daily commute',
            'type' => Expense::TYPE_PERSONAL,
            'amount' => 100,
        ]);

        Carbon::setTestNow(Carbon::parse('2024-01-10 09:00:00'));
        Expense::create([
            'description' => 'Office supplies',
            'type' => Expense::TYPE_WORK,
            'amount' => 200,
        ]);

        Carbon::setTestNow(Carbon::parse('2023-12-20 18:30:00'));
        Expense::create([
            'description' => 'Home repairs',
            'type' => Expense::TYPE_HOUSE,
            'amount' => 300,
        ]);

        Carbon::setTestNow(Carbon::parse('2024-01-15 12:00:00'));

        $response = $this->get(route('expenses.index', ['period' => 'month']));

        $response->assertOk();
        $response->assertViewHas('selectedPeriod', 'month');
        $response->assertViewHas('totals', function (array $totals) {
            return $totals['day'] === 100.0
                && $totals['week'] === 100.0
                && $totals['month'] === 300.0;
        });
        $response->assertViewHas('typeBreakdown', function (array $breakdown) {
            return $breakdown[Expense::TYPE_PERSONAL] === 100.0
                && $breakdown[Expense::TYPE_WORK] === 200.0
                && $breakdown[Expense::TYPE_HOUSE] === 0.0;
        });

        Carbon::setTestNow();
    }
}

