<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    // Dashboard view
    public function dashboard()
    {
        return view('supplier.dashboard');  // Ensure this view file exists (resources/views/supplier/dashboard.blade.php)
    }

    // Show all quote requests for the supplier
    public function quotes()
    {
        $supplierId = Auth::id();

        // Retrieve pending supply orders for this supplier using DB facade
        $supplyOrders = DB::table('supply_orders')
            ->where('supply_orders.supplier_id', $supplierId)
            ->join('projects', 'supply_orders.project_id', '=', 'projects.id')
            ->join('users as contractors', 'supply_orders.contractor_id', '=', 'contractors.id')
            ->join('supply_items', 'supply_orders.supply_item_id', '=', 'supply_items.id')
            ->select(
                'supply_orders.*',
                'projects.name as project_name',
                'contractors.name as contractor_name',
                'supply_items.name as item_name',
                'supply_items.stock_quantity',   // Correct stock column
                'supply_items.price'             // Correct price column
            )
            ->get();

        return view('supplier.quotes.dashboard', compact('supplyOrders'));
    }

    // Handle accept or reject quote request
    public function updateQuote(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Accepted,Rejected',
        ]);

        // Fetch the supply order
        $order = DB::table('supply_orders')
            ->where('id', $id)
            ->first();

        // Handle acceptance
        if ($request->status == 'Accepted') {
            // Fetch the corresponding supply item
            $item = DB::table('supply_items')
                ->where('id', $order->supply_item_id)
                ->first();

            // Check if the stock is available for the requested quantity
            if ($item->stock_quantity < $order->quantity) {
                return redirect()->route('supplier.quotes.dashboard')
                    ->with('error', 'Not enough stock available to fulfill the order.');
            }

            // Deduct the stock
            DB::table('supply_items')
                ->where('id', $order->supply_item_id)
                ->decrement('stock_quantity', $order->quantity);

            // Update the supply order status to accepted
            DB::table('supply_orders')
                ->where('id', $id)
                ->update([
                    'status' => 'Accepted',
                    'updated_at' => now(),
                ]);

            return redirect()->route('supplier.quotes.dashboard')
                ->with('success', 'Order has been accepted and stock has been updated.');
        }

        // Handle rejection
        if ($request->status == 'Rejected') {
            DB::table('supply_orders')
                ->where('id', $id)
                ->update([
                    'status' => 'rejected',
                    'updated_at' => now(),
                ]);

            return redirect()->route('supplier.quotes.dashboard')
                ->with('success', 'Order has been rejected.');
        }
    }

    // Submit delivery form and image
    public function submitDelivery(Request $request, $id)
    {
        $request->validate([
            'delivery_form' => 'required|mimes:pdf|max:2048',
            'delivery_image' => 'required|image|max:2048',
        ]);

        // Store the files
        $deliveryFormPath = $request->file('delivery_form')->store('delivery_forms');
        $deliveryImagePath = $request->file('delivery_image')->store('delivery_images');

        // Update the supply order with delivery details
        DB::table('supply_orders')
            ->where('id', $id)
            ->update([
                'delivery_form' => $deliveryFormPath,
                'delivery_image' => $deliveryImagePath,
                'status' => 'uShipped',
                'updated_at' => now(),
            ]);

        return redirect()->route('supplier.quotes.dashboard')->with('success', 'Delivery started successfully!');
    }
}
