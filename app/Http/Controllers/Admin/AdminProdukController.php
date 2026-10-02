<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminProdukController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()->with('category')->latest()->paginate(15)->withQueryString();
        $data = ['products' => $products];

        if (! $request->ajax()) {
            $data['categories'] = Category::query()->withCount('products')->orderBy('name')->get();
        }

        return $this->viewOrFragment($request, 'admin.produk', 'admin.produk-results', $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['name', 'description', 'price', 'stock', 'category_id', 'is_active']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        $product = Product::create($data);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_product',
            'model_type' => Product::class,
            'model_id' => $product->id,
            'description' => "Menambahkan produk {$product->name}",
            'old_values' => null,
            'new_values' => $product->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $request->expectsJson()
            ? response()->json(['message' => 'Data berhasil ditambahkan'])
            : redirect()->route('admin.produk.index')->with('swal_success', 'Data berhasil ditambahkan');
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'remove_image' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        $oldValues = $product->toArray();

        $data = $request->only(['name', 'description', 'price', 'stock', 'category_id', 'is_active']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        } elseif ($request->boolean('remove_image')) {
            // Hapus gambar tanpa upload baru
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = null;
        }

        $product->fill($data)->save();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_product',
            'model_type' => Product::class,
            'model_id' => $product->id,
            'description' => "Memperbarui produk {$product->name}",
            'old_values' => $oldValues,
            'new_values' => $product->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $request->expectsJson()
            ? response()->json(['message' => 'Data berhasil diperbarui'])
            : redirect()->route('admin.produk.index')->with('swal_success', 'Data berhasil diperbarui');
    }

    public function destroy(Product $product)
    {
        $name = $product->name;

        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_product',
            'model_type' => Product::class,
            'model_id' => $product->id,
            'description' => "Mengapus produk {$name}",
            'old_values' => ['name' => $name],
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.produk.index')->with('swal_success', 'Data berhasil dihapus');
    }
}
