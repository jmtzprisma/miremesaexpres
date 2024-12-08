<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\SendingPurpose;
use Illuminate\Http\Request;

class SendingPurposeController extends Controller
{
    public function index()
    {
        $pageTitle       = 'Sending Purpose';
        $sendingPurposes = SendingPurpose::searchable(['name'])->latest()->paginate(getPaginate());
        return view('admin.sending_purposes', compact('pageTitle', 'sendingPurposes'));
    }

    public function saveSendingPurpose(Request $request, $id = 0)
    {
        $request->validate([
            'name' => 'required|string:255|unique:sending_purposes,name,' . $id,
        ]);

        if ($id) {
            $sendingPurposes         = SendingPurpose::findOrFail($id);
            $sendingPurposes->status = $request->status ? Status::ENABLE : Status::DISABLE;
            $notification            = 'Sending purpose updated successfully';
        } else {
            $sendingPurposes = new SendingPurpose();
            $notification    = 'Sending purpose added successfully';
        }

        $sendingPurposes->name = $request->name;
        $sendingPurposes->save();
        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }
}
