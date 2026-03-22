<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function index()
    {
        // 1. Fetch last 30 days of visitor data
        $days = 30;
        $stats = collect();
        $labels = [];
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::now()->subDays($i)->format('d.m.');

            $visit = Counter::where('page', "daily:$date")->first();
            $count = $visit ? $visit->visits : 0;
            $data[] = $count;

            $stats->push([
                'date' => $date,
                'label' => Carbon::now()->subDays($i)->format('d.m.Y'),
                'count' => $count,
            ]);
        }

        // 2. Key Metrics
        $todayCount = $data[count($data) - 1];
        $yesterdayCount = $data[count($data) - 2] ?? 0;
        $totalLast7Days = array_sum(array_slice($data, -7));
        $avgLast30Days = round(array_sum($data) / $days, 1);
        $peak = max($data);
        $peakDateList = array_keys($data, $peak);
        $peakDate = $labels[$peakDateList[0]] ?? 'N/A';

        // 3. Overall Totals
        $allTimeTotal = Counter::where('page', 'all')->first()?->visits ?? 0;

        return view('admin.stats.index', compact(
            'labels', 'data', 'stats', 'todayCount', 'yesterdayCount',
            'totalLast7Days', 'avgLast30Days', 'peak', 'peakDate', 'allTimeTotal'
        ));
    }
}
