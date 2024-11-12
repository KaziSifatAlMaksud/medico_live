<?php

namespace Modules\MultiVendor\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\CustomField\Models\CustomField;
use Modules\CustomField\Models\CustomFieldGroup;
use Yajra\DataTables\DataTables;
use App\Models\User;
use Modules\MultiVendor\Http\Requests\MultivendorRequest;

class MultiVendorsController extends Controller
{
    // use Authorizable;
    protected string $exportClass = '\App\Exports\VendorExport';
    public function __construct()
    {
        // Page Title
        $this->module_title = 'clinic.clinic_admin';
        // module name
        $this->module_name = 'multivendors';

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
     *
     * @return Response
     */

    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];

        $module_action = 'List';
        $columns = CustomFieldGroup::columnJsonValues(new User());
        $customefield = CustomField::exportCustomFields(new User());

        $export_import = true;
        $export_columns = [
            [
                'value' => 'Name',
                'text' => __('multivendor.title') ,
            ],
            [
                'value' => 'mobile',
                'text' => __('clinic.lbl_phone_number'),
            ],
            [
                'value' => 'email',
                'text' => __('appointment.lbl_email'),
            ],
            [
                'value' => 'varification_status',
                'text' =>  __('multivendor.lbl_verification_status'),
            ],
            [
                'value' => 'status',
                'text' => __('multivendor.lbl_status'),
            ],
        ];
        $export_url = route('backend.multivendors.export');

