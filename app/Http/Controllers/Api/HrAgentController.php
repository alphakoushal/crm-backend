<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\HRAgentService;
use Illuminate\Support\Facades\DB;
class HRAgentController extends Controller
{

    public function run(Request $request)
    {
        $prompt = $request->input('prompt');

        if (!$prompt) {
            return response()->json([
                'success' => false,
                'message' => 'Prompt is required'
            ], 400);
        }

        $result = (new HRAgentService())->runAgent($prompt);

        return response()->json([
            'success' => true,
            'result' => $result
        ]);
    }
    public function pendingLeave ()
    {
         $result =  DB::table('employee_leave')
        ->where('user_id', "A002")
        ->where('status', '2')
        ->get();
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
}
