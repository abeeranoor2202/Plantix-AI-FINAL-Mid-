<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class AdminCategoriesController extends Controller
{
    /**
     * Get all categories with pagination
     */
    public function index(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            $search = $request->get('search', '');

            $query = Category::query();

            if ($search) {
                $query->where('name', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
            }

            $total = $query->count();
            $categories = $query->orderBy('created_at', 'desc')
                               ->skip(($page - 1) * $limit)
                               ->take($limit)
                               ->get()
                               ->map(function ($category) {
                                   return [
                                       'id' => $category->id,
                                       'name' => $category->name,
                                       'description' => $category->description,
                                       'image' => $category->image ? asset('storage/' . $category->image) : null,
                                       'is_active' => $category->is_active ?? true,
                                       'created_at' => $category->created_at,
                                   ];
                               });

            return response()->json([
                'success' => true,
                'data' => $categories,
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'pages' => ceil($total / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single category
     */
    public function show($id)
    {
        try {
            $category = Category::find($id);
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'image' => $category->image ? asset('storage/' . $category->image) : null,
                    'is_active' => $category->is_active ?? true,
                    'text_review_enabled' => $category->text_review_enabled ?? true,
                    'image_review_enabled' => $category->image_review_enabled ?? false,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create category
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|max:2048',
                'is_active' => 'nullable|boolean',
                'text_review_enabled' => 'nullable|boolean',
                'image_review_enabled' => 'nullable|boolean',
            ]);

            $category = new Category();
            $category->name = $validated['name'];
            $category->description = $validated['description'] ?? '';
            $category->is_active = $validated['is_active'] ?? true;
            $category->text_review_enabled = $validated['text_review_enabled'] ?? true;
            $category->image_review_enabled = $validated['image_review_enabled'] ?? false;

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('categories', 'public');
                $category->image = $path;
            }

            $category->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'image' => $category->image ? asset('storage/' . $category->image) : null,
                    'is_active' => $category->is_active,
                    'text_review_enabled' => $category->text_review_enabled,
                    'image_review_enabled' => $category->image_review_enabled,
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update category
     */
    public function update(Request $request, $id)
    {
        try {
            $category = Category::find($id);
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|max:2048',
                'is_active' => 'nullable|boolean',
                'text_review_enabled' => 'nullable|boolean',
                'image_review_enabled' => 'nullable|boolean',
            ]);

            if (isset($validated['name'])) {
                $category->name = $validated['name'];
            }
            if (isset($validated['description'])) {
                $category->description = $validated['description'];
            }
            if (isset($validated['is_active'])) {
                $category->is_active = $validated['is_active'];
            }
            if (array_key_exists('text_review_enabled', $validated)) {
                $category->text_review_enabled = $validated['text_review_enabled'];
            }
            if (array_key_exists('image_review_enabled', $validated)) {
                $category->image_review_enabled = $validated['image_review_enabled'];
            }

            if ($request->hasFile('image')) {
                if ($category->image) {
                    \Storage::disk('public')->delete($category->image);
                }
                $path = $request->file('image')->store('categories', 'public');
                $category->image = $path;
            }

            $category->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'image' => $category->image ? asset('storage/' . $category->image) : null,
                    'is_active' => $category->is_active,
                    'text_review_enabled' => $category->text_review_enabled,
                    'image_review_enabled' => $category->image_review_enabled,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete category
     */
    public function destroy($id)
    {
        try {
            $category = Category::find($id);
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            if ($category->image) {
                \Storage::disk('public')->delete($category->image);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting category: ' . $e->getMessage()
            ], 500);
        }
    }
}
