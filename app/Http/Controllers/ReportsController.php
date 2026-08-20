<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    /* ── Main Reports Page ─────────────────────────────────── */
    public function index(Request $request)
    {
        $range = $request->input('range', '30');
        $from  = $request->input('from');
        $to    = $request->input('to');

        // Date range
        if ($from && $to) {
            $startDate = \Carbon\Carbon::parse($from)->startOfDay();
            $endDate   = \Carbon\Carbon::parse($to)->endOfDay();
        } else {
            $endDate   = now();
            $startDate = match($range) {
                '7'   => now()->subDays(7),
                '30'  => now()->subDays(30),
                '90'  => now()->subDays(90),
                '180' => now()->subDays(180),
                '365' => now()->subDays(365),
                default => now()->subDays(30),
            };
        }

        // ── KPI Cards ────────────────────────────────────────
        $totalProperties = DB::table('propertys')->whereBetween('created_at', [$startDate, $endDate])->count();
        $totalCustomers  = DB::table('customers')->whereNull('owner_type')->whereBetween('created_at', [$startDate, $endDate])->count();
        $totalOwners     = DB::table('customers')->whereNotNull('owner_type')->whereBetween('created_at', [$startDate, $endDate])->count();
        $totalEnquiries  = DB::table('interested_users')->whereBetween('created_at', [$startDate, $endDate])->count();
        $totalViews      = DB::table('propertys')->whereBetween('created_at', [$startDate, $endDate])->sum('total_click');

        // Revenue from subscriptions
        $totalRevenue = DB::table('user_packages as up')
            ->join('packages as pk', 'pk.id', '=', 'up.package_id')
            ->whereBetween('up.created_at', [$startDate, $endDate])
            ->whereNull('up.deleted_at')
            ->where('pk.package_type', 'paid')
            ->sum('pk.price');

        // Previous period for % change
        $prevStart = (clone $startDate)->subDays($startDate->diffInDays($endDate));
        $prevProps = DB::table('propertys')->whereBetween('created_at', [$prevStart, $startDate])->count();
        $prevEnq   = DB::table('interested_users')->whereBetween('created_at', [$prevStart, $startDate])->count();

        // ── Property Trends (monthly last 12 months) ──────────
        $propertyTrends = $this->getMonthlyTrend('propertys', 12);

        // ── Properties by City (top 10) ───────────────────────
        $propsByCity = DB::table('propertys')
            ->select('city', DB::raw('COUNT(*) as total'))
            ->whereNotNull('city')->where('city', '!=', '')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('city')->orderByDesc('total')->limit(10)->get();

        // ── Properties by Category ────────────────────────────
        $propsByCategory = DB::table('propertys as p')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->select('c.category as name', DB::raw('COUNT(p.id) as total'))
            ->whereBetween('p.created_at', [$startDate, $endDate])
            ->groupBy('c.category')->orderByDesc('total')->get();

        // ── Sale vs Rent ──────────────────────────────────────
        $saleVsRent = [
            'sale' => DB::table('propertys')->where('propery_type', 0)->whereBetween('created_at', [$startDate, $endDate])->count(),
            'rent' => DB::table('propertys')->where('propery_type', 1)->whereBetween('created_at', [$startDate, $endDate])->count(),
        ];

        // ── Customer Growth (last 12 months) ─────────────────
        $customerGrowth = $this->getCustomerGrowth(12);

        // ── Enquiry Trends (last 12 months) ──────────────────
        $enquiryTrends = $this->getMonthlyTrend('interested_users', 12);

        // ── Most Viewed Properties ────────────────────────────
        $mostViewed = DB::table('propertys as p')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('customers as cu', 'cu.id', '=', 'p.added_by')
            ->where('p.total_click', '>', 0)
            ->select('p.id', 'p.title', 'p.city', 'p.price', 'p.total_click',
                     'p.propery_type', 'p.request_status', 'c.category as category_name',
                     'cu.name as owner_name')
            ->orderByDesc('p.total_click')->limit(10)->get();

        // ── Most Enquired Properties ──────────────────────────
        $mostEnquired = DB::table('interested_users as iu')
            ->join('propertys as p', 'p.id', '=', 'iu.property_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->select('p.id', 'p.title', 'p.city', 'p.price', 'p.propery_type',
                     'c.category as category_name', DB::raw('COUNT(iu.id) as enquiry_count'))
            ->whereBetween('iu.created_at', [$startDate, $endDate])
            ->groupBy('p.id', 'p.title', 'p.city', 'p.price', 'p.propery_type', 'c.category')
            ->orderByDesc('enquiry_count')->limit(10)->get();

        // ── Subscription Revenue (monthly) ────────────────────
        $revenueMonthly = DB::table('user_packages as up')
            ->join('packages as pk', 'pk.id', '=', 'up.package_id')
            ->select(
                DB::raw('YEAR(up.created_at) as year'),
                DB::raw('MONTH(up.created_at) as month'),
                DB::raw('SUM(pk.price) as revenue'),
                DB::raw('COUNT(up.id) as subscriptions')
            )
            ->whereNull('up.deleted_at')
            ->where('pk.package_type', 'paid')
            ->where('up.created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')->orderBy('month')->get();

        // ── Owner Type Breakdown ──────────────────────────────
        $ownerBreakdown = [
            'sellers'  => DB::table('customers')->where('owner_type', 'seller')->count(),
            'builders' => DB::table('customers')->where('owner_type', 'builder')->count(),
        ];

        // ── Property Status Breakdown ─────────────────────────
        $statusBreakdown = DB::table('propertys')
            ->select('request_status', DB::raw('COUNT(*) as total'))
            ->where('post_type', 1)
            ->groupBy('request_status')->get();

        // ── Top Performing Cities (enquiries) ─────────────────
        $topCitiesEnquiries = DB::table('interested_users as iu')
            ->join('propertys as p', 'p.id', '=', 'iu.property_id')
            ->select('p.city', DB::raw('COUNT(iu.id) as enquiries'))
            ->whereNotNull('p.city')->where('p.city', '!=', '')
            ->whereBetween('iu.created_at', [$startDate, $endDate])
            ->groupBy('p.city')->orderByDesc('enquiries')->limit(8)->get();

        return view('reports.index', compact(
            'range', 'startDate', 'endDate',
            'totalProperties', 'totalCustomers', 'totalOwners', 'totalEnquiries',
            'totalViews', 'totalRevenue', 'prevProps', 'prevEnq',
            'propertyTrends', 'propsByCity', 'propsByCategory', 'saleVsRent',
            'customerGrowth', 'enquiryTrends', 'mostViewed', 'mostEnquired',
            'revenueMonthly', 'ownerBreakdown', 'statusBreakdown', 'topCitiesEnquiries'
        ));
    }

    /* ── Export CSV ────────────────────────────────────────── */
    public function export(Request $request)
    {
        $type = $request->input('type', 'properties');

        $data = match($type) {
            'properties' => DB::table('propertys as p')
                ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
                ->leftJoin('customers as cu', 'cu.id', '=', 'p.added_by')
                ->select('p.id', 'p.title', 'p.city', 'p.state', 'p.price',
                         'p.propery_type', 'p.request_status', 'p.total_click',
                         'c.category as category', 'cu.name as owner', 'p.created_at')
                ->orderByDesc('p.created_at')->get(),

            'customers' => DB::table('customers')
                ->select('id', 'name', 'email', 'mobile', 'city', 'state',
                         'owner_type', 'isActive', 'created_at')
                ->orderByDesc('created_at')->get(),

            'enquiries' => DB::table('interested_users as iu')
                ->join('customers as c', 'c.id', '=', 'iu.customer_id')
                ->join('propertys as p', 'p.id', '=', 'iu.property_id')
                ->select('iu.id', 'c.name as buyer', 'c.email', 'c.mobile',
                         'p.title as property', 'p.city', 'iu.created_at')
                ->orderByDesc('iu.created_at')->get(),

            default => collect()
        };

        $filename = $type . '_report_' . date('d-m-Y') . '.csv';
        $headers  = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=$filename"];

        $callback = function () use ($data, $type) {
            $handle = fopen('php://output', 'w');
            if ($data->isNotEmpty()) {
                fputcsv($handle, array_keys((array) $data->first()));
                foreach ($data as $row) {
                    fputcsv($handle, (array) $row);
                }
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /* ── JSON: Chart Data API ──────────────────────────────── */
    public function chartData(Request $request)
    {
        $type  = $request->input('type');
        $range = (int) $request->input('range', 12);

        return match($type) {
            'property_trends' => response()->json($this->getMonthlyTrend('propertys', $range)),
            'enquiry_trends'  => response()->json($this->getMonthlyTrend('interested_users', $range)),
            'customer_growth' => response()->json($this->getCustomerGrowth($range)),
            default           => response()->json([])
        };
    }

    /* ── Helpers ───────────────────────────────────────────── */
    private function getMonthlyTrend(string $table, int $months): array
    {
        $labels = [];
        $data   = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');
            $data[]   = DB::table($table)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function getCustomerGrowth(int $months): array
    {
        $labels   = [];
        $buyers   = [];
        $owners   = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date     = now()->subMonths($i);
            $labels[] = $date->format('M Y');
            $buyers[] = DB::table('customers')->whereNull('owner_type')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)->count();
            $owners[] = DB::table('customers')->whereNotNull('owner_type')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)->count();
        }

        return ['labels' => $labels, 'buyers' => $buyers, 'owners' => $owners];
    }
}
