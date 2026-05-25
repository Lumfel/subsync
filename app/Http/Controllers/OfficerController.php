<?php

namespace App\Http\Controllers;

use App\Models\Officer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OfficerController extends Controller
{
    /** GET /api/officers */
    public function index()
    {
        return response()->json(
            Officer::select('id', 'name', 'email', 'contact_number', 'role_description', 'status')->get()
        );
    }

    /** POST /api/officers */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:150',
            'email'            => 'required|email|unique:officers,email',
            'password'         => 'required|string|min:8',
            'contact_number'   => 'nullable|string|max:20',
            'role_description' => 'nullable|string|max:150',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['status']   = 'Active';

        $officer = Officer::create($data);

        return response()->json(['success' => true, 'officer' => $officer]);
    }

    /** PUT /api/officers/{id} */
    public function update(Request $request, int $id)
    {
        $officer = Officer::findOrFail($id);

        $data = $request->validate([
            'name'             => 'sometimes|required|string|max:150',
            'email'            => 'sometimes|required|email|unique:officers,email,' . $id,
            'contact_number'   => 'nullable|string|max:20',
            'role_description' => 'nullable|string|max:150',
            'status'           => 'sometimes|in:Active,Inactive',
            'password'         => 'sometimes|nullable|string|min:8',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $officer->update($data);

        return response()->json(['success' => true, 'officer' => $officer->fresh()]);
    }

    /** DELETE /api/officers/{id} */
    public function destroy(int $id)
    {
        Officer::findOrFail($id)->update(['status' => 'Inactive']);
        return response()->json(['success' => true]);
    }
}
