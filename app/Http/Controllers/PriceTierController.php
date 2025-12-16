<?php

namespace App\Http\Controllers;

use App\Models\PriceTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PriceTierController extends Controller
{
    public function index()
    {
        $priceTiers = PriceTier::orderBy('priority')->paginate(10);
        return view('price_tiers.index', compact('priceTiers'));
    }

    public function create()
    {
        return view('price_tiers.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        PriceTier::create($request->all());

        return redirect()->route('price-tiers.index')
            ->with('success', 'Tingkat harga berhasil ditambahkan');
    }

    public function edit(PriceTier $priceTier)
    {
        return view('price_tiers.edit', compact('priceTier'));
    }

    public function update(Request $request, PriceTier $priceTier)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $priceTier->update($request->all());

        return redirect()->route('price-tiers.index')
            ->with('success', 'Tingkat harga berhasil diupdate');
    }

    public function destroy(PriceTier $priceTier)
    {
        $priceTier->delete();

        return redirect()->route('price-tiers.index')
            ->with('success', 'Tingkat harga berhasil dihapus');
    }
}