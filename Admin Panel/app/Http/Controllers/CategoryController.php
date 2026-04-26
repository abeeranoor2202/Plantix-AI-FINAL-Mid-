<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth'); // Removed to avoid guard conflicts
    }

    public function index()
    {
        $categories = Category::with('createdByVendor')
            ->withCount('products')
            ->orderByRaw('vendor_id IS NULL DESC')
            ->orderBy('name')
            ->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $attributes = Attribute::with('values')
            ->orderBy('name')
            ->orderBy('title')
            ->get();

        return view('admin.categories.create', compact('attributes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'active'               => 'nullable|boolean',
            'text_review_enabled'  => 'nullable|boolean',
            'image_review_enabled' => 'nullable|boolean',
            'category_attributes' => 'nullable|array',
            'category_attributes.*' => 'integer|exists:attributes,id',
            'required_attributes' => 'nullable|array',
            'required_attributes.*' => 'integer|exists:attributes,id',
        ]);
        $data['active'] = $request->boolean('active');
        $data['text_review_enabled'] = $request->boolean('text_review_enabled', true);
        $data['image_review_enabled'] = $request->boolean('image_review_enabled', false);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        } elseif ($request->filled('image_base64')) {
            $base64 = preg_replace('/^data:image\/[a-z]+;base64,/', '', $request->image_base64);
            $filename = 'categories/cat_' . time() . '.jpg';
            Storage::disk('public')->put($filename, base64_decode($base64));
            $data['image'] = $filename;
        }

        $category = Category::create($data);
        $this->syncCategoryAttributes($category, $request);

        return response()->json(['success' => true, 'redirect' => route('admin.categories')]);
    }

    public function edit($id)
    {
        $category = Category::with(['attributes', 'createdByVendor'])->findOrFail($id);
        $attributes = Attribute::with('values')
            ->orderBy('name')
            ->orderBy('title')
            ->get();

        return view('admin.categories.edit', compact('category', 'id', 'attributes'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        // Vendor-created categories can only be edited by that vendor, not admin
        if (! $category->isGlobal()) {
            return response()->json(['success' => false, 'message' => 'This category was created by a vendor and cannot be edited here.'], 403);
        }

        $data = $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'active'               => 'nullable|boolean',
            'text_review_enabled'  => 'nullable|boolean',
            'image_review_enabled' => 'nullable|boolean',
            'category_attributes' => 'nullable|array',
            'category_attributes.*' => 'integer|exists:attributes,id',
            'required_attributes' => 'nullable|array',
            'required_attributes.*' => 'integer|exists:attributes,id',
        ]);
        $data['active'] = $request->boolean('active');
        $data['text_review_enabled'] = $request->boolean('text_review_enabled', true);
        $data['image_review_enabled'] = $request->boolean('image_review_enabled', false);

        if ($request->hasFile('image')) {
            if ($category->image) Storage::disk('public')->delete($category->image);
            $data['image'] = $request->file('image')->store('categories', 'public');
        } elseif ($request->filled('image_base64')) {
            $base64 = preg_replace('/^data:image\/[a-z]+;base64,/', '', $request->image_base64);
            $filename = 'categories/cat_' . $id . '_' . time() . '.jpg';
            if ($category->image) Storage::disk('public')->delete($category->image);
            Storage::disk('public')->put($filename, base64_decode($base64));
            $data['image'] = $filename;
        }

        $category->update($data);
        $this->syncCategoryAttributes($category, $request);

        return response()->json(['success' => true, 'redirect' => route('admin.categories')]);
    }

    public function togglePublish(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $category->update(['active' => $request->boolean('active')]);
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Vendor-created categories can only be deleted by that vendor
        if (! $category->isGlobal()) {
            return response()->json(['success' => false, 'message' => 'This category was created by a vendor and cannot be deleted here.'], 403);
        }

        if ($category->image) Storage::disk('public')->delete($category->image);
        $category->delete();
        return response()->json(['success' => true]);
    }

    private function syncCategoryAttributes(Category $category, Request $request): void
    {
        $attributeIds = collect($request->input('category_attributes', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $requiredIds = collect($request->input('required_attributes', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $syncPayload = [];
        foreach ($attributeIds as $index => $attributeId) {
            $syncPayload[$attributeId] = [
                'is_required' => $requiredIds->contains($attributeId),
                'sort_order' => $index,
            ];
        }

        $category->attributes()->sync($syncPayload);
    }
}