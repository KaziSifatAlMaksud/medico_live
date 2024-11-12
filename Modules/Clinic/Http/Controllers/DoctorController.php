<?php

namespace Modules\Clinic\Http\Controllers;
use App\Authorizable;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\CustomField\Models\CustomField;
use Modules\CustomField\Models\CustomFieldGroup;
use App\Models\User;
use Yajra\DataTables\DataTables;
use App\Models\Setting;
use Carbon\Carbon;
use Modules\Clinic\Models\Doctor;
use Modules\Commission\Models\EmployeeCommission;
use Modules\Clinic\Models\DoctorDocument;
use Hash;
use Modules\Clinic\Models\DoctorServiceMapping;
use Modules\Clinic\Models\Clinics;
use Modules\Clinic\Models\DoctorClinicMapping;
use Modules\Clinic\Models\ClinicsService;
use Modules\Clinic\Models\DoctorSession;
use Modules\Clinic\Http\Requests\DoctorRequest;
use Modules\Appointment\Models\Appointment;
use Modules\Clinic\Models\Receptionist;
use Modules\Clinic\Models\DoctorRating;
use Modules\Clinic\Models\ClinicServiceMapping;
use Illuminate\Database\Query\Expression;
use App\Models\Holiday;
use  App\Models\DoctorHoliday;

class DoctorController extends Controller
{
    protected string $exportClass = '\App\Exports\DoctorExport';

    public function __construct()
    {
        // Page Title
        $this->module_title = 'Doctor Detail';

        // module name
        $this->module_name = 'doctor';

        // directory path of the module
        $this->module_path = 'clinic::backend';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => 'fa-regular fa-sun',
            'module_name' => $this->module_name,
            'module_path' => $this->module_path,
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $module_action = 'List';
        $columns = CustomFieldGroup::columnJsonValues(new User());
        $customefield = CustomField::exportCustomFields(new User());
        $filter = [
            'status' => $request->status,
        ];
        $user = User::role('doctor')->SetRole(auth()->user())->with('doctor', 'doctorclinic')->get();
        $clinic = Clinics::SetRole(auth()->user())->with('clinicdoctor','specialty','clinicdoctor','receptionist')->get();
        $vendor =User::where('user_type','vendor')->get();

        $module_title = 'clinic.doctor_list';
        $create_title = 'clinic.doctor_title';

        $export_import = true;
        $export_columns = [
            [
                'value' => 'Name',
                'text' => __('service.lbl_name'),
            ],
            [
                'value' => 'mobile',
                'text' =>  __('clinic.lbl_phone_number'),
            ],
            [
                'value' => 'email',
                'text' => __('appointment.lbl_email'),
            ],
            [
                'value' => 'Clinic Center',
                'text' => __('clinic.lbl_clinic_center'),
            ],

            [
                'value' => 'varification_status',
                'text' => __('clinic.lbl_verification_status'),
            ],
            [
                'value' => 'status',
                'text' => __('clinic.lbl_status'),
            ],
        ];
        $export_url = route('backend.doctor.export');

        return view('clinic::backend.doctor.index', compact('filter','vendor','module_action', 'module_title','create_title','columns', 'customefield', 'export_import', 'export_columns','clinic', 'export_url'));

    }
    public function index_list(Request $request)
    {
        $term = trim($request->q);

        $query = Doctor::SetRole(auth()->user())->with('user', 'doctorclinic')->where('status',1);

        if($request->has('clinic_id') && $request->clinic_id != '') {
            $clinicId = $request->clinic_id;
            $query = $query->whereHas('doctorclinic', function ($data) use ($clinicId) {
                $data->where('clinic_id', $clinicId);
            });

        }

        $query_data = $query->where('status',1)->get();
        $data = [];

        foreach ($query_data as $row) {
            $data[] = [
                'id' => $row->id,
                'doctor_name' => optional($row->user)->full_name,
                'doctor_id' => $row->doctor_id,
                'avatar' => optional($row->user)->profile_image,
            ];
        }

        return response()->json($data);
    }
    public function service_list(Request $request)
    {

        $category_id = $request->category_id;
        $data = ClinicsService::query()->with('ClinicServiceMapping', 'doctor_service');


        if (isset($category_id)) {
            $data->where('category_id', $category_id);
        }


        if ($request->has('clinic_id') && !empty($request->clinic_id)) {

            $clinicId = explode(",", $request->clinic_id);

            $data = $data->whereHas('ClinicServiceMapping', function ($query) use ($clinicId) {
                $query->whereIn('clinic_id', $clinicId);
            });
        }

        $data = $data->get();


        return response()->json($data);
    }
    public function employee_list(Request $request)
    {
        $term = trim($request->q);

        $branchId = $request->branch_id;

        $role = $request->role;

        // Need To Add Role Base
        $query_data = User::role(['doctor'])->with('media')->where(function ($q) use ($term) {
            if (!empty($term)) {
                $q->orWhere('first_name', 'LIKE', "%$term%");
                $q->orWhere('last_name', 'LIKE', "%$term%");
            }
        });


        if ($request->show_in_calender) {
            $query_data->CalenderResource();
        }

        if (!empty($role)) {
            $query_data->role($role);
        }

        $query_data = $query_data->get();

        $data = [];

        foreach ($query_data as $row) {
            $data[] = [
                'id' => $row->id,
                'name' => $row->full_name,
                'avatar' => $row->profile_image,
            ];
        }

        return response()->json($data);
    }

