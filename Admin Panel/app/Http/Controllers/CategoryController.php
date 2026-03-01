<?php

namespace App\Http\Controllers;

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
        $categories = Category::withCount('products')->orderBy('name')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'active'      => 'nullable|boolean',
        ]);
        $data['active'] = $request->boolean('active');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        } elseif ($request->filled('image_base64')) {
            $base64 = preg_replace('/^data:image\/[a-z]+;base64,/', '', $request->image_base64);
            $filename = 'categories/cat_' . time() . '.jpg';
            Storage::disk('public')->put($filename, base64_decode($base64));
            $data['image'] = $filename;
        }

        Category::create($data);
        return response()->json(['success' => true, 'redirect' => route('admin.categories')]);
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category', 'id'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'active'      => 'nullable|boolean',
        ]);
        $data['active'] = $request->boolean('active');

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
        if ($category->image) Storage::disk('public')->delete($category->image);
        $category->delete();
        return response()->json(['success' => true]);
    }
}