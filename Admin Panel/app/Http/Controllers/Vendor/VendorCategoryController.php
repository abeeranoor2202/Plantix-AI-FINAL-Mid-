<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * VendorCategoryController
 *
 * Full CRUD for vendor-created product categories.
 *
 * Visibility rules:
 *   - ALL categories (admin + every vendor) are visible to everyone.
 *   - A vendor can only edit / delete categories they created (vendor_id = their id).
 *   - Admin-created categories (vendor_id = null) are read-only for vendors.
 */
class VendorCategoryController extends Controller
{
    private function vendorId(): int
    {
        return auth('vendor')->user()->vendor->id;
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Category::withCount('products')
            ->with('createdByVendor')
            ->orderByRaw('vendor_id IS NULL ASC')   // global first
            ->orderBy('name');

        if ($request->filled('search')) {
            $term = '%' . trim((string) $request->input('search')) . '%';
            $query->where('name', 'like', $term);
        }

        if ($request->filled('scope')) {
            $scope = (string) $request->input('scope');
            if ($scope === 'mine') {
                $query->byVendor($this->vendorId());
            } elseif ($scope === 'global') {
                $query->global();
            }
        }

        $categories = $query->paginate(20)->withQueryString();
        $filters    = $request->only(['search', 'scope']);

        return view('vendor.categories.index', compact('categories', 'filters'));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create(): View
    {
        return view('vendor.categories.form', ['category' => null]);
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['vendor_id'] = $this->vendorId();

        // Handle image separately — don't pass image_base64 to the model
        $imageData = $this->handleImage($request);
        unset($data['image'], $data['image_base64']);
        if ($imageData !== null) {
            $data['image'] = $imageData;
        }

        Category::create($data);

        return redirect()->route('vendor.categories.index')
                         ->with('success', 'Category created successfully.');
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function edit(int $id): View
    {
        $category = Category::byVendor($this->vendorId())->findOrFail($id);
        return view('vendor.categories.form', compact('category'));
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, int $id): RedirectResponse
    {
        $category = Category::byVendor($this->vendorId())->findOrFail($id);

        $data = $this->validated($request, $id);

        // Handle image separately — don't pass image_base64 to the model
        $newImage = $this->handleImage($request, $category->image);
        unset($data['image'], $data['image_base64']);
        if ($newImage !== null) {
            $data['image'] = $newImage;
        }

        $category->update($data);

        return redirect()->route('vendor.categories.index')
                         ->with('success', 'Category updated.');
    }

    // ── Toggle active ─────────────────────────────────────────────────────────

    public function toggle(int $id): RedirectResponse
    {
        $category = Category::byVendor($this->vendorId())->findOrFail($id);
        $category->update(['active' => ! $category->active]);

        return back()->with('success', 'Category status updated.');
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(int $id): RedirectResponse
    {
        $category = Category::byVendor($this->vendorId())->findOrFail($id);

        if ($category->products()->exists()) {
            return back()->with('error', 'Cannot delete a category that has products assigned to it.');
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()->route('vendor.categories.index')
                         ->with('success', 'Category deleted.');
    }

    // ── Shared validation ─────────────────────────────────────────────────────

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $vendorId = $this->vendorId();

        // Uniqueness is scoped per vendor: two different vendors CAN have the same category name.
        // Rule: name must be unique within this vendor's own categories (ignoring the current record on update).
        $uniqueRule = Rule::unique('categories', 'name')
            ->where('vendor_id', $vendorId);

        if ($ignoreId) {
            $uniqueRule = $uniqueRule->ignore($ignoreId);
        }

        return $request->validate([
            'name'        => ['required', 'string', 'max:255', $uniqueRule],
            'description' => ['nullable', 'string', 'max:2000'],
            'active'      => ['boolean'],
            'image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'image_base64' => ['nullable', 'string'],
        ]);
    }

    // ── Image helper ──────────────────────────────────────────────────────────

    /**
     * Handle image upload — supports both direct file upload and base64.
     * Returns the stored path, or null if no new image was provided.
     */
    private function handleImage(Request $request, ?string $existing = null): ?string
    {
        // Prefer direct file upload
        if ($request->hasFile('image')) {
            if ($existing) {
                Storage::disk('public')->delete($existing);
            }
            return $request->file('image')->store('categories', 'public');
        }

        // Fall back to base64 (legacy / JS-encoded)
        if ($request->filled('image_base64')) {
            $base64   = preg_replace('/^data:image\/[a-z]+;base64,/', '', $request->image_base64);
            $filename = 'categories/vendor_cat_' . $this->vendorId() . '_' . time() . '.jpg';

            if ($existing) {
                Storage::disk('public')->delete($existing);
            }

            Storage::disk('public')->put($filename, base64_decode($base64));
            return $filename;
        }

        return null;
    }
}
