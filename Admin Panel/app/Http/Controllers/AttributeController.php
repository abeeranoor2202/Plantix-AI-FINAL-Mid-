<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth'); // Removed to avoid guard conflicts
    }

    public function index()
    {
        $attributes = Attribute::orderBy('title')->get();
        return view('admin.attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('admin.attributes.create');
    }

    public function store(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);
        Attribute::create(['title' => $request->title]);
        return response()->json(['success' => true, 'redirect' => route('admin.attributes')]);
    }

    public function edit($id)
    {
        $attribute = Attribute::findOrFail($id);
        return view('admin.attributes.edit', compact('attribute', 'id'));
    }

    public function update(Request $request, $id)
    {
        $attribute = Attribute::findOrFail($id);
        $request->validate(['title' => 'required|string|max:255']);
        $attribute->update(['title' => $request->title]);
        return response()->json(['success' => true, 'redirect' => route('admin.attributes')]);
    }

    public function destroy($id)
    {
        Attribute::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}