<?php

namespace App\Http\Controllers;

use App\Models\CoffeeShop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoffeeShopController extends Controller
{
    public function publicIndex(): JsonResponse
    {
        $features = CoffeeShop::whereNotNull('latitude')->whereNotNull('longitude')->orderBy('name')->get()->map(fn ($shop) => [
            'type' => 'Feature', 'id' => $shop->id,
            'geometry' => ['type' => 'Point', 'coordinates' => [$shop->longitude, $shop->latitude]],
            'properties' => [
                'name' => $shop->name, 'license_number' => $shop->license_number, 'address' => $shop->address,
                'ward' => $shop->ward, 'business_type' => $shop->business_type, 'business_code' => $shop->business_code,
                'location_accuracy' => $shop->location_accuracy, 'opening_hours' => $shop->opening_hours,
            ],
        ]);
        return response()->json(['type' => 'FeatureCollection', 'features' => $features]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = CoffeeShop::query();
        if ($search = $request->query('search')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
                ->orWhere('ward', 'like', "%{$search}%"));
        }
        if ($ward = $request->query('ward')) $query->where('ward', $ward);
        if ($type = $request->query('type')) $query->where('business_type', $type);
        return response()->json(['data' => $query->orderBy('name')->get()]);
    }

    public function show(CoffeeShop $coffeeShop): JsonResponse
    {
        return response()->json(['data' => $coffeeShop]);
    }

    public function store(Request $request): JsonResponse
    {
        $shop = CoffeeShop::create($this->validateShop($request));
        return response()->json(['data' => $shop], 201);
    }

    public function update(Request $request, CoffeeShop $coffeeShop): JsonResponse
    {
        $coffeeShop->update($this->validateShop($request, true));
        return response()->json(['data' => $coffeeShop->fresh()]);
    }

    public function destroy(CoffeeShop $coffeeShop): JsonResponse
    {
        $coffeeShop->delete();
        return response()->json(['message' => 'Data kedai berhasil dihapus.']);
    }

    private function validateShop(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'source_key' => 'nullable|string|max:100',
            'license_number' => 'nullable|string|max:100',
            'name' => 'required|string|max:200',
            'address' => 'nullable|string|max:1000',
            'ward' => 'nullable|string|max:150',
            'business_code' => 'nullable|string|max:20',
            'business_type' => 'nullable|string|max:150',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_accuracy' => 'required|string|max:100',
            'opening_hours' => 'nullable|string|max:255',
            'source_year' => 'nullable|integer|min:2000|max:2100',
            'source' => 'nullable|string|max:255',
        ]);
    }
}
