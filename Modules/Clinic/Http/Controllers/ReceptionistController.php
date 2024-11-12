<?php

namespace Modules\Clinic\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;
use Modules\CustomField\Models\CustomField;
use Modules\CustomField\Models\CustomFieldGroup;
use Carbon\Carbon;
use Hash;
use Modules\Clinic\Models\Receptionist;

class ReceptionistController extends Controller
{
    protected string $exportClass = '\App\Exports\ReceptionistExport';
    public function __construct()
    {
        // Page Title
        $this->module_title = 'Receptionist';

        // module name
        $this->module_name = 'receptionist';

        // directory path of the module
        $this->module_path = 'clinic::backend';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => 'fa-regular fa-sun',
            'module_name' => $this->module_name,
            'module_path' => $this->module_path,
        ]);
        $this->middleware(['permission:view_clinic_receptionist_list'])->only('index');
        $this->middleware(['permission:edit_clinic_receptionist_list'])->only('edit', 'update');
        $this->middleware(['permission:add_clinic_receptionist_list'])->only('store');
        $this->middleware(['permission:delete_clinic_receptionist_list'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $module_action = 'List';
        $module_title = 'clinic.receptionist';
        $columns = CustomFieldGroup::columnJsonValues(new User());
        $customefield = CustomField::exportCustomFields(new User());

        $export_import = true;
        $export_columns = [
            [
                'value' => 'Name',
                'text' => __('service.lbl_name'),
            ],
            [
                'value' => 'mobile',
                'text' => __('customer.lbl_phone_number'),
            ],
            [
                'value' => 'email',
                'text' => __('appointment.lbl_email'),
            ],
            [
                'value' => 'varification_status',
                'text' =>  __('clinic.lbl_verification_status') ,
            ],
            [
                'value' => 'Clinic Center',
                'text' => __('clinic.singular_title'),
            ],
            [
                'value' => 'status',
                'text' => __('customer.lbl_status'),
            ],
        ];
        $export_url = route('backend.receptionist.export');
        return view('clinic::backend.receptionist.index', compact('module_action','module_title', 'columns', 'customefield',  'export_import', 'export_columns','export_url'));
    }
    

    public function index_list(Request $request)
    {
        $term = trim($request->q);

        // Need To Add Role Base
        $query_data = User::role(['receptionist'])->with('media')->where(function ($q) use ($term) {
            if (!empty($term)) {
                $q->orWhere('first_name', 'LIKE', "%$term%");
                $q->orWhere('last_name', 'LIKE', "%$term%");
            }
        });

        $query_data = $query_data->where('status',1)->get();

        $data = [];

        foreach ($query_data as $row) {
            $data[] = [
                'id' => $row->id,
                'name' => $row->full_name,
                'avatar' => $row->profile_image,
                'email' => $row->email,
                'mobile' => $row->mobile,
                'created_at' => $row->created_at,
            ];
        }

        return response()->json($data);
    }


    public function index_data(Datatables $datatable, Request $request)
    {
        $module_name = $this->module_name;
        $query = User::role('receptionist')->setRoleReceptionist(auth()->user())->with('receptionist');
        $userId = auth()->id();
      
        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }
        $query->orderBy('created_at', 'desc');

        $datatable = $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->addColumn('action', function ($data) use ($module_name) {
                return view('clinic::backend.receptionist.action_column', compact('module_name', 'data'));
            })

            ->editColumn('image', function ($data) {
                return "<img src='" . $data->profile_image . "'class='avatar avatar-50 rounded-pill'>";
            })

            ->editColumn('receptionist_id', function ($data) {
                return view('clinic::backend.receptionist.user_id', compact('data'));
            })
            ->filterColumn('receptionist_id', function ($query, $keyword) {
                if (!empty($keyword)) {
                    $query->where('first_name', 'like', '%' . $keyword . '%')->orWhere('last_name', 'like', '%' . $keyword . '%')->orWhere('email', 'like', '%' . $keyword . '%');
                }
            })
            ->orderColumn('receptionist_id', function ($query, $order) {
                $query->orderByRaw("CONCAT(first_name, ' ', last_name) $order");
            }, 1)

            ->editColumn('clinic_id', function ($data) {
                // return optional(optional($data->receptionist)->clinics)->name;
                return view('clinic::backend.receptionist.clinic_id', compact('data'));
            })
            

            ->editColumn('email_verified_at', function ($data) {
                $checked = '';
                if ($data->email_verified_at) {
                    return '<span class="badge bg-success-subtle p-2"> ' . __('customer.msg_verified') . '</span>';
                }

                return '<button  type="button" data-url="' . route('backend.receptionist.verify-receptionist', $data->id) . '" data-token="' . csrf_token() . '" class="button-status-change btn btn-text-danger btn-sm  bg-danger-subtle"  id="datatable-row-' . $data->id . '"  name="is_verify" value="' . $data->id . '" ' . $checked . '>Verify</button>';
            })

          

            ->editColumn('status', function ($row) {
                $checked = '';
                if ($row->status) {
                    $checked = 'checked="checked"';
                }

                return '
                    <div class="form-check form-switch ">
                        <input type="checkbox" data-url="' . route('backend.receptionist.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $row->id . '"  name="status" value="' . $row->id . '" ' . $checked . '>
                    </div>
                ';
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

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatable, User::CUSTOM_FIELD_MODEL, null);

        return $datatable->rawColumns(array_merge(['action', 'status', 'is_banned', 'email_verified_at', 'check', 'image','clinic_id'], $customFieldColumns))
            ->toJson();
    }

    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('receptionist.bulk_update');
        switch ($actionType) {
            case 'change-status':
                $receptionist = User::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('receptionist.bulk_receptionist_update');
                break;

            case 'delete':
                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('receptionist.permission_denied'), 'status' => false], 200);
                }
                User::whereIn('id', $ids)->delete();
                $message = __('receptionist.bulk_receptionist_delete');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => __('receptionist.bulk_update')]);
    }
    public function update_status(Request $request, User $id)
    {
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('service_providers.status_update')]);
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
        $data = $request->except('profile_image');
        $data = $request->all();
        
        if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')) {
            $request->vendor_id = $request->filled('vendor_id') ? $request->vendor_id : auth()->user()->id;
            
        } else {
            $request->vendor_id = auth()->user()->id;
            
        }
        $data['email_verified_at'] = Carbon::now();
        $data['user_type'] = 'receptionist';
        $data['password'] = Hash::make($data['password']);
        $data = User::create($data);

        $roles = ['receptionist'];

        $data->syncRoles($roles);

        $receptionist = [
            'receptionist_id' => $data->id,
            'clinic_id' => $request->clinic_id,
            'vendor_id' => $request->vendor_id,
        ];
        
        Receptionist::create($receptionist);


        if ($request->custom_fields_data) {
            $data->updateCustomFieldData(json_decode($request->custom_fields_data));
        }
        if ($request->has('profile_image') && !empty($request->profile_image) ) {

            storeMediaFile($data, $request->file('profile_image'),'profile_image');
        }

        $message = __('messages.create_form', ['form' => __('receptionist.singular_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
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
        $data = User::role(['receptionist'])->where('id', $id)->with('receptionist')->first();

        if (!is_null($data)) {
            $custom_field_data = $data->withCustomFields();
            $data['custom_field_data'] = collect($custom_field_data->custom_fields_data)
                ->filter(function ($value) {
                    return $value !== null;
                })
                ->toArray();
        }

        $data['clinic_id'] = $data->receptionist->clinic_id ?? null;
        $data['vendor_id'] = $data->receptionist->vendor_id ?? null;


        return response()->json(['data' => $data, 'status' => true]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = User::role(['receptionist'])->findOrFail($id);

        $request_data = $request->except('profile_image');

        $request_data = $request->except('password');
        
        if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')) {
            $request->vendor_id = $request->filled('vendor_id') ? $request->vendor_id : auth()->user()->id;
        }

        $data->update($request_data);
        $receptionist = Receptionist::firstOrNew(['receptionist_id' => $data->id]);
        $receptionist->fill([
            'receptionist_id' => $data->id,
            'clinic_id' => $request->clinic_id,
            'vendor_id' => $request->vendor_id,
        ]);
        $receptionist->save();

        if ($request->hasFile('profile_image')) {
            storeMediaFile($data, $request->file('profile_image'),'profile_image');
        }
        
        if ($request->profile_image == null) {
            $data->clearMediaCollection('profile_image');
        }

        $message = __('messages.update_form', ['form' => __('receptionist.singular_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $data = User::role('receptionist')->findOrFail($id);
        $data->receptionist()->delete();
        $data->delete();
        $message = __('messages.delete_form', ['form' => __('receptionist.singular_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }
    public function verify_receptionist(Request $request, $id)
    {
        $data = User::role(['receptionist'])->findOrFail($id);

        $current_time = Carbon::now();

        $data->update(['email_verified_at' => $current_time]);

        return response()->json(['status' => true, 'message' => __('receptionist.receptionist_verify')]);
    }
    public function change_password(Request $request)
    {

        $data = $request->all();

        $receptionist_id = $data['receptionist_id'];

        $data = User::role(['receptionist'])->findOrFail($receptionist_id);

        $request_data = $request->only('password');
        $request_data['password'] = Hash::make($request_data['password']);

        $data->update($request_data);

        $message = __('receptionist.password_update');

        return response()->json(['message' => $message, 'status' => true], 200);
    }
}
