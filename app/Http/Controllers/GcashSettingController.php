<?php

namespace App\Http\Controllers;

use App\Models\GcashSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GcashSettingController extends Controller
{
    /** GET /api/gcash-settings — current GCash receiving account + QR (all roles). */
    public function show()
    {
        return response()->json($this->payload(GcashSetting::current()));
    }

    /** POST /api/gcash-settings — admin changes the GCash receiving account / QR code. */
    public function update(Request $request)
    {
        $data = $request->validate([
            'account_name' => 'nullable|string|max:120',
            'account_number' => 'nullable|string|max:40',
            'instructions' => 'nullable|string|max:1000',
            'qr_image' => 'nullable|image|max:8192',
            'remove_qr' => 'nullable|boolean',
        ]);

        $setting = GcashSetting::current();

        if ($request->hasFile('qr_image')) {
            if ($setting->qr_image_path) {
                Storage::disk('public')->delete($setting->qr_image_path);
            }
            $setting->qr_image_path = $request->file('qr_image')->store('gcash', 'public');
        } elseif ($request->boolean('remove_qr') && $setting->qr_image_path) {
            Storage::disk('public')->delete($setting->qr_image_path);
            $setting->qr_image_path = null;
        }

        // Only overwrite fields that were actually submitted, so a QR-only or
        // partial update never blanks the account details.
        if ($request->hasAny(['account_name'])) {
            $setting->account_name = $data['account_name'] ?? null;
        }
        if ($request->hasAny(['account_number'])) {
            $setting->account_number = $data['account_number'] ?? null;
        }
        if ($request->hasAny(['instructions'])) {
            $setting->instructions = $data['instructions'] ?? null;
        }
        $setting->updated_by = session('admin_id');
        $setting->save();

        return response()->json([
            'success' => true,
            'settings' => $this->payload($setting->fresh()),
        ]);
    }

    private function payload(GcashSetting $s): array
    {
        return [
            'account_name' => $s->account_name,
            'account_number' => $s->account_number,
            'instructions' => $s->instructions,
            'qr_image_path' => $s->qr_image_path,
            'qr_image_url' => $s->qr_image_path ? asset('storage/'.$s->qr_image_path) : null,
            'updated_at' => $s->updated_at?->format('M d, Y g:i A'),
        ];
    }
}
