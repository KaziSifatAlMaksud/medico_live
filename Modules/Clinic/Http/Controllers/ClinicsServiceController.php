<?php

namespace Modules\Clinic\Http\Controllers;

use App\Authorizable;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\CustomField\Models\CustomField;
use Modules\CustomField\Models\CustomFieldGroup;
use Modules\Clinic\Models\ClinicsCategory;
use Modules\Clinic\Models\ClinicsService;
use Yajra\DataTables\DataTables;
use Modules\Clinic\Http\Requests\ClinicsServiceRequest;
use Carbon\Carbon;
use Modules\Clinic\Models\Doctor;
use Illuminate\Support\Str;
use Modules\Clinic\Models\ClinicServiceMapping;
use Modules\Clinic\Models\DoctorServiceMapping;
use App\Models\User;
use Modules\Clinic\Models\Clinics;
use Modules\Appointment\Models\PatientEncounter;
use Modules\Clinic\Models\SystemService;
use Modules\Appointment\Trait\AppointmentTrait;
use Modules\Clinic\Models\Receptionist;


class ClinicsServiceController extends Controller
{
     use AppointmentTrait;

    protected string $exportClass = '\App\Exports\ClinicsServiceExport';


    public function __construct()
    {
        // Page Title
        $this->module_title = 'service.title';
        // module name
        $this->module_name = 'services';

        // module icon
        $this->module_icon = 'fa-solid fa-clipboard-list';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $module_action = 'List';
        $columns = CustomFieldGroup::columnJsonValues(new ClinicsService());
        $customefield = CustomField::exportCustomFields(new ClinicsService());

        $categories = ClinicsCategory::whereNull('parent_id')->get();
        $subcategories = ClinicsCategory::whereNotNull('parent_id')->get();
        $doctor = User::role('doctor')->SetRole(auth()->user())->with('doctor', 'doctorclinic')->get();
        $clinic = Clinics::SetRole(auth()->user())->with('clinicdoctor','specialty','clinicdoctor','receptionist')->where('status', 1)->get();
        $service = ClinicsService::SetRole(auth()->user())->with( 'sub_category','doctor_service','ClinicServiceMapping','systemservice')->where('status', 1)->get();

        $prices = $service->pluck('charges');
        $minPrice = 0;
        $maxPrice = $prices->max();

        $interval = 50; 
        $priceRanges = [];
        if ($maxPrice <= $interval) {
            $priceRanges[] = [$minPrice, $maxPrice];
        } else {
            for ($i = $minPrice; $i <= $maxPrice; $i += $interval) {
                $priceRanges[] = [$i, min($i + $interval, $maxPrice)];
            }
        }

        $doctor_id = null;
        if($request->has('doctor_id')){
            $doctor_id = $request->doctor_id;
        }

        $export_import = true;
        $export_columns = [
            [
                'value' => 'system_service_id',
                'text' => __('service.lbl_name'),
            ],
            [
                'value' => 'charges',
                'text' => __('service.lbl_price'),
            ],
            [
                'value' => 'duration_min',
                'text' => __('service.lbl_duration'),
            ],
            [
                'value' => 'category_id',
                'text' => __('service.lbl_category_id'),
            ],
        ];
        
        if (multiVendor() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin'))) {
            $export_columns[] = [
                'value' => 'vendor_id',
                'text' => __('multivendor.singular_title'),
            ];
        }
        
        $export_columns[] = [
            'value' => 'status',
            'text' => __('service.lbl_status'),
        ];
        $export_url = route('backend.services.export');
        return view('clinic::backend.services.index_datatable', compact('module_action', 'filter', 'categories', 'subcategories','clinic','service','priceRanges', 'columns', 'customefield', 'export_import', 'export_columns', 'export_url', 'doctor', 'doctor_id'));
    }
    public function index_list(Request $request)
    {

        $category_id = $request->category_id;
        
        $data = ClinicsService::SetRole(auth()->user())->with('category', 'sub_category','doctor_service','ClinicServiceMapping','systemservice');

        if (isset($category_id)) {
            $data->where('category_id', $category_id);
        }


        if($request->has('clinicId') && $request->clinicId) {
            $clinic_id = $request->clinicId;
            $data->whereHas('ClinicServiceMapping', function ($query) use ($clinic_id) {
                $query->where('clinic_id', $clinic_id);
            });
        }

        if($request->has('doctorId') && $request->doctorId) {
            $doctor_id = $request->doctorId;
            $data->whereHas('doctor_service', function ($query) use ($doctor_id) {
                $query->where('doctor_id', $doctor_id);
            });
        }

        if($request->has('clinic_id')) {
            $clinicId = explode(",", $request->clinic_id);
            $data->whereHas('ClinicServiceMapping', function ($query) use ($clinicId) {
                $query->whereIn('clinic_id', $clinicId);
            });
        }

        if($request->filled('encounter_id') && $request->encounter_id !=null){

            $encounterDetails=PatientEncounter::where('id',$request->encounter_id)->with('appointment')->first();

            if ($encounterDetails) {
                $doctor_id = $encounterDetails->doctor_id;
                $clinic_id = $encounterDetails->clinic_id;
        
                // Fetch all services associated with the doctor and clinic
                $services = DoctorServiceMapping::where('doctor_id', $doctor_id)
                    ->where('clinic_id', $clinic_id)
                    ->pluck('service_id');
        
                if ($services->isNotEmpty()) {
                    $data->whereIn('id', $services);
                }
            }
         
            // $data->whereHas('ClinicServiceMapping', function ($query) use ($clinic_id) {
            //     $query->where('clinic_id', $clinic_id);
            // });

            // $data->whereHas('doctor_service', function ($query) use ($doctor_id) {
            //     $query->where('doctor_id', $doctor_id);
            // });
        //    if($encounterDetails->appointment){

        //        $service_id=$encounterDetails->appointment->service_id;

        //        $data->where('id', $service_id);

        //    }

        }

        $query_data = $data->get();
    
        $data = [];

        foreach ($query_data as $row) {
            $doctorService = $row->doctor_service->first();
            $data[] = [
                'id' => $row->id,
                'name' => $row->name,
                'avatar' => $row->file_url,
                'charges' => optional($doctorService)->charges,
            ];
        }

        return response()->json($data);
    }

