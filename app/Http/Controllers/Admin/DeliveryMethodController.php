<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryMethod;
use Illuminate\Http\Request;

class DeliveryMethodController extends Controller {
    public function all() {
        $pageTitle = 'All Delivery Methods';
        $deliveryMethods = DeliveryMethod::orderBy('name')->paginate(getPaginate());
        return view('admin.delivery_method.index', compact('pageTitle', 'deliveryMethods'));
    }

    public function store(Request $request, $id = 0) {
        $request->validate([
            'name' => 'required|max:255|unique:delivery_methods,name,' . $id
        ]);

        if ($id) {
            $method = DeliveryMethod::findOrFail($id);
            $notification = 'Method updated successfully';
        } else {
            $method = new DeliveryMethod();
            $notification = 'Method added successfully';
        }

        $method->name = $request->name;
        $method->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function updateStatus($id) {
        return DeliveryMethod::changeStatus($id);
    }
}
