<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * VendorAttributeController
 *
 * Full CRUD for vendor-created product attributes.
 *
 * Visibility rules:
 *   - ALL attributes (admin + every vendor) are visible to everyone.
 *   - A vendor can only edit / delete attributes they created (vendor_id = their id).
 *   - Admin-created attributes (vendor_id = null) are read-only for vendors.
 */
class VendorAttributeController extends Controller
{
    private function vendorId(): int
    {
        return auth('vendor')->user()->vendor->id;
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Attribute::withCount(['values', 'categories'])
            ->with('createdByVendor')
            ->orderByRaw('vendor_id IS NULL ASC')   // global first
            ->orderBy('name')
            ->orderBy('title');

        if ($request->filled('search')) {
            $term = '%' . trim((string) $request->input('search')) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('title', 'like', $term);
            });
        }

        if ($request->filled('scope')) {
            $scope = (string) $request->input('scope');
            if ($scope === 'mine') {
                $query->byVendor($this->vendorId());
            } elseif ($scope === 'global') {
                $query->global();
            }
        }

        $attributes = $query->paginate(20)->withQueryString();
        $filters    = $request->only(['search', 'scope']);

        return view('vendor.attributes.index', compact('attributes', 'filters'));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create(): View
    {
        return view('vendor.attributes.form', ['attribute' => null]);
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data, $request) {
            $attribute = Attribute::create([
                'vendor_id' => $this->vendorId(),
                'name'      => $data['name'],
                'title'     => $data['name'],
                'type'      => $data['type'],
                'unit'      => $data['unit'] ?? null,
            ]);

            $this->syncValues($attribute, $request->input('values', []));
        });

        return redirect()->route('vendor.attributes.index')
                         ->with('success', 'Attribute created successfully.');
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function edit(int $id): View
    {
        $attribute = Attribute::with('values')->byVendor($this->vendorId())->findOrFail($id);
        return view('vendor.attributes.form', compact('attribute'));
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, int $id): RedirectResponse
    {
        $attribute = Attribute::with('values')->byVendor($this->vendorId())->findOrFail($id);
        $data      = $this->validated($request, $id);

        DB::transaction(function () use ($attribute, $data, $request) {
            $attribute->update([
                'name'  => $data['name'],
                'title' => $data['name'],
                'type'  => $data['type'],
                'unit'  => $data['unit'] ?? null,
            ]);

            $this->syncValues($attribute, $request->input('values', []));
        });

        return redirect()->route('vendor.attributes.index')
                         ->with('success', 'Attribute updated.');
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(int $id): RedirectResponse
    {
        $attribute = Attribute::byVendor($this->vendorId())->findOrFail($id);

        if ($attribute->categories()->exists()) {
            return back()->with('error', 'Cannot delete an attribute that is assigned to categories.');
        }

        $attribute->values()->delete();
        $attribute->delete();

        return redirect()->route('vendor.attributes.index')
                         ->with('success', 'Attribute deleted.');
    }

    // ── Shared validation ─────────────────────────────────────────────────────

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'type'       => ['required', 'in:text,number,select,multi-select'],
            'unit'       => ['nullable', 'string', 'max:40'],
            'values'     => ['nullable', 'array'],
            'values.*'   => ['nullable', 'string', 'max:255'],
        ]);
    }

    // ── Value sync ────────────────────────────────────────────────────────────

    private function syncValues(Attribute $attribute, array $rawValues): void
    {
        $normalized = collect($rawValues)
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values();

        if (! in_array($attribute->type, [Attribute::TYPE_SELECT, Attribute::TYPE_MULTI_SELECT], true)) {
            $attribute->values()->delete();
            return;
        }

        $keep = [];
        foreach ($normalized as $index => $value) {
            $record = AttributeValue::updateOrCreate(
                ['attribute_id' => $attribute->id, 'value' => $value],
                ['sort_order' => $index]
            );
            $keep[] = $record->id;
        }

        $attribute->values()->when(! empty($keep), fn ($q) => $q->whereNotIn('id', $keep))->delete();
    }
}
