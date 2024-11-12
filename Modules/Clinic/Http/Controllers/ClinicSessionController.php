<?php

namespace Modules\Clinic\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Clinic\Models\ClinicSession;

class ClinicSessionController extends Controller
{
    public function __construct()
    {
        // Page clinic_name
        $this->module_title = 'clinic.clinic_session';
        // module name
        $this->module_name = 'clinic-session';

        // module icon
        $this->module_icon = 'fa-solid fa-clipboard-list';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
    }


    public function index_list(Request $request)
    {
        $clinic_id = $request->input('clinic_id');

        $query_data = ClinicSession::where('clinic_id',$clinic_id)->get();

        return response()->json(['data' => $query_data , 'status' => true]);
    }
   
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('clinic::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clinic::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        
        $clinic_id = $data['clinic_id'];

        $weekdays = $data['weekdays'];

        foreach ($weekdays as $key => $value) {

            $value['clinic_id'] =$clinic_id;

            ClinicSession::updateOrCreate(['clinic_id' => $clinic_id,'id' => $value['id'] ?? -1], $value);
        }

        $data = ClinicSession::where('clinic_id',$clinic_id)->get();

        $message = __('clinic.clinic_session_added');

        return response()->json(['message' => $message, 'data' => $data,  'status' => true], 200);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('clinic::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('clinic::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
