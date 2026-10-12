<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\Officer;
use App\Models\Resident;
use App\Models\UserSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Resolves the currently authenticated portal actor (admin | officer | resident).
 * Mirrors the resolution used by App\Http\Middleware\PortalRole.
 */
class Actor
{
    /** @return array{type:?string,id:?int,user:?Model,name:?string} */
    public static function current(?Request $request = null): array
    {
        $request = $request ?: request();

        if ($request->session()->has('admin_id')) {
            $admin = Admin::find($request->session()->get('admin_id'));
            if ($admin) {
                return ['type' => 'admin', 'id' => $admin->id, 'user' => $admin, 'name' => $admin->name];
            }
        }

        if (Auth::guard('officer')->check()) {
            $officer = Auth::guard('officer')->user();

            return ['type' => 'officer', 'id' => $officer->id, 'user' => $officer, 'name' => $officer->name];
        }

        if (Auth::guard('resident')->check()) {
            $resident = Auth::guard('resident')->user();

            return ['type' => 'resident', 'id' => $resident->id, 'user' => $resident, 'name' => $resident->name];
        }

        return ['type' => null, 'id' => null, 'user' => null, 'name' => null];
    }

    public static function settings(?Request $request = null): UserSetting
    {
        $actor = self::current($request);

        return UserSetting::resolve($actor['type'], $actor['id']);
    }

    /** Preferences array for the current actor (used to seed the layouts). */
    public static function prefs(?Request $request = null): array
    {
        $actor = self::current($request);

        if (! $actor['type']) {
            return (new UserSetting)->toPrefs();
        }

        return self::settings($request)->toPrefs();
    }
}
