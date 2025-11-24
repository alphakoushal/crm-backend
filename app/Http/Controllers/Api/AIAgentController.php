<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CrmAgentService;

class AIAgentController extends Controller
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

        $result = (new CrmAgentService())->runAgent($prompt);

        return response()->json([
            'success' => true,
            'result' => $result
        ]);
    }
}
