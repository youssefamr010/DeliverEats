<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\ItemVariant;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Manage menu categories
     */
    public function categories(Restaurant $restaurant)
    {
        $this->authorizeOwner($restaurant);
        $categories = $restaurant->categories()->with('menuItems')->get();
        return view('menu.categories', compact('restaurant', 'categories'));
    }

    public function storeCategory(Request $request, Restaurant $restaurant)
    {
        $this->authorizeOwner($restaurant);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'sort_order'  => 'nullable|integer',
        ]);

        $validated['restaurant_id'] = $restaurant->id;
        Category::create($validated);

        return back()->with('success', 'Category created!');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $this->authorizeOwner($category->restaurant);

        $category->update($request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'boolean',
        ]));

        return back()->with('success', 'Category updated!');
    }

    public function destroyCategory(Category $category)
    {
        $this->authorizeOwner($category->restaurant);
        $category->delete();
        return back()->with('success', 'Category deleted!');
    }

    /**
     * Menu items management
     */
    public function items(Category $category)
    {
        $this->authorizeOwner($category->restaurant);
        $category->load('menuItems.variants');
        return view('menu.items', compact('category'));
    }

    public function storeItem(Request $request, Category $category)
    {
        $this->authorizeOwner($category->restaurant);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price'  => 'required|numeric|min:0',
            'prep_time'   => 'nullable|integer|min:1',
        ]);

        $validated['category_id'] = $category->id;
        MenuItem::create($validated);

        return back()->with('success', 'Menu item added!');
    }

    public function updateItem(Request $request, MenuItem $menuItem)
    {
        $this->authorizeOwner($menuItem->category->restaurant);

        $menuItem->update($request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'base_price'   => 'required|numeric|min:0',
            'is_available' => 'boolean',
            'is_featured'  => 'boolean',
            'prep_time'    => 'nullable|integer|min:1',
        ]));

        return back()->with('success', 'Item updated!');
    }

    public function toggleAvailability(MenuItem $menuItem)
    {
        $this->authorizeOwner($menuItem->category->restaurant);
        $menuItem->update(['is_available' => !$menuItem->is_available]);
        return back()->with('success', 'Availability toggled!');
    }

    public function destroyItem(MenuItem $menuItem)
    {
        $this->authorizeOwner($menuItem->category->restaurant);
        $menuItem->delete();
        return back()->with('success', 'Item deleted!');
    }

    /**
     * Variants management
     */
    public function storeVariant(Request $request, MenuItem $menuItem)
    {
        $this->authorizeOwner($menuItem->category->restaurant);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'price_modifier' => 'required|numeric',
        ]);

        $validated['menu_item_id'] = $menuItem->id;
        ItemVariant::create($validated);

        return back()->with('success', 'Variant added!');
    }

    public function destroyVariant(ItemVariant $variant)
    {
        $this->authorizeOwner($variant->menuItem->category->restaurant);
        $variant->delete();
        return back()->with('success', 'Variant deleted!');
    }

    private function authorizeOwner(Restaurant $restaurant)
    {
        if ($restaurant->owner_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403);
        }
    }
}
