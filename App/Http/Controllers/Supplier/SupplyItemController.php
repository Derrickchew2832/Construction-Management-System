<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplyItemController extends Controller
{
    // Show the list of supply items
    public function supplyIndex()
    {
        // Fetch all supply items for the current supplier
        $supplierId = Auth::id();
        $supplyItems = DB::table('supply_items')
            ->where('supplier_id', $supplierId)
            ->orderBy('supplier_item_number', 'asc')
            ->get();

        // Return the view with the list of items
        return view('supplier.supplyitems', compact('supplyItems')); // Updated to use 'supplyitems.blade.php'
    }

    // Store a new supply item
    public function supplyStore(Request $request)
    {
        // Validate input
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric', 
            'stock_quantity' => 'required|integer|min:1',
        ], 
             
);

        // Get the next available supplier item number for the current supplier
        $supplierId = Auth::id();
        $nextItemNumber = DB::table('supply_items')
            ->where('supplier_id', $supplierId)
            ->max('supplier_item_number') + 1;

        // Insert into the supply_items table
        DB::table('supply_items')->insert([
            'supplier_id' => $supplierId,
            'supplier_item_number' => $nextItemNumber,
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'price' => $validatedData['price'],
            'stock_quantity' => $validatedData['stock_quantity'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Supply item added successfully.');
    }

    // Show the form to edit a supply item
    public function supplyEdit($id)
    {
        // Fetch the supply item to edit
        $item = DB::table('supply_items')->where('id', $id)->first();

        if (!$item) {
            return redirect()->back()->with('error', 'Supply item not found.');
        }

        // Return the edit view with the supply item
        return view('supplier.edit_supplyitem', compact('item'));
    }

    // Update an existing supply item
    public function supplyUpdate(Request $request, $id)
{
    // Validate the input data
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric',
        'stock_quantity' => 'required|integer|min:0',
    ]);

    // Update the supply item in the database
    DB::table('supply_items')
        ->where('id', $id)
        ->update([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'price' => $validatedData['price'],
            'stock_quantity' => $validatedData['stock_quantity'],
            'updated_at' => now(),
        ]);

    return redirect()->route('supplier.supplyitems.index')->with('success', 'Supply item updated successfully.');
}


    // Delete a supply item
    public function supplyDelete($id)
    {
        // Check if the supply item exists
        $item = DB::table('supply_items')->where('id', $id)->first();

        if (!$item) {
            return redirect()->back()->with('error', 'Supply item not found.');
        }

        // Delete the supply item from the database
        DB::table('supply_items')->where('id', $id)->delete();

        return redirect()->route('supplier.supplyitems.index')->with('success', 'Supply item deleted successfully.');
    }
}
