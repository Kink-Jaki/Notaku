<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query()->withCount('products');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $categories = $query->latest()->paginate(15)->withQueryString();

        return view('admin.kategori', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')],
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        $category = Category::create($request->only(['name', 'slug']));

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_category',
            'model_type' => Category::class,
            'model_id' => $category->id,
            'description' => "Membuat kategori {$category->name}",
            'old_values' => null,
            'new_values' => $category->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $request->expectsJson()
            ? response()->json(['message' => 'Data berhasil ditambahkan'])
            : redirect()->route('admin.kategori.index')->with('swal_success', 'Data berhasil ditambahkan');
    }

    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category->id)],
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        $oldValues = $category->toArray();

        $category->fill($request->only(['name', 'slug']))->save();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_category',
            'model_type' => Category::class,
            'model_id' => $category->id,
            'description' => "Memperbarui kategori {$category->name}",
            'old_values' => $oldValues,
            'new_values' => $category->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $request->expectsJson()
            ? response()->json(['message' => 'Data berhasil diperbarui'])
            : redirect()->route('admin.kategori.index')->with('swal_success', 'Data berhasil diperbarui');
    }

    public function destroy(Category $category)
    {
        $name = $category->name;

        if ($category->products()->count() > 0) {
            return redirect()->route('admin.kategori.index')->with('swal_error', 'Kategori tidak bisa dihapus karena masih memiliki produk.');
        }

        $category->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_category',
            'model_type' => Category::class,
            'model_id' => $category->id,
            'description' => "Menghapus kategori {$name}",
            'old_values' => ['name' => $name],
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.kategori.index')->with('swal_success', 'Data berhasil dihapus');
    }
}
