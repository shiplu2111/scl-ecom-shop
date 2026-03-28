<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

/**
 * @group Admin
 * @subgroup Activity Logs
 */
class ActivityLogController extends Controller
{
    /**
     * Display a listing of the activity logs.
     */
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])->latest();

        if ($request->has('causer_type')) {
            $query->where('causer_type', $request->causer_type);
        }

        if ($request->has('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->has('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('properties->ip', 'like', "%{$search}%")
                  ->orWhere('properties->device', 'like', "%{$search}%")
                  ->orWhere('properties->platform', 'like', "%{$search}%")
                  ->orWhere('subject_type', 'like', "%{$search}%")
                  ->orWhereHasMorph('causer', ['App\Models\Admin', 'App\Models\User'], function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->paginate($request->get('per_page', 20));

        return response()->json($logs);
    }
}