    public function service_price(Request $request)
    {
        $service_charge = 0;
    
        if ($request->has('service_id') && $request->has('doctor_id')) {
            $serviceId = $request->service_id;
            $doctorId = $request->doctor_id;
    
            $data = ClinicsService::where('id', $serviceId)
                ->with(['doctor_service' => function ($query) use ($doctorId) {
                    $query->where('doctor_id', $doctorId);
                }])
                ->first();
    
            if ($data && $data->doctor_service->isNotEmpty()) {
                $doctorService = $data->doctor_service->first();
                $service_charge = $doctorService->charges;

                if($data->discount==1){

                    if($data->discount==1){

                        $discount_amount = ($data->discount_type == 'percentage') 
                        ? $service_charge * $data->discount_value / 100 
                        : $data->discount_value;
            
                        $service_charge = $service_charge-$discount_amount;
                     }
                 }
            }
        }
 
        return response()->json($service_charge);
    }

    /* category wise service list */
    public function categort_services_list(Request $request)
    {
        $category = $request->category_id;
        $categoryService = ClinicsService::where('category_id', $category)->get();

        return $categoryService;
    }

    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                $ClinicsService = ClinicsService::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('clinic.clinicservice_status');
                break;

            case 'delete':

                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }

                ClinicsService::whereIn('id', $ids)->delete();
                $message = __('clinic.clinicservice_delete');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    public function update_status(Request $request, ClinicsService $id)
    {
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('clinic.clinicservice_status')]);
    }

    public function index_data(Datatables $datatable, Request $request)
    {
        $userId = auth()->id();
        $module_name = $this->module_name;

        $query = ClinicsService::SetRole(auth()->user())->with('category', 'sub_category','doctor_service','ClinicServiceMapping','systemservice')->withCount(['doctor_service']);

        // if (auth()->user()->hasRole('receptionist')) {
        //     $query = ClinicsService::whereHas('ClinicServiceMapping.center', function ($qry) use ($userId) {
        //         $qry->whereHas('receptionist', function ($qry) use ($userId) {
        //             $qry->where('receptionist_id', $userId);
        //         });
        //     });
        // }
        if($request->doctor_id !== null) {
            $doctor_id = $request->doctor_id;
            $query->whereHas('doctor_service', function($query) use ($doctor_id) {
                $query->where('doctor_id', $doctor_id);
            });
        }

        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
       
            if (isset($filter['service_id'])) {
                $query->where('system_service_id', $filter['service_id']);
            }
           
            if (isset($filter['price'])) {
                $priceRange = explode('-', $filter['price']);
                if (count($priceRange) === 2) {
                    $minPrice = (int) $priceRange[0];
                    $maxPrice = (int) $priceRange[1];
                    $query->whereBetween('charges', [$minPrice, $maxPrice]);
                }
            }
      
            if (isset($filter['category_id'])) {
                $query->where('category_id', $filter['category_id']);
            }
        
            if (isset($filter['sub_category_id'])) {
                $query->where('subcategory_id', $filter['sub_category_id']);
            }
            
            if (isset($filter['doctor_id'])) {
                $query->whereHas('doctor_service', function($query) use ($filter) {
                    $query->where('doctor_id', $filter['doctor_id']);
                });
               
            }
            if (isset($filter['clinic_id'])) {
                $query->whereHas('ClinicServiceMapping', function($query) use ($filter) {
                    $query->where('clinic_id', $filter['clinic_id']);
                });
              
            }
            if (isset($filter['clinic_admin'])) {
                $query->where('vendor_id', $filter['clinic_admin']);
            }
            
        }

        $datatable = $datatable->eloquent($query)
            ->addColumn('check', function ($data) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $data->id . '"  name="datatable_ids[]" value="' . $data->id . '" onclick="dataTableRowCheck(' . $data->id . ')">';
            })
            ->editColumn('name', function ($data) {
                return '<img src="' . $data->file_url . '" class="avatar avatar-50 rounded-pill me-3">' . $data->name;
            })

            ->addColumn('action', function ($data) use ($module_name) {
                return view('clinic::backend.services.action_column', compact('module_name', 'data'));
            })

            ->editColumn('charges', function ($data) {
                return \Currency::format($data->charges);
            })
            ->editColumn('duration_min', function ($data) {
                return $data->duration_min . ' Min';
            })
            ->editColumn('status', function ($row) {
                $checked = '';
                if ($row->status) {
                    $checked = 'checked="checked"';
                }

                return '
                    <div class="form-check form-switch ">
                        <input type="checkbox" data-url="' . route('backend.services.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $row->id . '"  name="status" value="' . $row->id . '" ' . $checked . '>
                    </div>
                ';
            })
            
            ->editColumn('category_id', function ($data) {
                $category = isset($data->category->name) ? $data->category->name : '-';
                if (isset($data->sub_category->name)) {
                    $category = $category . ' > ' . $data->sub_category->name;
                }

                return $category;
            })
            ->editColumn('vendor_id', function ($data) {
                $vendor = optional($data->vendor)->full_name;
                return $vendor;
            })
            ->filterColumn('category', function ($query, $keyword) {
                $query->whereHas('category', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', '%' . $keyword . '%');
                });
            })
            ->editColumn('doctor', function ($data) {
                if(auth()->user()->hasRole('doctor')){
                    $data->doctor_service_count = $data->doctor_service->where('doctor_id',auth()->user()->id)->count();
                    return "<button type='button' data-assign-module='" . $data->id . "' data-assign-target='#service-doctor-assign-form' data-assign-event='doctor_assign' class='btn btn-sm p-0 text-primary' data-bs-toggle='tooltip' title='Assign Doctor To Service'><span class='bg-primary-subtle rounded tbl-badge'><b>$data->doctor_service_count</b> </button></span>";
                }
                else{
                    return "<span class='bg-primary-subtle rounded tbl-badge'><b>$data->doctor_service_count</b>  <button type='button' data-assign-module='" . $data->id . "' data-assign-target='#service-doctor-assign-form' data-assign-event='doctor_assign' class='btn btn-sm p-0 text-primary' data-bs-toggle='tooltip' title='Assign Doctor To Service'><i class='ph ph-plus'></i></button></span>";
                }
            })

            ->editColumn('updated_at', function ($data) {
                $module_name = $this->module_name;

                $diff = Carbon::now()->diffInHours($data->updated_at);

                if ($diff < 25) {
                    return $data->updated_at->diffForHumans();
                } else {
                    return $data->updated_at->isoFormat('llll');
                }
            })

            ->orderColumns(['id'], '-:column $1');

        $customFieldColumns = CustomField::customFieldData($datatable, ClinicsService::CUSTOM_FIELD_MODEL, null);

        return $datatable->rawColumns(array_merge(['action', 'name','image', 'status', 'check', 'doctor','vendor_id'], $customFieldColumns))
            ->toJson();
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
    public function store(ClinicsServiceRequest $request)
    {
        $data = $request->except('file_url');
        $clinicid = $request->clinic_id;
        if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')) {
            $data['vendor_id']  = $request->filled('vendor_id') ? $request->vendor_id : auth()->user()->id;
        }elseif(auth()->user()->hasRole('receptionist')){
            $vendor_id = Receptionist::where('receptionist_id', auth()->user()->id)
            ->whereHas('clinics', function ($query) use ($clinicid) {
                $query->where('clinic_id', $clinicid);
            })
            ->pluck('vendor_id')
            ->first();
            $data['vendor_id'] = $vendor_id;
        }else{
            $data['vendor_id']  = auth()->user()->id;
        }
        if($request->has('system_service_id') && $request->system_service_id != null){
            $systemService = SystemService::where('id', $data['system_service_id'])->first();
            $data['name'] = $systemService->name;
        }

        $query = ClinicsService::create($data);

        if ($request->has('clinic_id') && $request->clinic_id != null) {

            $clinic_ids = explode(',', $request->clinic_id);

            foreach ($clinic_ids as $value) {

                $service_mapping_data = [

                    'service_id' => $query['id'],
                    'clinic_id' => $value,

                ];

                ClinicServiceMapping::create($service_mapping_data);
            }
        }
        if($request->has('doctor_id') && $request->doctor_id != null && $request->has('clinic_id') && $request->clinic_id != null){
            
                $service_mapping = [
                    'service_id' => $query['id'],
                    'clinic_id' => $request->clinic_id,
                    'doctor_id' => $request->doctor_id,
                    'charges' => $query['charges'] ?? 0,
                ];
            
                DoctorServiceMapping::updateOrCreate(
                    [
                        'service_id' => $query['id'],
                        'clinic_id' => $request->clinic_id,
                        'doctor_id' => $request->doctor_id
                    ],
                    $service_mapping
                );
            
        }
        if ($request->custom_fields_data) {
            $query->updateCustomFieldData(json_decode($request->custom_fields_data));
        }

        if ($request->hasFile('file_url')) {
            storeMediaFile($query, $request->file('file_url'));
        }

        $message = __('messages.create_form', ['form' => __('service.singular_title')]);

        if ($request->is('api/*')) {
            return response()->json(['message' => $message, 'data' => $data, 'status' => true], 200);
        } else {
            return response()->json(['message' => $message, 'status' => true], 200);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $module_action = 'Show';

        $data = ClinicsService::findOrFail($id);

        return view('clinic::backend.services.show', compact('module_action', "$data"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data = ClinicsService::with('ClinicServiceMapping')->findOrFail($id);

        if (!is_null($data)) {
            $custom_field_data = $data->withCustomFields();
            $data['custom_field_data'] = collect($custom_field_data->custom_fields_data)
                ->filter(function ($value) {
                    return $value !== null;
                })
                ->toArray();
        }

        $data['clinic_id'] = $data->ClinicServiceMapping->pluck('clinic_id') ?? [];
        $data['file_url'] = $data->file_url;

        return response()->json(['data' => $data, 'status' => true]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = ClinicsService::findOrFail($id);
        $request_data = $request->except('file_url');
        if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')) {
            $request_data['vendor_id']  = $request->filled('vendor_id') ? $request->vendor_id : auth()->user()->id;
        }
        $data->update($request_data);

        if ($request->has('clinic_id') && $request->clinic_id != null) {
            $clinic_ids = explode(',', $request->clinic_id);
            $clinic_ids = array_map('intval', $clinic_ids);
            ClinicServiceMapping::where('service_id', $data['id'])
                ->whereIn('clinic_id', $clinic_ids)
                ->forceDelete();
            foreach ($clinic_ids as $value) {
                $service_mapping_data = [
                    'service_id' => $data['id'],
                    'clinic_id' => $value,
                ];
        
                ClinicServiceMapping::create($service_mapping_data);
            }
        }
        if ($request->custom_fields_data) {
            $data->updateCustomFieldData(json_decode($request->custom_fields_data));
        }

        if ($request->hasFile('file_url') && $request->file_url !=null ) {
            
            storeMediaFile($data, $request->file('file_url'), 'file_url');
        }

        if ($request->is('api/*')) {
            if ($request->file_url && $request->file_url == null) {
                $data->clearMediaCollection('file_url');
            }
        }
        else{
            if ($request->file_url == null) {
                $data->clearMediaCollection('file_url');
            }
        }

        $message = __('messages.update_form', ['form' => __('service.singular_title')]);

        if ($request->is('api/*')) {
            return response()->json(['message' => $message, 'data' => $data, 'status' => true], 200);
        } else {
            return response()->json(['message' => $message, 'status' => true], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (env('IS_DEMO')) {
            return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
        }

        $data = ClinicsService::with('ClinicServiceMapping')->findOrFail($id);
        $data->ClinicServiceMapping()->delete();
        $data->delete();

        $message = __('messages.delete_form', ['form' => __('service.singular_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function trashed()
    {
        $module_name_singular = Str::singular($this->module_name);

        $module_action = 'Trash List';

        $data = ClinicsService::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate();

        return view('clinic::backend.services.trash', compact("$data", 'module_name_singular', 'module_action'));
    }

    public function restore($id)
    {
        $data = ClinicsService::withTrashed()->find($id);
        $data->restore();

        $message = __('messages.service_data');

        return response()->json(['message' => $message, 'status' => true]);
    }

    public function assign_doctor_list(Request $request)
    {

        if(auth()->user()->hasRole('doctor')){
            $query = DoctorServiceMapping::with('doctors')->where('doctor_id',auth()->user()->id)->where('service_id',$request->service_id)->where('clinic_id', $request->clinic_id);
        }
        else{
            $query = DoctorServiceMapping::with('doctors')->where('service_id',$request->service_id)->where('clinic_id', $request->clinic_id);
        }

         $query_data = $query->get();
         $data=[];

         if($query_data){

            foreach ($query_data as $row) {
         
                $data[] =[
                    'service_mapping_id' => $row->id ,
                    'doctor_id' => $row->doctors->doctor_id,
                    'doctor_name' =>  $row->doctors->user ? $row->doctors->user->first_name . ' ' . $row->doctors->user->last_name : null,
                    'avatar' => $row->doctors->user ? $row->doctors->user->profile_image : null,
                    'charges' => $row->charges 
                  ];
            }

         }

       
        
        // $doctorIds = DoctorServiceMapping::where('service_id', $request->service_id)->pluck('doctor_id');
        // $doctorServiceMapping = DoctorServiceMapping::whereIn('doctor_id', $doctorIds)->get();

        // $doctors = $query->whereIn('doctor_id', $doctorIds)->get();
        // $doctor_service = $doctors->map(function ($doctor) use ($doctorServiceMapping) {
        //     $user = $doctor->user;
        //     $mapping = $doctorServiceMapping->where('doctor_id', $doctor->doctor_id)->first();

        //     return [
        //         'id' => $mapping ? $mapping->id : null,
        //         'doctor_id' => $doctor->doctor_id,
        //         'doctor_name' => $user ? $user->first_name . ' ' . $user->last_name : null,
        //         'avatar' => $user ? $user->profile_image : null,
        //         'charges' => $mapping ? $mapping->charges : null,
        //     ];
        // });

        return response()->json(['status' => true, 'data' => $data]);
    }
    public function assign_doctor_update($id, Request $request)
    {
        DoctorServiceMapping::where('service_id',$id)->where('clinic_id',$request->clinic_id)->forceDelete();

        foreach ($request->doctors as $key => $doctor) {
            $service_mapping = [
                'service_id' => $id,
                'clinic_id' => $request->clinic_id,
                'doctor_id' => $doctor['doctor_id'],
                'charges' => $doctor['charges'] ?? 0,
            ];
        
            DoctorServiceMapping::updateOrCreate(
                [
                    'service_id' => $id,
                    'clinic_id' => $request->clinic_id,
                    'doctor_id' => $doctor['doctor_id']
                ],
                $service_mapping
            );
        }

        return response()->json(['status' => true, 'message' => __('clinic.doctor_service_update')]);
    }

    public function ServiceDetails(Request $request){
        
        $serviceDetails=[];


        if($request->filled('service_id') && $request->service_id !=null && $request->filled('encounter_id') && $request->encounter_id !=null ){

            $encounterDetails=PatientEncounter::with('appointment')->where('id',$request->encounter_id)->first();

            $doctor_id=$encounterDetails->doctor_id;
            $clinic_id=$encounterDetails->clinic_id;



            $serviceDetails = ClinicsService::where('id', $request->service_id)
            ->with(['doctor_service' => function ($query) use ($doctor_id, $clinic_id) {
                $query->where('doctor_id', $doctor_id)
                      ->where('clinic_id', $clinic_id);
            }])->first(); 
         
            $servicePricedata = [];
            if($encounterDetails->appointment == null){
                $servicePricedata = $this->getServiceAmount($request->service_id,$doctor_id,$clinic_id);

                $serviceDetails['tax_data'] = $servicePricedata['service_amount'] > 0 ? $this->calculateTaxdata($servicePricedata['service_amount']) : null; 
            }
            else{
                $taxes = optional(optional($encounterDetails->appointment)->appointmenttransaction)->tax_percentage;
                $serviceTax = 0;
                $gstPercentage = 0;
                if (is_string($taxes)) {
                    $taxes = json_decode($taxes, true);
                }
                if (is_array($taxes)) {                
                    foreach ($taxes as $tax) {
                        if ($tax['type'] === 'fixed') {
                            $serviceTax = $tax['value'];
                        } elseif ($tax['type'] === 'percent') {
                            $gstPercentage = $tax['value'];
                        }
                    }
                }else{
                    echo "No tax information available or invalid format.\n";
                }
                $gstAmount = optional($encounterDetails->appointment)->service_amount * ($gstPercentage / 100);
                $totalTax = $serviceTax + $gstAmount;
                $servicePricedata = [
                    'service_price' => optional($encounterDetails->appointment)->service_price,
                    'service_amount' => optional($encounterDetails->appointment)->service_amount,
                    'total_amount' => optional($encounterDetails->appointment)->total_amount,
                    'duration' => optional($encounterDetails->appointment)->duration ?? 0,
                    'total_tax' => $totalTax ?? 0,
                    'discount_type' => optional(optional($encounterDetails->appointment)->appointmenttransaction)->discount_type,
                    'discount_value' => optional(optional($encounterDetails->appointment)->appointmenttransaction)->discount_value,
                    'discount_amount' => optional(optional($encounterDetails->appointment)->appointmenttransaction)->discount_amount
                ];

                $service_amount = optional($encounterDetails->appointment)->service_amount;
                $tax_data = [];
                $taxes = json_decode(optional(optional($encounterDetails->appointment)->appointmenttransaction)->tax_percentage);
                foreach($taxes as $tax){
                    $amount = 0;
                    if ($tax->type == 'percent') {
                        $amount = ($tax->value / 100) * $service_amount;
                    }else {
                        $amount = $tax->value??0;
                    }
                    $tax_data[] = [
                        'title' => $tax->title,
                        'value' => $tax->value,
                        'type' => $tax->type,
                        'amount' => (float)number_format($amount, 2), 
                    ];
                }
                $serviceDetails['tax_data'] = $tax_data;
            }
            

            $serviceDetails['service_price_data']=$servicePricedata;
        
        }

        return response()->json(['status' => true, 'data' => $serviceDetails]);

    }
}
