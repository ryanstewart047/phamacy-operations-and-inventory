<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Expense;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportingService
{
    public function dailySalesSummary(?Carbon $date = null): array
    {
        $date = $date ?? now();

        $sales = Sale::whereDate('sold_at', $date->toDateString())
            ->where('status', 'completed')
            ->get();

        return [
            'total_sales' => $sales->sum('total_amount'),
            'transactions' => $sales->count(),
            'profit' => $sales->sum('profit_total'),
            'average_ticket' => $sales->count() ? $sales->average('total_amount') : 0,
        ];
    }

    public function salesTrend(int $days = 7): Collection
    {
        $start = now()->subDays($days - 1)->startOfDay();

        return Sale::select([
                DB::raw('DATE(sold_at) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('SUM(profit_total) as profit'),
            ])
            ->where('status', 'completed')
            ->where('sold_at', '>=', $start)
            ->groupBy(DB::raw('DATE(sold_at)'))
            ->orderBy('date')
            ->get();
    }

    public function profitAndLoss(Carbon $from, Carbon $to): array
    {
        $sales = Sale::whereBetween('sold_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->where('status', 'completed')
            ->get();

        $expenses = Expense::whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])->get();

        $totalIncome = $sales->sum('total_amount');
        $totalCost = $sales->sum('cost_total');
        $totalExpenses = $expenses->sum('amount');

        return [
            'income' => $totalIncome,
            'cogs' => $totalCost,
            'gross_profit' => $totalIncome - $totalCost,
            'operating_expenses' => $totalExpenses,
            'net_profit' => $totalIncome - $totalCost - $totalExpenses,
        ];
    }
}
