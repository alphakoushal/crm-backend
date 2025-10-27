<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenAI\Laravel\Facades\OpenAI;

class SmartInvoiceController extends Controller
{
    public function ask(Request $request)
    {
        $question = $request->input('question');

        if (!$question) {
            return response()->json(['error' => 'Please provide a question.'], 400);
        }

        // 🧩 Step 1: Define your database schema
        $schema = "
        Database has the following tables:
        1. clients
           - id
           - name
           - email
           - phone
           - created_at
        2. invoices
           - id
           - client_id
           - amount
           - status
           - created_at
        Relationship:
        - invoices.client_id references clients.id
        ";

        // 🧩 Step 2: Ask GPT to generate SQL
        $prompt = "
        You are a SQL expert. Convert the following natural language question 
        into a safe SQL query using the given schema. 
        Only use SELECT statements. Do not modify or delete any data.

        Schema:
        $schema

        Question: $question
        ";

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'You are an expert SQL generator for Laravel MySQL database.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $sql = trim($response['choices'][0]['message']['content']);

        // 🧩 Step 3: Basic safety check
        if (!preg_match('/^SELECT/i', $sql)) {
            return response()->json(['error' => 'Unsafe or invalid query generated.', 'query' => $sql], 400);
        }

        try {
            // 🧩 Step 4: Execute the SQL query
            $results = DB::select($sql);

            // 🧩 Step 5: Optionally, ask GPT to summarize results
            $summary = null;
            if (!empty($results)) {
                $summaryPrompt = "
                Here are the query results: " . json_encode($results) . ".
                Summarize them briefly in plain language.
                ";
                $summaryResponse = OpenAI::chat()->create([
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You summarize database query results in simple English.'],
                        ['role' => 'user', 'content' => $summaryPrompt],
                    ],
                ]);

                $summary = trim($summaryResponse['choices'][0]['message']['content']);
            }

            return response()->json([
                'question' => $question,
                'generated_query' => $sql,
                'data' => $results,
                'summary' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Database query failed',
                'message' => $e->getMessage(),
                'query' => $sql
            ], 500);
        }
    }
}