        return view('multivendor::backend.multivendors.index_datatable', compact('module_action', 'filter', 'columns',  'export_import', 'export_columns', 'export_url', 'customefield'));
    }

    /**
     * Select Options for Select 2 Request/ Response.
     *
     * @return Response
     */
    public function index_list(Request $request)
    {
        $term = trim($request->q);

        $query_data = User::role(['vendor'])->with('media')->where(function ($q) use ($term) {
            if (!empty($term)) {
                $q->orWhere('first_name', 'LIKE', "%$term%");
                $q->orWhere('last_name', 'LIKE', "%$term%");
            }
        });
       

        $query_data = $query_data->get();

        $data = [];

        foreach ($query_data as $row) {
            $data[] = [
                'id' => $row->id,
                'name' => $row->first_name.' '.$row->last_name,
                 'avatar' => $row->profile_image,
            ];
        }
        return response()->json($data);
    }

    public function index_data(Request $request)
    {
        $query = User::role('vendor');
        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }
        $query->orderBy('created_at', 'desc');

        return Datatables::of($query)
                        ->addColumn('check', function ($data) {
                            return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$data->id.'"  name="datatable_ids[]" value="'.$data->id.'" onclick="dataTableRowCheck('.$data->id.')">';
                        })
                        ->addColumn('action', function ($data) {
                            return view('multivendor::backend.multivendors.action_column', compact('data'));
                        })
                        ->editColumn('vendor_id', function ($data) {
                            return view('multivendor::backend.multivendors.user_id', compact('data'));
                        })
                        ->filterColumn('vendor_id', function ($query, $keyword) {
                            if (!empty($keyword)) {
                                $query->where('first_name', 'like', '%' . $keyword . '%')->orWhere('last_name', 'like', '%' . $keyword . '%')->orWhere('email', 'like', '%' . $keyword . '%');
                            }
                        })
                        ->orderColumn('vendor_id', function ($query, $order) {
                            $query->orderByRaw("CONCAT(first_name, ' ', last_name) $order");
                        }, 1)

                        ->editColumn('email_verified_at', function ($data) {
                            $checked = '';
                            if ($data->email_verified_at) {
                                return '<span class="badge bg-success-subtle"><i class="fa-solid fa-envelope" style="margin-right: 2px"></i> ' . __('customer.msg_verified') . '</span>';
                            }

                            return '<button  type="button" data-url="' . route('backend.multivendors.verify-vendor', $data->id) . '" data-token="' . csrf_token() . '" class="button-status-change btn btn-text-danger btn-sm  bg-danger-subtle"  id="datatable-row-' . $data->id . '"  name="is_verify" value="' . $data->id . '" ' . $checked . '>Verify</button>';
                        })

                        ->editColumn('is_banned', function ($data) {
                            $checked = '';
                            if ($data->is_banned) {
                                $checked = 'checked="checked"';
                            }

                            return '
                                <div class="form-check form-switch ">
                                    <input type="checkbox" data-url="' . route('backend.multivendors.block-vendor', $data->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $data->id . '"  name="is_banned" value="' . $data->id . '" ' . $checked . '>
                                </div>
                             ';
                        })

                        ->editColumn('status', function ($row) {
                            $checked = '';
                            if ($row->status) {
                                $checked = 'checked="checked"';
                            }

                            return '
                                <div class="form-check form-switch ">
                                    <input type="checkbox" data-url="' . route('backend.multivendors.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $row->id . '"  name="status" value="' . $row->id . '" ' . $checked . '>
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
                        ->rawColumns(['action', 'status', 'check','vendor_id','email_verified_at','is_banned'])
                        ->orderColumns(['id'], '-:column $1')
                        ->make(true);
    }
    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                $vendor = User::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('multivendor.vendor_status');
                break;

            case 'delete':
                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                User::whereIn('id', $ids)->delete();
                $message = __('multivendor.vendor_delete');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => $message]);
    }
    public function update_status(Request $request, User $id)
    {
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' =>  __('clinic.clinic_status')]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(MultivendorRequest $request)
    {
        $data = $request->except('profile_image');
        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = Carbon::now();

        $data['user_type'] = 'vendor';

        $data = User::create($data);
        $data->syncRoles(['vendor']);

        
        \Artisan::call('cache:clear');

        if ($request->custom_fields_data) {
            $data->updateCustomFieldData(json_decode($request->custom_fields_data));
        }
        if ($request->has('profile_image') && !empty($request->profile_image)) {
            storeMediaFile($data, $request->file('profile_image'), 'profile_image');
        }
        $message = __('multivendor.new_vendor');

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {

        $data = User::role(['vendor'])->where('id',$id)->first();

        return response()->json(['data' => $data, 'status' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $data = User::role(['vendor'])->findOrFail($id);

        $request_data = $request->except('profile_image');

        $request_data = $request->except('password');

        $data->update($request_data);
        
        if ($request->hasFile('profile_image')) {
            storeMediaFile($data, $request->file('profile_image'),'profile_image');
        }
        
        if ($request->profile_image == null) {
            $data->clearMediaCollection('profile_image');
        }

       
        $message = __('multivendor.update_vendor');

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $data = User::role('vendor')->findOrFail($id);
        $data->delete();

        $message = __('multivendor.delete_vendor');

        return response()->json(['message' => $message, 'status' => true], 200);
    }
    public function block_vendor(Request $request, User $id)
    {
        $id->update(['is_banned' => $request->status]);

        if ($request->status == 1) {
            $message = __('multivendor.google_blocked');
        } else {
            $message = __('multivendor.google_unblocked');
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    public function verify_vendor(Request $request, $id)
    {
        $data = User::findOrFail($id);

        $current_time = Carbon::now();

        $data->update(['email_verified_at' => $current_time]);

        return response()->json(['status' => true, 'message' => __('multivendor.vendor_verify')]);
    }
    public function change_password(Request $request)
    {
        $data = $request->all();

        $user_id = $data['user_id'];

        $data = User::findOrFail($user_id);

        $request_data = $request->only('password');
        $request_data['password'] = Hash::make($request_data['password']);

        $data->update($request_data);

        $message = __('messages.password_update');

        return response()->json(['message' => $message, 'status' => true], 200);
    }
    public function view(){
        return view('multivendor::backend.multivendors.view');
    }
}
