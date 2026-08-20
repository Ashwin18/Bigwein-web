<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\DataModeService;

class AdminNotificationsController extends Controller
{
    public function index()
    {
        $items = [];
        try {
            // Pending approvals
            $pendingQuery = DB::table('propertys')
                ->where('added_by','!=',0)->where('request_status','pending');
            DataModeService::applyPropertyScope($pendingQuery, 'propertys', true);
            $pending = $pendingQuery->orderByDesc('created_at')->take(5)
                ->get(['id','title','city','created_at']);
            foreach ($pending as $p) {
                $items[] = [
                    'type'  => 'pending',
                    'title' => 'Pending: '.\Illuminate\Support\Str::limit($p->title, 35),
                    'body'  => ($p->city ?? 'City unknown').' · '.Carbon::parse($p->created_at)->diffForHumans(),
                    'url'   => url('property-approval/'.$p->id.'/detail'),
                    'time'  => Carbon::parse($p->created_at)->diffForHumans(),
                    'read'  => false,
                ];
            }
            // Recent enquiries
            $enquiryQuery = DB::table('interested_users as iu')
                ->join('propertys as p','p.id','=','iu.property_id')
                ->where('iu.created_at','>=',now()->subDay());
            DataModeService::applyPropertyScope($enquiryQuery, 'p', false);
            $enquiries = $enquiryQuery->orderByDesc('iu.created_at')->take(5)
                ->get(['iu.created_at','p.title','p.id']);
            foreach ($enquiries as $e) {
                $items[] = [
                    'type'  => 'enquiry',
                    'title' => 'New enquiry: '.\Illuminate\Support\Str::limit($e->title, 35),
                    'body'  => Carbon::parse($e->created_at)->diffForHumans(),
                    'url'   => url('property-inquiry'),
                    'time'  => Carbon::parse($e->created_at)->diffForHumans(),
                    'read'  => false,
                ];
            }
            // New owners today
            $ownerIds = DataModeService::ownerIdsForCurrentMode();
            if (DataModeService::isDemo()) {
                $ownerQ = DB::table('customers')->where('isActive',1)->where('created_at','>=',now()->startOfDay());
                empty($ownerIds) ? $ownerQ->whereRaw('1=0') : $ownerQ->whereIn('id',$ownerIds);
                $owners = $ownerQ->count();
            } else {
                $owners = DB::table('customers')->where('isActive',1)->whereIn('owner_type',['seller','builder'])->where('created_at','>=',now()->startOfDay())->count();
            }
            if ($owners > 0) {
                $items[] = [
                    'type'  => 'owner',
                    'title' => "{$owners} new owner".($owners>1?'s':'')." registered today",
                    'body'  => 'View in customer management',
                    'url'   => url('customer'),
                    'time'  => 'Today',
                    'read'  => false,
                ];
            }
        } catch (\Exception $e) {}

        usort($items, fn($a,$b) => $a['read'] - $b['read']);
        $pendingCountQ = DB::table('propertys')->where('added_by','!=',0)->where('request_status','pending');
        DataModeService::applyPropertyScope($pendingCountQ, 'propertys', true);
        $pendingCount = $pendingCountQ->count();
        return response()->json(['items' => array_slice($items, 0, 10), 'pending_count' => $pendingCount]);
    }

    public function markRead()
    {
        return response()->json(['success'=>true]);
    }

    public function chartData(Request $request)
    {
        $year = $request->year ?? date('Y');
        try {
            $monthly = DB::table('propertys')
                ->whereYear('created_at', $year)
                ->where('status',1)->where('request_status','approved');
            DataModeService::applyPropertyScope($monthly, 'propertys', true);
            $monthly = $monthly->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->groupBy('month')->pluck('count','month');

            $monthlyArr = [];
            for ($m=1; $m<=12; $m++) $monthlyArr[] = $monthly[$m] ?? 0;

            $typeSplit = DB::table('propertys')
                ->where('status',1)->where('request_status','approved');
            DataModeService::applyPropertyScope($typeSplit, 'propertys', true);
            $typeSplit = $typeSplit->selectRaw('propery_type, COUNT(*) as count')
                ->groupBy('propery_type')->get();

            $typeLabels = [];
            $typeData   = [];
            foreach ($typeSplit as $t) {
                $typeLabels[] = $t->propery_type == 1 ? 'For Rent' : 'For Sale';
                $typeData[]   = $t->count;
            }

            return response()->json([
                'monthly'    => $monthlyArr,
                'typeLabels' => $typeLabels,
                'typeData'   => $typeData,
            ]);
        } catch (\Exception $e) {
            return response()->json(['monthly'=>array_fill(0,12,0),'typeLabels'=>[],'typeData'=>[]]);
        }
    }
}
