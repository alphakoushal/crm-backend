<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Session;
use App\Models\AiMemory;
class HRAgentService
{
    /**
     * Main Agent that understands commands & triggers DB actions
     */
    public function runAgent(string $prompt)
    {
        // 1️⃣ First call → Ask AI what action to perform
        $plan = OpenAI::chat()->create([
            'model' => 'gpt-4.1-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' =>
                        "
You are an HR assistant with access to tools.  
You must ALWAYS respond using one of these actions when needed:

- ACTION: SEARCH_LEAVE | EMP_ID
- ACTION: FILTER_LEAVE | DATE_OR_ID
- ACTION: APPROVE_LEAVE | LEAVE_ID

You can also give a direct final answer when no DB action is needed.

Rules:
1. If user asks for pending leave → return ACTION: SEARCH_LEAVE
2. If user references earlier results → RETURN ACTION: FILTER_LEAVE
3. If user says approve a leave → RETURN ACTION: APPROVE_LEAVE
Otherwise, give direct answers."
                ],
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);
        $decision = trim($plan['choices'][0]['message']['content']);
return $decision;
if (str_contains($decision, 'SEARCH_LEAVE')) {

    list($tool, $employee) = explode('|', $decision);
    $employee = trim($employee);

    $dbResult = $this->getPendingLeave($employee);
    //  saveMemory('1',$dbResult);

    // Return result back to AI for formatted final answer
    $final = OpenAI::chat()->create([
        'model' => 'gpt-4.1-mini',
        'messages' => [
            ['role' => 'system', 'content' => "Format this HR data clearly"],
            ['role' => 'assistant', 'content' => json_encode($dbResult)],
            ['role' => 'user', 'content' => "Give a clean HR-style response."]
        ]
    ]);

    return $final['choices'][0]['message']['content'];
}
if (str_contains($decision, 'ACTION: FILTER_LEAVE')) {

            $filter = trim(str_replace('ACTION: FILTER_LEAVE |', '', $decision));

            $previous = Session::get('last_leave_results', []);

            $filtered = collect($previous)->filter(function ($item) use ($filter) {
                return str_contains($item->date, $filter) || 
                       str_contains((string)$item->id, $filter);
            })->values()->toArray();

            return $this->returnFinalAnswer("Filtered results based on: {$filter}", $filtered);
        }
        // 3️⃣ No DB action → return message directly
        return $decision;
    }

    private function getPendingLeave($employee)
{
    return DB::table('employee_leave')
        ->where('user_id', 'like', "%$employee%")
        ->where('status', '2')
        ->get();
}
 private function returnFinalAnswer($title, $data)
    {
        $final = OpenAI::chat()->create([
            'model' => 'gpt-4.1-mini',
            'messages' => [
                ['role' => 'system', 'content' => "Format the response nicely for the manager."],
                ['role' => 'assistant', 'content' => json_encode($data)],
                ['role' => 'user', 'content' => $title]
            ]
        ]);

        return $final['choices'][0]['message']['content'];
    }
    public function saveMemory($sessionId, $data)
{
    AiMemory::updateOrCreate(
        ['session_id' => $sessionId],
        ['data' => $data]
    );
}


}
