<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminPromoController extends Controller
{
    public function index(Request $request)
    {
        $query = PromoCode::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $promos = $query->latest()->paginate(15)->withQueryString();

        return view('admin.promo', compact('promos'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:50', 'unique:promo_codes'],
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'value' => ['required', 'integer', 'min:1'],
            'min_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        $promo = PromoCode::create([
            'code' => strtoupper($request->code),
            'type' => $request->type,
            'value' => $request->value,
            'min_order' => $request->min_order,
            'is_active' => $request->boolean('is_active'),
            'starts_at' => $request->starts_at ? $request->starts_at : null,
            'ends_at' => $request->ends_at ? $request->ends_at : null,
            'usage_limit' => $request->usage_limit,
            'used_count' => 0,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_promo',
            'model_type' => PromoCode::class,
            'model_id' => $promo->id,
            'description' => "Membuat promo {$promo->code} ({$promo->type} {$promo->value})",
            'old_values' => null,
            'new_values' => $promo->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $request->expectsJson()
            ? response()->json(['message' => 'Data berhasil ditambahkan'])
            : redirect()->route('admin.promo.index')->with('swal_success', 'Data berhasil ditambahkan');
    }

    public function update(Request $request, PromoCode $promo)
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:50', 'unique:promo_codes,code,'.$promo->id],
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'value' => ['required', 'integer', 'min:1'],
            'min_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        $oldValues = $promo->toArray();

        $promo->fill([
            'code' => strtoupper($request->code),
            'type' => $request->type,
            'value' => $request->value,
            'min_order' => $request->min_order,
            'is_active' => $request->boolean('is_active'),
            'starts_at' => $request->starts_at ? $request->starts_at : null,
            'ends_at' => $request->ends_at ? $request->ends_at : null,
            'usage_limit' => $request->usage_limit,
        ])->save();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_promo',
            'model_type' => PromoCode::class,
            'model_id' => $promo->id,
            'description' => "Memperbarui promo {$promo->code}",
            'old_values' => $oldValues,
            'new_values' => $promo->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $request->expectsJson()
            ? response()->json(['message' => 'Data berhasil diperbarui'])
            : redirect()->route('admin.promo.index')->with('swal_success', 'Data berhasil diperbarui');
    }

    public function destroy(PromoCode $promo)
    {
        $code = $promo->code;
        $promo->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_promo',
            'model_type' => PromoCode::class,
            'model_id' => $promo->id,
            'description' => "Menghapus promo {$code}",
            'old_values' => ['code' => $code],
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.promo.index')->with('swal_success', 'Data berhasil dihapus');
    }
}
