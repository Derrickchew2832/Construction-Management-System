<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskSupplyController extends Controller
{
    // Display the available suppliers for the project
    public function showSuppliers($projectId)
{
    // Fetch the project, including the project manager's ID
    $project = DB::table('projects')->where('id', $projectId)->first();

    // Fetch the project manager's name from the users table using the project_manager_id
    $projectManager = DB::table('users')->where('id', $project->project_manager_id)->first();
    $projectManagerName = $projectManager->name ?? 'Unknown'; // Fallback if name is not found

    // Fetch the main contractor's name
    $mainContractor = DB::table('users')
        ->join('project_contractor', 'users.id', '=', 'project_contractor.contractor_id')
        ->where('project_contractor.project_id', $projectId)
        ->where('project_contractor.main_contractor', 1)
        ->first();
    $mainContractorName = $mainContractor->name ?? 'Unknown'; // Fallback if name is not found

    // Calculate the total project days
    $totalProjectDays = \Carbon\Carbon::parse($project->start_date)
        ->diffInDays(\Carbon\Carbon::parse($project->end_date));

    // Fetch suppliers
    $suppliers = DB::table('users')
        ->where('role_id', function ($query) {
            $query->select('id')->from('roles')->where('name', 'supplier');
        })
        ->get();

    // Fetch supply orders for the project
    $orders = DB::table('supply_orders')
        ->where('project_id', $projectId)
        ->where('contractor_id', auth()->user()->id)
        ->get();

    // Pass all necessary data to the view
    return view('tasks.supply_order', compact(
        'project', 
        'projectManagerName', 
        'mainContractorName', 
        'totalProjectDays', 
        'suppliers', 
        'orders', 
        'projectId'
    ));
}



    // Fetch supply items for a specific supplier in the project context
    public function getSupplierItems($projectId, $supplierId)
    {
        $supplyItems = DB::table('supply_items')
            ->where('supplier_id', $supplierId)
            ->where('stock_quantity', '>', 0)
            ->get();

        $html = view('contractor.partials.supply_items_list', compact('supplyItems'))->render();
        return response()->json(['html' => $html]);
    }

    // Handle placing the order for the project
    public function placeOrder(Request $request, $projectId)
    {
        $validated = $request->validate([
            'delivery_address' => 'required|string',
            'items' => 'required|array',
            'items.*.item_id' => 'required|integer|exists:supply_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $contractorId = auth()->user()->id;

        // Retrieve contractor's name to be used as the company name
        $contractor = DB::table('users')->where('id', $contractorId)->first();
        $companyName = $contractor->name; // Use the contractor's name as the company name

        foreach ($request->items as $item) {
            $supplyItem = DB::table('supply_items')->where('id', $item['item_id'])->first();
            
            // Insert the supply order into the database
            DB::table('supply_orders')->insert([
                'project_id' => $projectId,
                'contractor_id' => $contractorId,
                'supplier_id' => $supplyItem->supplier_id,
                'supply_item_id' => $supplyItem->id,
                'item_name' => $supplyItem->name,
                'quantity' => $item['quantity'],
                'quoted_price' => $supplyItem->price * $item['quantity'],
                'status' => 'pending',
                'company_name' => $companyName, // Automatically use the contractor's name
                'delivery_address' => $request->input('delivery_address'),
                'description' => $request->input('description'), // Add optional description
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Decrease stock quantity
            DB::table('supply_items')->where('id', $item['item_id'])->decrement('stock_quantity', $item['quantity']);
        }

        return response()->json(['success' => true]);
    }
}
