<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskSupplyController extends Controller
{
    public function showSuppliers($projectId)
    {
        // Get the authenticated contractor ID
        $contractorId = auth()->user()->id;
    
        // Fetch the project
        $project = DB::table('projects')->where('id', $projectId)->first();
    
        // Ensure the project is found
        if (!$project) {
            return back()->with('error', 'Project not found.');
        }
    
        // Fetch the project manager's name
        $projectManager = DB::table('users')->where('id', $project->project_manager_id)->first();
        $projectManagerName = $projectManager ? $projectManager->name : 'Unknown';
    
        // Fetch the main contractor's name
        $mainContractor = DB::table('users')
            ->join('project_contractor', 'users.id', '=', 'project_contractor.contractor_id')
            ->where('project_contractor.project_id', $projectId)
            ->where('project_contractor.main_contractor', 1)
            ->first();
        $mainContractorName = $mainContractor ? $mainContractor->name : 'Unknown';
    
        // Calculate total project days based on start and end dates
        $startDate = \Carbon\Carbon::parse($project->start_date);
        $endDate = \Carbon\Carbon::parse($project->end_date);
        $totalProjectDays = $startDate->diffInDays($endDate);
    
        // Fetch the contractor's quoted price for the task from the task_contractor table
        $task = DB::table('tasks')
            ->join('task_contractor', 'tasks.id', '=', 'task_contractor.task_id')
            ->where('tasks.project_id', $projectId)
            ->where('task_contractor.contractor_id', $contractorId)
            ->select('task_contractor.quoted_price', 'tasks.id as task_id')
            ->first();
    
        // Ensure the task exists and fetch the quoted price
        $quotedPrice = $task ? $task->quoted_price : 0;
    
        // Fetch the total supply order price only for confirmed/received orders by the authenticated contractor
        $totalSupplyOrderPrice = DB::table('supply_orders')
            ->where('project_id', $projectId)
            ->where('contractor_id', $contractorId)
            ->where('status', 'Received')
            ->sum('quoted_price');
    
        // Calculate the remaining money
        $remainingMoney = $quotedPrice - $totalSupplyOrderPrice;
    
        // Fetch suppliers
        $suppliers = DB::table('users')
            ->where('role_id', function ($query) {
                $query->select('id')->from('roles')->where('name', 'supplier');
            })
            ->get();
    
        // Fetch supply orders for the project
        $orders = DB::table('supply_orders')
            ->join('supply_items', 'supply_orders.supply_item_id', '=', 'supply_items.id')
            ->join('users', 'supply_orders.supplier_id', '=', 'users.id')
            ->where('supply_orders.project_id', $projectId)
            ->where('supply_orders.contractor_id', $contractorId)
            ->select('supply_orders.*', 'supply_items.name as item_name', 'users.name as supplier_name')
            ->get();
    
        return view('tasks.supply_order', compact(
            'project',
            'task',
            'quotedPrice',
            'totalSupplyOrderPrice',
            'remainingMoney',
            'suppliers',
            'orders',
            'projectId',
            'projectManagerName',
            'mainContractorName',
            'totalProjectDays'
        ));
    }
    
    public function getSupplierItems($projectId, $supplierId)
    {
        $supplyItems = DB::table('supply_items')
            ->where('supplier_id', $supplierId)
            ->where('stock_quantity', '>', 0)
            ->get();

        $html = view('tasks.partials.supply_items_list', compact('supplyItems'))->render();
        return response()->json(['html' => $html]);
    }

    public function placeOrder(Request $request, $projectId)
    {
        // Ensure only the correct contractor can place an order
        $contractorId = auth()->user()->id;

        // Validate that the contractor is assigned to at least one task within the project
        $isValidContractor = DB::table('task_contractor')
            ->where('contractor_id', $contractorId)
            ->whereIn('task_id', function ($query) use ($projectId) {
                $query->select('id')
                      ->from('tasks')
                      ->where('project_id', $projectId);
            })
            ->exists();

        if (!$isValidContractor) {
            return response()->json(['error' => 'Unauthorized contractor'], 403);
        }

        // Validate order input
        $validated = $request->validate([
            'delivery_address' => 'required|string',
            'items' => 'required|array',
            'items.*.item_id' => 'required|integer|exists:supply_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        // Contractor's name for order record
        $contractor = DB::table('users')->where('id', $contractorId)->first();
        $companyName = $contractor->name;

        // Calculate total amount for new order
        $newOrderTotal = 0;
        foreach ($request->items as $item) {
            $supplyItem = DB::table('supply_items')->where('id', $item['item_id'])->first();
            $newOrderTotal += $supplyItem->price * $item['quantity'];
        }

        // Check current total order cost for the project and contractor
        $currentOrderTotal = DB::table('supply_orders')
            ->where('project_id', $projectId)
            ->where('contractor_id', $contractorId)
            ->sum('quoted_price');

        // Calculate the new total with the new order
        $totalAfterNewOrder = $currentOrderTotal + $newOrderTotal;

        // Ensure the new order doesn't exceed the quoted price
        $quotedPrice = DB::table('task_contractor')
            ->where('contractor_id', $contractorId)
            ->whereIn('task_id', function ($query) use ($projectId) {
                $query->select('id')
                      ->from('tasks')
                      ->where('project_id', $projectId);
            })
            ->value('quoted_price');

        if ($totalAfterNewOrder > $quotedPrice) {
            return response()->json(['error' => 'Order exceeds quoted price limit. Please reduce the order amount.'], 400);
        }

        // Place the order
        foreach ($request->items as $item) {
            $supplyItem = DB::table('supply_items')->where('id', $item['item_id'])->first();

            DB::table('supply_orders')->insert([
                'project_id' => $projectId,
                'contractor_id' => $contractorId,
                'supplier_id' => $supplyItem->supplier_id,
                'supply_item_id' => $supplyItem->id,
                'quantity' => $item['quantity'],
                'quoted_price' => $supplyItem->price * $item['quantity'],
                'status' => 'pending',
                'company_name' => $companyName,
                'delivery_address' => $request->input('delivery_address'),
                'description' => $request->input('description'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function OrderReceived(Request $request, $projectId, $orderId)
    {
        // Validate the received image if it's required
        $request->validate([
            'received_image' => 'required|image|max:2048',
        ]);

        // Update the order status to 'Received'
        DB::table('supply_orders')
            ->where('id', $orderId)
            ->update([
                'status' => 'received', 
                'received_image' => $request->file('received_image')->store('received_images', 'public'),
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Order marked as received successfully.');
    }
}
