<?php

namespace App\Http\Controllers\Tenant\Support;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    public function knowledgeBaseIndex(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'This is the knowledge base index endpoint.',
                'status' => 'success',
            ]);
        }

        return view('tenant.support.knowledgebase');
    }
}
