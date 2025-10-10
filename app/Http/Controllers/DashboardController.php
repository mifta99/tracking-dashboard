<?php

namespace App\Http\Controllers;

use App\Models\Insiden;
use App\Models\Keluhan;
use App\Models\Pengiriman;
use App\Models\Puskesmas;
use App\Models\Tahapan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $dataPuskesmasCount = Puskesmas::count();
        foreach (Tahapan::all() as $tahapan) {
            $dataStatus[$tahapan->tahapan] = Pengiriman::where('tahapan_id', $tahapan->id)->count();
        }
        $countDataProvince = Puskesmas::query()
            ->join('districts', 'districts.id', '=', 'puskesmas.district_id')
            ->join('regencies', 'regencies.id', '=', 'districts.regency_id')
            ->join('provinces', 'provinces.id', '=', 'regencies.province_id')
            ->distinct()
            ->count('provinces.id');

        $countRegency = Puskesmas::query()
        ->join('districts', 'districts.id', '=', 'puskesmas.district_id')
        ->join('regencies', 'regencies.id', '=', 'districts.regency_id')
        ->join('provinces', 'provinces.id', '=', 'regencies.province_id')
        ->distinct()
        ->count('regencies.id');

        $countDistrict = Puskesmas::query()
        ->join('districts', 'districts.id', '=', 'puskesmas.district_id')
        ->join('regencies', 'regencies.id', '=', 'districts.regency_id')
        ->join('provinces', 'provinces.id', '=', 'regencies.province_id')
        ->distinct()
        ->count('districts.id');

        

        $tahapan = Tahapan::all();
        return view('dashboard', ['countPuskesmas' => $dataPuskesmasCount, 'dataStatus' => $dataStatus, 'tahapan' => $tahapan , 'countDataProvince' => $countDataProvince, 'countRegency' => $countRegency, 'countDistrict' => $countDistrict]);
    }

    function getDataKeluhanPeriode(Request $request)
    {
        $months = [];
        $complaintCounts = [];

        $endDate = now();
        $startDate = (clone $endDate)->subMonths($request->input('months', 5));

        for ($date = $startDate; $date->lte($endDate); $date->addMonth()) {
            $months[] = $date->format('F Y');
            $complaintCount = Pengiriman::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $complaintCounts[] = $complaintCount;
        }

        return response()->json([
            'months' => $months,
            'complaintCounts' => $complaintCounts,
        ]);

    }

    public function getKeluhanChart(Request $request)
    {
        [$startDate, $endDate] = $this->resolveMonthlyRange($request);

        $reportedQuery = Keluhan::query()
            ->whereNotNull('reported_date')
            ->whereBetween('reported_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('YEAR(reported_date) as year, MONTH(reported_date) as month, COUNT(*) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month');

        $resolvedQuery = Keluhan::query()
            ->whereNotNull('resolved_date')
            ->whereBetween('resolved_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('YEAR(resolved_date) as year, MONTH(resolved_date) as month, COUNT(*) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month');

        if (auth()->user()->role_id == 1) {
            $reportedQuery->where('puskesmas_id', auth()->user()->puskesmas_id);
            $resolvedQuery->where('puskesmas_id', auth()->user()->puskesmas_id);
        }

        $reportedCounts = $reportedQuery->get()->mapWithKeys(function ($item) {
            return [sprintf('%04d-%02d', $item->year, $item->month) => (int) $item->total];
        });

        $resolvedCounts = $resolvedQuery->get()->mapWithKeys(function ($item) {
            return [sprintf('%04d-%02d', $item->year, $item->month) => (int) $item->total];
        });

        [$labels, $keys, $reportedSeries, $resolvedSeries] = $this->buildMonthlySeries(
            $startDate,
            $endDate,
            $reportedCounts,
            $resolvedCounts
        );

        return response()->json([
            'success' => true,
            'labels' => $labels,
            'periods' => $keys,
            'series' => [
                'reported' => $reportedSeries,
                'resolved' => $resolvedSeries,
            ],
            'range' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
        ]);
    }

    public function getInsidenChart(Request $request)
    {
        [$startDate, $endDate] = $this->resolveMonthlyRange($request);

        $reportedQuery = Insiden::query()
            ->whereNotNull('tgl_kejadian')
            ->whereBetween('tgl_kejadian', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('YEAR(tgl_kejadian) as year, MONTH(tgl_kejadian) as month, COUNT(*) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month');

        $resolvedQuery = Insiden::query()
            ->whereNotNull('tgl_selesai')
            ->whereBetween('tgl_selesai', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('YEAR(tgl_selesai) as year, MONTH(tgl_selesai) as month, COUNT(*) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month');

        if (auth()->user()->role_id == 1) {
            $reportedQuery->where('puskesmas_id', auth()->user()->puskesmas_id);
            $resolvedQuery->where('puskesmas_id', auth()->user()->puskesmas_id);
        }

        $reportedCounts = $reportedQuery->get()->mapWithKeys(function ($item) {
            return [sprintf('%04d-%02d', $item->year, $item->month) => (int) $item->total];
        });

        $resolvedCounts = $resolvedQuery->get()->mapWithKeys(function ($item) {
            return [sprintf('%04d-%02d', $item->year, $item->month) => (int) $item->total];
        });

        [$labels, $keys, $reportedSeries, $resolvedSeries] = $this->buildMonthlySeries(
            $startDate,
            $endDate,
            $reportedCounts,
            $resolvedCounts
        );

        return response()->json([
            'success' => true,
            'labels' => $labels,
            'periods' => $keys,
            'series' => [
                'reported' => $reportedSeries,
                'resolved' => $resolvedSeries,
            ],
            'range' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
        ]);
    }

    private function resolveMonthlyRange(Request $request): array
    {
        $defaultStart = now()->copy()->subMonths(4)->startOfMonth();
        $defaultEnd = now()->copy()->endOfMonth();

        try {
            $startDate = $request->filled('start_date')
                ? Carbon::parse($request->input('start_date'))->startOfMonth()
                : $defaultStart->copy();
        } catch (\Exception $e) {
            $startDate = $defaultStart->copy();
        }

        try {
            $endDate = $request->filled('end_date')
                ? Carbon::parse($request->input('end_date'))->endOfMonth()
                : $defaultEnd->copy();
        } catch (\Exception $e) {
            $endDate = $defaultEnd->copy();
        }

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfMonth(), $startDate->copy()->endOfMonth()];
        }

        return [$startDate, $endDate];
    }

    private function buildMonthlySeries(
        Carbon $startDate,
        Carbon $endDate,
        $reportedCounts,
        $resolvedCounts
    ): array {
        $labels = [];
        $keys = [];
        $reportedSeries = [];
        $resolvedSeries = [];

        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            $key = $cursor->format('Y-m');
            $keys[] = $cursor->format('Y-m-01');
            $labels[] = $cursor->locale('id')->isoFormat('MMM YY');
            $reportedSeries[] = $reportedCounts[$key] ?? 0;
            $resolvedSeries[] = $resolvedCounts[$key] ?? 0;
            $cursor->addMonth();
        }

        return [$labels, $keys, $reportedSeries, $resolvedSeries];
    }
}
