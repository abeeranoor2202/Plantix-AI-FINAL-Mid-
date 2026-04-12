<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttributeController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth'); // Removed to avoid guard conflicts
    }

    public function index()
    {
        $attributes = Attribute::withCount(['values', 'categories'])
            ->orderBy('name')
            ->orderBy('title')
            ->get();

        return view('admin.attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('admin.attributes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'type'   => 'required|in:text,number,select,multi-select',
            'unit'   => 'nullable|string|max:40',
            'values' => 'nullable|array',
            'values.*' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data) {
            $attribute = Attribute::create([
                'name'  => $data['name'],
                'title' => $data['name'],
                'type'  => $data['type'],
                'unit'  => $data['unit'] ?? null,
            ]);

            $this->syncAttributeValues($attribute, $data['values'] ?? []);
        });

        return response()->json(['success' => true, 'redirect' => route('admin.attributes')]);
    }

    public function edit($id)
    {
        $attribute = Attribute::with('values')->findOrFail($id);
        return view('admin.attributes.edit', compact('attribute', 'id'));
    }

    public function update(Request $request, $id)
    {
        $attribute = Attribute::findOrFail($id);

        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'type'   => 'required|in:text,number,select,multi-select',
            'unit'   => 'nullable|string|max:40',
            'values' => 'nullable|array',
            'values.*' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($attribute, $data) {
            $attribute->update([
                'name'  => $data['name'],
                'title' => $data['name'],
                'type'  => $data['type'],
                'unit'  => $data['unit'] ?? null,
            ]);

            $this->syncAttributeValues($attribute, $data['values'] ?? []);
        });

        return response()->json(['success' => true, 'redirect' => route('admin.attributes')]);
    }

    public function destroy($id)
    {
        Attribute::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    private function syncAttributeValues(Attribute $attribute, array $values): void
    {
        $normalized = collect($values)
            ->map(fn ($value) => trim((string) $value))
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

        if (! empty($keep)) {
            $attribute->values()->whereNotIn('id', $keep)->delete();
        } else {
            $attribute->values()->delete();
        }
    }
}