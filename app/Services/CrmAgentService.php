<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use OpenAI\Laravel\Facades\OpenAI;

class CrmAgentService
{
    /**
     * Main Agent function
     */
    public function runAgent($prompt)
    {
        // 1️⃣ Ask AI to decide if database action is needed
        $plan = OpenAI::chat()->create([
            'model' => 'gpt-4.1-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are a research assistant who provides concise and accurate answers."
                ],
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        $decision = $plan['choices'][0]['message']['content'];

        // 2️⃣ Check if LLM requested DB execution
        if (str_starts_with($decision, 'ACTION: SEARCH_FIRM')) {

            $query = trim(str_replace('ACTION: SEARCH_FIRM |', '', $decision));

            $dbResult = $this->searchFirm($query);

            // 3️⃣ Give result back to LLM for final formatted answer
            $final = OpenAI::chat()->create([
                'model' => 'gpt-4.1-mini',
                'messages' => [
                    ['role' => 'system', 'content' => "You now have DB details below"],
                    ['role' => 'assistant', 'content' => json_encode($dbResult)],
                    ['role' => 'user', 'content' => "Give a clear answer based on this data."]
                ]
            ]);

            return $final['choices'][0]['message']['content'];
        }

        // 4️⃣ No DB action → return direct answer
        return $decision;
    }

    /**
     * Database Tool - Search firm
     */
    private function searchFirm($name)
    {
        return DB::table('law_firm')
            ->where('firm_name', 'like', "%$name%")
            ->get()
            ->toArray();
    }
}