    public function availableSlot(Request $request)
    {

        $availableSlot = [];

        if($request->filled(['appointment_date', 'clinic_id', 'doctor_id', 'service_id'])) {

            $timezone = new \DateTimeZone(setting('default_time_zone') ?? 'UTC');

            $time_slot_duration = 10;
            $timeslot = ClinicsService::where('id', $request->service_id)->value('time_slot');

            if($timeslot) {
                $time_slot_duration = ($timeslot === 'clinic_slot') ?
                    (int) Clinics::where('id', $request->clinic_id)->value('time_slot') :
                    (int) $timeslot;
             }

            $currentDate = Carbon::today($timezone);
            $carbonDate = Carbon::parse($request->appointment_date, $timezone);

            $dayOfWeek = $carbonDate->dayName;
            $availableSlot = [];

            $doctorSession = DoctorSession::where('clinic_id', $request->clinic_id)->where('doctor_id', $request->doctor_id)->where('day', $dayOfWeek)->first();

            if($doctorSession && !$doctorSession->is_holiday) {

                $startTime = Carbon::parse($doctorSession->start_time, $timezone);
                $endTime = Carbon::parse($doctorSession->end_time, $timezone);

                $breaks = $doctorSession->breaks;

                $timeSlots = [];

                $current = $startTime->copy();
                while ($current < $endTime) {

                    $inBreak = false;
                    foreach ($breaks as $break) {
                        $breakStartTime = Carbon::parse($break['start_break'],$timezone);
                        $breakEndTime = Carbon::parse($break['end_break'],$timezone);
                        if ($current >= $breakStartTime && $current < $breakEndTime) {
                            $inBreak = true;
                            break;
                        }
                    }

                    if (!$inBreak) {
                        $timeSlots[] = $current->format('H:i');
                    }

                    $current->addMinutes($time_slot_duration);
                }

                $availableSlot = $timeSlots;

                if ($carbonDate == $currentDate) {      
                    $todaytimeSlots = [];
                    $currentDateTime = Carbon::now($timezone);
                    foreach ($timeSlots as $slot) {
                        $slotTime = Carbon::parse($slot, $timezone);
            
                        if ($slotTime->greaterThan(Carbon::parse($currentDateTime,$timezone))) {

                            $todaytimeSlots[] = $slotTime->format('H:i');
                        }
                    
                    }
                    $availableSlot = $todaytimeSlots;
                }

            
                $clinic_holiday = Holiday::where('clinic_id', $request->clinic_id)
                ->where('date', $request->appointment_date)
                ->first();


                if ($clinic_holiday) {
                    $holidayStartTime =  Carbon::parse($clinic_holiday->start_time, $timezone);
                    $holidayEndTime = Carbon::parse($clinic_holiday->end_time,$timezone);
    
                    $availableSlot = array_filter($availableSlot, function ($slot) use ($holidayStartTime, $holidayEndTime, $timezone) {
                        $slotTime = Carbon::parse($slot, $timezone);
                        return !($slotTime->between($holidayStartTime, $holidayEndTime));
                    });
    
                    $availableSlot = array_values($availableSlot);
                }

                $doctor_holiday = DoctorHoliday::where('doctor_id', $request->doctor_id)
                ->where('date', $request->appointment_date)
                ->first();

                if($doctor_holiday) {
                    $holidayStartTime = Carbon::parse($doctor_holiday->start_time, $timezone);
                    $holidayEndTime = Carbon::parse($doctor_holiday->end_time, $timezone);

                    $availableSlot = array_filter($availableSlot, function ($slot) use ($holidayStartTime, $holidayEndTime, $timezone) {
                        $slotTime = Carbon::parse($slot, $timezone);
                        return !($slotTime->between($holidayStartTime, $holidayEndTime));
                    });
    
                    $availableSlot = array_values($availableSlot);
                }
    

                $appointmentData = Appointment::where('appointment_date', $request->appointment_date)->where('doctor_id',$request->doctor_id)->where('status','!=','cancelled')->get();
            

                $bookedSlots = [];

                foreach ($appointmentData as $appointment) {

                    $startTime = Carbon::parse($appointment->start_date_time)->setTimezone($timezone);
                    $startTime = strtotime( $startTime);
                    $duration = $appointment->duration;

                    $endTime = $startTime + ($duration * 60);
                     
                    $startTime=$startTime-($duration * 60);

                    while ($startTime < $endTime) {
                        $bookedSlots[] = date('H:i', $startTime);
                        $startTime += 300;
                    }
                }
                $availableSlotTime = array_diff($availableSlot, $bookedSlots);
                $availableSlot = array_values($availableSlotTime);

            }
    
        }

        $message='messages.avaibleslot';

        $data = [
            'availableSlot' => $availableSlot
        ];


        if ($request->is('api/*')) {
            
            return response()->json(['message' => $message, 'data' => $availableSlot, 'status' => true], 200);

        } else {

            return response()->json($data);
        }

      
    }



    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);
        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                // Need To Add Role Base
                $employee = User::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('clinic.doctor_update');
                break;

            case 'delete':

                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                User::whereIn('id', $ids)->delete();
                $message = __('clinic.doctor_delete');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('clinic.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    public function index_data(Datatables $datatable, Request $request)
    {

        $module_name = $this->module_name;
        $userId = auth()->id();
        $query = User::role('doctor')->SetRole(auth()->user())->with('doctor', 'doctorclinic');

     
        $filter = $request->filter;
   
        if (isset($filter)) {

            if (isset($filter['clinic_name'])) {

                $query->whereHas('doctor', function ($query) use ($filter) {
                    $query->whereHas('doctorclinic', function ($query) use ($filter) {
                        $query->whereHas('clinics', function ($query) use ($filter) {
                            $query->where('id', $filter['clinic_name']);
                        });
                    });
                });
            }
            if(isset($filter['doctor_name'])) {
                $fullName = $filter['doctor_name'];
                
                $query->where(function($query) use ($fullName) {
                    $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$fullName%"]);
                });
            }
            if(isset($filter['email'])) {
                
                $query->where('email',$filter['email']);
            }
            if(isset($filter['contact'])) {
                
                $query->where('mobile',$filter['contact']);
            }
            if(isset($filter['gender'])) {
                
                $query->where('gender',$filter['gender']);
            }
            if(isset($filter['vendor_id'])) {
                
                $query->whereHas('doctor', function ($query) use ($filter) {
                            $query->where('vendor_id', $filter['vendor_id']);
                });
            }
        }

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
            ->addColumn('action', function ($data) {

                $other_settings = Setting::where('name', 'is_provider_push_notification')->first();

                $enable_push_notification = 0;

                if (!empty($other_settings)) {

                    $enable_push_notification = $other_settings->val;
                }
                return view('clinic::backend.doctor.action_column', compact('data', 'enable_push_notification'));
            })
            // ->addColumn('doctor_session', function ($data) {
            //     return " <button type='button' class='btn text-success p-0 fs-5' data-assign-module='" . $data->id . "' data-assign-target='#session-form-offcanvas' data-assign-event='employee_assign' class='fs-6 text-info border-0 bg-transparent text-nowrap' data-bs-toggle='tooltip' title='Session'>  <i class='ph ph-paper-plane-tilt'></i></button>";
            // })
            ->editColumn('doctor_id', function ($data) {
                return view('clinic::backend.doctor.user_id', compact('data'));
            })

            ->editColumn('clinic_id', function ($data) {
              
                return "<span class='bg-primary-subtle rounded tbl-badge'> <button type='button' data-assign-module='" . $data->id . "' data-assign-target='#clinic-list' data-assign-event='clinic_list' class='btn btn-sm p-0 text-primary' data-bs-toggle='tooltip' title='Clinic List'><b>$data->doctorclinic_count </b> </button></span>";
            })
            ->orderColumn('clinic_id', function ($query, $order) {
                $query->whereHas('doctor', function ($query) use ($order){
                    $query->whereHas('doctorclinic', function ($query) use ($order) {
                        $query->whereHas('clinics', function ($query) use ($order) {
                            $query->orderBy('name', $order);
                        });
                    });
                });
            }, 1)
            ->filterColumn('doctor_id', function ($query, $keyword) {
                if (!empty($keyword)) {
                    $query->where('first_name', 'like', '%' . $keyword . '%')->orWhere('last_name', 'like', '%' . $keyword . '%')->orWhere('email', 'like', '%' . $keyword . '%');
                }
            })
            ->orderColumn('doctor_id', function ($query, $order) {
                $query->orderByRaw("CONCAT(first_name, ' ', last_name) $order");
            }, 1)
            ->editColumn('image', function ($data) {
                return "<img src='" . $data->profile_image . "'class='avatar avatar-50 rounded-pill'>";
            })

            ->editColumn('email_verified_at', function ($data) {

                return view('clinic::backend.doctor.verify_action', compact('data'));
            })
            ->editColumn('user_type', function ($data) {
                return '<span class="badge booking-status bg-primary-subtle p-3">' . str_replace("_", "", ucfirst($data->user_type)) . '</span>';
            })
            ->editColumn('full_name', function ($data) {
                return $data->first_name . ' ' . $data->last_name;
            })
            ->filterColumn('full_name', function ($query, $keyword) {
                if (!empty($keyword)) {
                    $query->where('first_name', 'like', '%' . $keyword . '%')->orWhere('last_name', 'like', '%' . $keyword . '%');
                }
            })
            ->orderColumn('full_name', function ($query, $order) {
                $query->orderByRaw("CONCAT(first_name, ' ', last_name) $order");
            }, 1)

            ->editColumn('status', function ($data) {
                $checked = '';
                if ($data->status) {
                    $checked = 'checked="checked"';
                }

                return '
                    <div class="form-check form-switch ">
                        <input type="checkbox" data-url="' . route('backend.doctor.update_status', $data->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $data->id . '"  name="status" value="' . $data->id . '" ' . $checked . '>
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

        return $datatable->rawColumns(array_merge(['action','clinic_id', 'status', 'is_banned', 'email_verified_at', 'check', 'image', 'user_type'], $customFieldColumns))
            ->toJson();
    }
    public function update_status(Request $request, User $id)
    {
        $id->update(['status' => $request->status]);
        Doctor::where('doctor_id', $id->id)->update(['status' => $request->status]);
        return response()->json(['status' => true, 'message' => __('clinic.doctor_update')]);
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
    public function store(DoctorRequest $request)
    {
        $data = $request->except('profile_image');
        $data = $request->all();
        $clinicid = $request->clinic_id;
        if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')) {
            $request->vendor_id = $request->filled('vendor_id') ? $request->vendor_id : auth()->user()->id;
            
        }elseif(auth()->user()->hasRole('receptionist')){
            $vendor_id = Receptionist::where('receptionist_id', auth()->user()->id)
            ->whereHas('clinics', function ($query) use ($clinicid) {
                $query->where('clinic_id', $clinicid);
            })
            ->pluck('vendor_id')
            ->first();
            $request->vendor_id = $vendor_id;
        } else {
            $request->vendor_id = auth()->user()->id;
            
        }
        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = Carbon::now();
        $data['user_type'] = 'doctor';

        $data = User::create($data);

        $profile = [
            'about_self' => $request->about_self,
            'expert' => $request->expert,
            'facebook_link' => $request->facebook_link,
            'instagram_link' => $request->instagram_link,
            'twitter_link' => $request->twitter_link,
            'dribbble_link' => $request->dribbble_link,
        ];

        $data->profile()->updateOrCreate([], $profile);

        if ($request->custom_fields_data) {

            $data->updateCustomFieldData(json_decode($request->custom_fields_data));
        }

        if ($request->has('profile_image') && !empty($request->profile_image) ) {

            storeMediaFile($data, $request->file('profile_image'),'profile_image');
        }

        $employee_id = $data['id'];

        $roles = ['doctor'];

        $data->syncRoles($roles);

        $doctor_data = [
            'doctor_id' => $data->id,
            'clinic_id' => $request->clinic_id,
            'experience' => $request->experience,
            'vendor_id' => $request->vendor_id,
            'signature' => $request->signature,
        ];
        Doctor::create($doctor_data);

        if ($request->has('qualifications') && $request->qualifications !== '[{"degree":"","university":"","year":""}]') {
            $qualifications = json_decode($request->qualifications);
            foreach ($qualifications as $qualification) {
                if (!empty($qualification->degree) && !empty($qualification->university) && !empty($qualification->year)) {
                    $qualification_data = [
                        'doctor_id' => $data->id,
                        'degree' => $qualification->degree,
                        'university' => $qualification->university,
                        'year' => $qualification->year,
                    ];
                    DoctorDocument::create($qualification_data);
                }
            }
        }


        if ($request->has('clinic_id') && !empty($request->clinic_id)) {


            $days = [
                ['day' => 'monday', 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_holiday' => false, 'breaks' => []],
                ['day' => 'tuesday', 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_holiday' => false, 'breaks' => []],
                ['day' => 'wednesday', 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_holiday' => false, 'breaks' => []],
                ['day' => 'thursday', 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_holiday' => false, 'breaks' => []],
                ['day' => 'friday', 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_holiday' => false, 'breaks' => []],
                ['day' => 'saturday', 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_holiday' => false, 'breaks' => []],
                ['day' => 'sunday', 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_holiday' => true, 'breaks' => []],
            ];

            $clinicId = explode(",", $request->clinic_id);

            foreach ($clinicId as $value) {

                $doctor_clinic = [
                    'doctor_id' => $data->id,
                    'clinic_id' => $value,
                ];

                DoctorClinicMapping::create($doctor_clinic);

                foreach ($days as $key => $val) {

                    $val['clinic_id'] = $value;
                    $val['doctor_id'] =  $data->id;

                    DoctorSession::create($val);
                }
            }
        }

        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('config:cache');


        if ($request->has('service_id') &&   $request->has('clinic_id') ) {

            if ($request->service_id !== null &&  $request->clinic_id !==null) {

                $services = explode(',', $request->service_id);
                $clinices = explode(",", $request->clinic_id);

                foreach( $clinices as $clinic){
                
                    foreach($services as $value) {

                        $clinic_service=ClinicServiceMapping::where('service_id',$value)->where('clinic_id',$clinic)->first();

                        if($clinic_service){

                            $clinicService = ClinicsService::findOrFail($value);
                            $charges = $clinicService->charges;
                            $service_data = [
                                 'doctor_id' => $data->id,
                                 'service_id' => $value,
                                 'charges' => $charges,
                                 'clinic_id' => $clinic,
                            ];

                           DoctorServiceMapping::create($service_data);

                        }   
                    }

                }
              
            }
        }
        if (isset($request->commission_id) && $request->has('commission_id')) {
            if ($request->commission_id !== null) {

                $commissions = explode(',', $request->commission_id);

                foreach ($commissions as $value) {
                    $commission_data = [
                        'employee_id' => $data->id,
                        'commission_id' => $value,
                    ];

                    EmployeeCommission::create($commission_data);
                }
            }
        }

        $message = __('messages.create_form', ['form' => __('clinic.doctor_title')]);

        return response()->json(['message' => $message, 'data' => $data, 'status' => true], 200);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $module_action = 'Show';

        $data = User::role('doctor')->findOrFail($id);

        return view('clinic::backend.doctor.show', compact('module_action', "$data"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data = User::role(['doctor'])->where('id', $id)->with('doctor', 'doctorclinic', 'doctor_service','commissionData', 'profile', 'doctor_document')->first();

        if (!is_null($data)) {
            $custom_field_data = $data->withCustomFields();
            $data['custom_field_data'] = collect($custom_field_data->custom_fields_data)
                ->filter(function ($value) {
                    return $value !== null;
                })
                ->toArray();
        }

        $data['clinic_id'] = optional($data->doctorclinic)->pluck('clinic_id') ?? [];

        $data['service_id'] = optional($data->doctor_service)->pluck('service_id') ?? [];

        $data['commission_id'] = optional($data->commissionData)->pluck('commission_id') ?? [];

        $data['profile_image'] = $data->profile_image;

        $data['about_self'] = optional($data->profile)->about_self ?? null;

        $data['expert'] = optional($data->profile)->expert ?? null;

        $data['facebook_link'] = optional($data->profile)->facebook_link ?? null;

        $data['instagram_link'] = optional($data->profile)->instagram_link ?? null;

        $data['twitter_link'] = optional($data->profile)->twitter_link ?? null;

        $data['dribbble_link'] = optional($data->profile)->dribbble_link ?? null;
        $data['experience'] = optional($data->doctor)->experience ?? null;
        $data['signature'] = optional($data->doctor)->Signature ?? null;
        $data['vendor_id'] = optional($data->doctor)->vendor_id ?? null;
        $data['doctor_document'] = optional($data->doctor_document)->map(function ($document) {

            return [
                'degree' => $document->degree,
                'university' => $document->university,
                'year' => $document->year,
            ];
        })->toArray();

        return response()->json(['data' => $data, 'status' => true]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = User::role(['doctor'])->findOrFail($id);

        $request_data = $request->except(['profile_image', 'password', 'vendor_id']);

        $data->update($request_data);

        $profile = [
            'about_self' => $request->about_self,
            'expert' => $request->expert,
            'facebook_link' => $request->facebook_link,
            'instagram_link' => $request->instagram_link,
            'twitter_link' => $request->twitter_link,
            'dribbble_link' => $request->dribbble_link,
        ];

        $data->profile()->updateOrCreate([], $profile);

        if ($request->custom_fields_data) {

            $data->updateCustomFieldData(json_decode($request->custom_fields_data));
        }

        if ($request->hasFile('profile_image')) {
            storeMediaFile($data, $request->file('profile_image'),'profile_image');
        }


        if ($request->is('api/*')) {
            if ($request->profile_image && $request->profile_image == null) {
                $data->clearMediaCollection('profile_image');
            }
        }
        else{
            if ($request->profile_image == null) {
                $data->clearMediaCollection('profile_image');
            }
        }
    

        DoctorDocument::where('doctor_id', $id)->forceDelete();

        // DoctorClinicMapping::where('doctor_id', $id)->forceDelete();

        DoctorServiceMapping::where('doctor_id', $id)->forceDelete();

        EmployeeCommission::where('employee_id', $id)->forceDelete();

        $employee_id = $data->id;
        $doctor = Doctor::firstOrNew(['doctor_id' => $data->id]);
        $doctor->fill([
            'doctor_id' => $data->id,
            'experience' => $request->experience,
            'signature' => $request->signature,
        ]);
        $doctor->save();

        if ($request->has('qualifications') && $request->qualifications != '[{"degree":"","university":"","year":""}]') {
            $qualifications = json_decode($request->qualifications);
        
            // Check if $qualifications is an array
            if (is_array($qualifications)) {
                foreach ($qualifications as $qualification) {
                    // Ensure properties exist
                    if (!empty($qualification->degree) && !empty($qualification->university) && !empty($qualification->year)) {
                        $qualification_data = [
                            'doctor_id' => $data->id,
                            'degree' => $qualification->degree,
                            'university' => $qualification->university,
                            'year' => $qualification->year,
                        ];
                        DoctorDocument::create($qualification_data);
                    }
                }
            }
        }

        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('config:cache');


        if ($request->has('service_id') &&   $request->has('clinic_id') ) {

            if ($request->service_id !== null &&  $request->clinic_id !==null) {

                $services = explode(',', $request->service_id);
                $clinices = explode(",", $request->clinic_id);

                foreach( $clinices as $clinic){
                
                    foreach($services as $value) {

                        $clinic_service=ClinicServiceMapping::where('service_id',$value)->where('clinic_id',$clinic)->first();

                        if($clinic_service){

                            $clinicService = ClinicsService::findOrFail($value);
                            $charges = $clinicService->charges;
                            $service_data = [
                                 'doctor_id' => $data->id,
                                 'service_id' => $value,
                                 'charges' => $charges,
                                 'clinic_id' => $clinic,
                            ];

                           DoctorServiceMapping::create($service_data);

                        }   
                    }

                }
              
            }
        }


        
        $existingClinicIds = DoctorClinicMapping::where('doctor_id', $id)->pluck('clinic_id')->toArray();
        $newClinicIds = $request->has('clinic_id') && !empty($request->clinic_id) ? explode(",", $request->clinic_id) : [];
        $clinicsToRemove = array_diff($existingClinicIds, $newClinicIds);
        if (!empty($clinicsToRemove)) {
            DoctorSession::where('doctor_id', $id)->whereIn('clinic_id', $clinicsToRemove)->delete();
        }
        DoctorClinicMapping::where('doctor_id', $id)->whereIn('clinic_id', $clinicsToRemove)->forceDelete();
        foreach ($newClinicIds as $clinicId) {
            DoctorClinicMapping::updateOrCreate(
                ['doctor_id' => $id, 'clinic_id' => $clinicId],
                ['doctor_id' => $id, 'clinic_id' => $clinicId]
            );
        }

        if (isset($request->commission_id) && $request->has('commission_id')) {
            if ($request->commission_id !== null) {

                $commissions = explode(',', $request->commission_id);

                foreach ($commissions as $value) {
                    $commission_data = [
                        'employee_id' => $employee_id,
                        'commission_id' => $value,
                    ];

                    EmployeeCommission::create($commission_data);
                }
            }
        }


        $message = __('messages.update_form', ['form' => __('clinic.doctor_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (env('IS_DEMO')) {
            return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
        }

        $data = User::role('doctor')->findOrFail($id);

        $data->profile()->delete();
        $data->doctor()->delete();
        $data->doctor_service()->delete();
        $data->doctorclinic()->delete();
        $data->doctor_document()->delete();

        DoctorSession::where('doctor_id', $id)->delete();

        $data->delete();

        $message = __('messages.delete_form', ['form' => __('clinic.doctor_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }
    public function doctorDeatails(Request $request, $id){
         
        $data = User::with('doctor','profile','media','employeeAppointment','doctor_service','rating')->findOrFail($id);
        $doctor_session = DoctorSession::where('doctor_id', $data->id)->where('is_holiday',0)->get();
        $data->total_appointment = $data->employeeAppointment->count();
        $data->specialization = optional($data->profile)->expert ? optional($data->profile)->expert : '-';
        $data->total_sessions = $doctor_session->count();
        $data->experience = optional($data->doctor)->experience ? optional($data->doctor)->experience : 0;
        
        $data->doctor_service = $data->doctor_service;

        $data->rating = $data->rating;

        return response()->json(['data' => $data, 'status' => true]);
    }
    public function change_password(Request $request)
    {

        $data = $request->all();

        $doctor_id = $data['doctor_id'];

        $data = User::role(['doctor'])->findOrFail($doctor_id);

        $request_data = $request->only('password');
        $request_data['password'] = Hash::make($request_data['password']);

        $data->update($request_data);

        $message = __('messages.password_update');

        return response()->json(['message' => $message, 'status' => true], 200);
    }
    public function verify_doctor(Request $request, $id)
    {
        $data = User::role(['doctor'])->findOrFail($id);

        $current_time = Carbon::now();

        $data->update(['email_verified_at' => $current_time]);

        return response()->json(['status' => true, 'message' => __('messages.doctor_verify')]);
    }
    // public function view()
    // {
    //     return view('clinic::backend.doctor.view');
    // }

    public function review(Request $request)
    {
        $module_title = __('clinic.reviews');
        $filter = $request->filter;

        $doctor_id = null;
        if($request->has('doctor_id')){
            $doctor_id = $request->doctor_id;
        }
        return view('clinic::backend.doctor.review', compact('module_title', 'filter', 'doctor_id'));
    }

    public function review_data(Datatables $datatable, Request $request)
    {

        $query = DoctorRating::with('user', 'doctor');
        $filter = $request->filter;
        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }
        if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')) {
            $query;
        } else {
            $query->where('doctor_id', auth()->id());
        }

        if ($request->doctor_id !== null) {
            $doctor_id = $request->doctor_id;
            $query->where('doctor_id', $doctor_id);
        }

        $datatable = $datatable->eloquent($query)
            ->addColumn('check', function ($data) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $data->id . '"  name="datatable_ids[]" value="' . $data->id . '" onclick="dataTableRowCheck(' . $data->id . ')">';
            })
            ->addColumn('image', function ($data) {
                if(isset($data->user->profile_image)){
                    return '<img src=' . $data->user->profile_image . " class='avatar avatar-50 rounded-pill'>";
                }
                else{
                    return "<img src='" . default_user_avatar() . "' class='avatar avatar-50 rounded-pill'>";
                }
            })
            ->addColumn('action', function ($data) {
                return view('clinic::backend.doctor.review_action_column', compact('data'));
            })
            ->filterColumn('doctor_id', function ($query, $keyword) {
                if (!empty($keyword)) {
                    $query->whereHas('doctor', function ($q) use ($keyword) {
                        $q->where('first_name', 'like', '%' . $keyword . '%');
                        $q->orWhere('last_name', 'like', '%' . $keyword . '%');
                    });
                }
            })
            ->editColumn('doctor_id', function ($data) {
                $doctor_id = isset($data->doctor->full_name) ? $data->doctor->full_name : '-';
                if(isset($data->doctor->profile_image)){
                    return '<img src=' . $data->doctor->profile_image . " class='avatar avatar-40 rounded-pill me-2'>".' '.$doctor_id;
                }
                else{
                    return "<img src='" . default_user_avatar() . "' class='avatar avatar-40 rounded-pill me-2'>" . ' ' . $doctor_id;
                }
            })
            ->orderColumn('doctor_id', function ($query, $order) {
                $query->join('users', 'doctor_ratings.doctor_id', '=', 'users.id')
                      ->orderBy('users.first_name', $order);
            }, 1)
            ->editColumn('service_id', function ($data) {
                $service_name = isset($data->clinic_service->name) ? $data->clinic_service->name : '-';
                if(isset($data->clinic_service->file_url)){
                    return '<img src=' . $data->clinic_service->file_url . " class='avatar avatar-40 rounded-pill me-2'>".' '.$service_name;
                }
                else{
                    return "<img src='" . default_file_url() . "' class='avatar avatar-40 rounded-pill me-2'>" . ' ' . $service_name;
                }
            })
            ->filterColumn('service_id', function ($query, $keyword) {
                if (!empty($keyword)) {
                    $query->whereHas('clinic_service', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%');
                    });
                }
            })
            ->orderColumn('service_id', function ($query, $order) {
                $query->join('clinics_services', 'doctor_ratings.service_id', '=', 'clinics_services.id')
                      ->orderBy('clinics_services.name', $order);
            }, 1)
            ->editColumn('review_msg', function ($data) {
                return '<div class="text-desc">'.$data->review_msg.'</div>';
            })   
            ->editColumn('rating', function ($data) {
                return $data->rating - floor($data->rating) > 0 ? number_format($data->rating, 1) : $data->rating;
            })            

            ->filterColumn('user_id', function ($query, $keyword) {
                if (!empty($keyword)) {
                    $query->whereHas('user', function ($q) use ($keyword) {
                        $q->where('first_name', 'like', '%' . $keyword . '%');
                        $q->orWhere('last_name', 'like', '%' . $keyword . '%');
                    });
                }
            })

            ->editColumn('user_id', function ($data) {
                $user_id = isset($data->user->full_name) ? $data->user->full_name : '-';
                if(isset($data->user->profile_image)){
                    return '<img src=' . $data->user->profile_image . " class='avatar avatar-40 rounded-pill me-2'>".$user_id;
                }
                else{
                    return "<img src='" . default_user_avatar() . "' class='avatar avatar-40 rounded-pill me-2'>" . ' ' . $user_id;
                }

                // return $user_id;
            })
            ->orderColumn('user_id', function ($query, $order) {
                $query->orderBy(new Expression('(SELECT first_name FROM users WHERE id = doctor_ratings.user_id LIMIT 1)'), $order);
            }, 1)
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

        return $datatable->rawColumns(array_merge(['action', 'image', 'check','service_id', 'doctor_id', 'user_id','review_msg','updated_at']))
            ->toJson();
    }

    public function bulk_action_review(Request $request)
    {
        $ids = explode(',', $request->rowIds);
        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {

            case 'delete':

                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                DoctorRating::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_review_delete');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('branch.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => __('messages.bulk_update')]);
    }

    public function destroy_review($id)
    {

        $module_title = __('clinic.reviews');

        if (env('IS_DEMO')) {
            return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
        }

        $data = DoctorRating::findOrFail($id);

        $data->delete();

        $message = __('messages.delete_form', ['form' => __($module_title)]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }
    public function user_list(Request $request){
        
        $data = User::query();


        $data = $data->get();


        return response()->json($data);
    }
}
