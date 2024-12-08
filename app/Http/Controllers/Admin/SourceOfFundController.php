<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\SourceOfFund;
use Illuminate\Http\Request;

class SourceOfFundController extends Controller
{
    public function index()
    {
        $pageTitle    = 'Source of Funds';
        $sourceOfFunds = SourceOfFund::searchable(['name'])->latest()->paginate(getPaginate());
        return view('admin.source_of_funds', compact('pageTitle', 'sourceOfFunds'));
    }

    public function saveSourceOfFund(Request $request, $id = 0)
    {
        $request->validate([
            'name' => 'required|string:255|unique:source_of_funds,name,' . $id,
        ]);

        if ($id) {
            $sof           = SourceOfFund::findOrFail($id);
            $notification  = 'Source of fund updated successfully';
            $sof->status   = $request->status ? Status::ENABLE : Status::DISABLE;
        } else {
            $sof           = new SourceOfFund();
            $notification  = 'Source of fund added successfully';
        }

        $sof->name   = $request->name;
        $sof->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }
}
