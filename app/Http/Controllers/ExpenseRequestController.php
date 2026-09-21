<?php

namespace App\Http\Controllers;

use App\Models\ExpenseRequest;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseRequestController extends Controller
{
    private function residentId(Request $request): ?int
    {
        return Auth::guard('resident')->id() ?? $request->session()->get('resident_id');
    }

    /** GET /api/expense-requests/my — resident's own expense requests */
    public function myRequests(Request $request)
    {
        $residentId = $this->residentId($request);

        if (!$residentId) {
            return response()->json(['success' => false, 'message' => 'Resident session expired. Please log in again.'], 401);
        }

        $requests = ExpenseRequest::where('resident_id', $residentId)
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'description', 'amount', 'expense_date', 'status', 'created_at']);

        return response()->json($requests);
    }

    /** POST /api/expense-requests — resident submits expense request */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:200',
            'description'   => 'required|string',
            'amount'        => 'required|numeric|min:0.01',
            'expense_date'  => 'required|date',
            'documents.*'   => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $residentId = $this->residentId($request);

        if (!$residentId) {
            return response()->json(['success' => false, 'message' => 'Resident session expired. Please log in again.'], 401);
        }

        $data['resident_id'] = $residentId;
        $data['status']      = 'Pending';

        $request = ExpenseRequest::create($data);

        // Handle uploaded documents
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('expense_documents', 'public');
                // We could create a Media record here if needed, but for simplicity we'll just store the path
                // For now, we'll just keep track that documents were uploaded
            }
        }

        return response()->json(['success' => true, 'request' => $request]);
    }

    /** DELETE /api/expense-requests/{id} — resident cancels their own request */
    public function destroy(Request $request, int $id)
    {
        $residentId = $this->residentId($request);

        if (!$residentId) {
            return response()->json(['success' => false, 'message' => 'Resident session expired. Please log in again.'], 401);
        }

        $request = ExpenseRequest::where('id', $id)
            ->where('resident_id', $residentId)
            ->where('status', 'Pending')
            ->firstOrFail();

        $request->delete();

        return response()->json(['success' => true]);
    }
}