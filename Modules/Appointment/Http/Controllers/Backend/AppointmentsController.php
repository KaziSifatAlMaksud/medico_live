<?php

namespace Modules\Appointment\Http\Controllers\Backend;

use App\Authorizable;
use App\Models\User;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Modules\Appointment\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\CustomField\Models\CustomField;
use Modules\CustomField\Models\CustomFieldGroup;
use Yajra\DataTables\DataTables;
use Modules\Appointment\Trait\AppointmentTrait;
use Modules\Appointment\Models\AppointmentTransaction;
use Modules\Tax\Models\Tax;
use Google\Client;
use App\Models\Setting;
use App\Mail\AppointmentConfirmation;
use Illuminate\Support\Facades\Mail;
use Google\Service\Calendar;
use Google\Client as GoogleClient;
use Modules\Clinic\Models\ClinicServiceMapping;
use Modules\Clinic\Models\Clinics;
use Modules\Clinic\Models\ClinicsService;
use Modules\Clinic\Models\Doctor;
use Modules\Commission\Models\CommissionEarning;
use Modules\Constant\Models\Constant;
use Modules\Appointment\Trait\EncounterTrait;
use Modules\Clinic\Models\SystemService;
use Modules\Appointment\Models\BillingRecord;
use Modules\Appointment\Models\PatientEncounter;
use Modules\Appointment\Trait\BillingRecordTrait;


class AppointmentsController extends Controller
{
    use AppointmentTrait;
    use EncounterTrait;
    use BillingRecordTrait;

    protected string $exportClass = '\App\Exports\CustomerExport';

    public function __construct()
    {
        // Page Title
        $this->module_title = 'appointment.title';
        // module name
        $this->module_name = 'appointment';

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
        $columns = CustomFieldGroup::columnJsonValues(new Appointment());
        $customefield = CustomField::exportCustomFields(new Appointment());

        $export_import = true;
        $export_columns = [
            [
                'value' => 'name',
                'text' => ' Name',
            ]
        ];
        $export_url = route('backend.appointment.export');

        return view('appointment::backend.appointment.index_datatable', compact('module_action', 'filter', 'columns', 'customefield', 'export_import', 'export_columns', 'export_url'));
    }

    /**
     * Select Options for Select 2 Request/ Response.
     *
     * @return Response
     */
    public function index_list(Request $request)
    {
        $term = trim($request->q);

        if (empty($term)) {
            return response()->json([]);
        }

        $query_data = Appointment::where('name', 'LIKE', "%$term%")->orWhere('slug', 'LIKE', "%$term%")->limit(7)->get();

        $data = [];

        foreach ($query_data as $row) {
            $data[] = [
                'id' => $row->id,
                'text' => $row->name . ' (Slug: ' . $row->slug . ')',
            ];
        }
        return response()->json($data);
    }

    public function index_data()
    {
        $query = Appointment::query();
        if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')) {
            $query;
        } else {
            $query->where('doctor_id', auth()->id());
        }
        $appointment_status = Constant::getAllConstant()->where('type', 'BOOKING_STATUS');
        $appointment_colors = Constant::getAllConstant()->where('type', 'BOOKING_STATUS_COLOR');
        $payment_status = config('appointment.PAYMENT_STATUS');

        $query->orderBy('created_at', 'desc');

        $doctor = User::where('user_type', 'doctor')->get();

        return Datatables::of($query)
            ->addColumn('check', function ($data) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $data->id . '"  name="datatable_ids[]" value="' . $data->id . '" onclick="dataTableRowCheck(' . $data->id . ')">';
            })
            ->addColumn('action', function ($data) {
                return view('appointment::backend.appointment.datatable.action_column', compact('data'));
            })
            ->editColumn('status', function ($data) use ($appointment_status, $appointment_colors) {
                return view('booking::backend.appointment.datatable.select_column', compact('data', 'appointment_status', 'appointment_colors'));
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
            ->rawColumns(['action', 'status', 'check'])
            ->orderColumns(['id'], '-:column $1')
            ->make(true);
        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatable, User::CUSTOM_FIELD_MODEL, null);

        return $datatable->rawColumns(array_merge(['action', 'status', 'is_banned', 'email_verified_at', 'check', 'image'], $customFieldColumns))
            ->toJson();
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $data['start_date_time'] = Carbon::createFromFormat('Y-m-d H:i', $data['appointment_date'] . ' ' . $data['appointment_time'], setting('default_time_zone'))->setTimezone('UTC');

        $serviceData = $this->getServiceAmount($data['service_id'], $data['doctor_id'], $data['clinic_id']);

        $data['service_price'] = $serviceData['service_price'];
        $data['service_amount'] = $serviceData['service_amount'];
        $data['total_amount'] = $serviceData['total_amount'];
        $data['duration'] = $serviceData['duration'];
        $data['status'] = $data['status'] ? $data['status'] : 'confirmed';
        $data = Appointment::create($data);
        $is_telemet = ClinicsService::where('id', $data['service_id'])->pluck('is_video_consultancy')->first();
        if ($is_telemet == 1) {
            $setting = Setting::where('name', 'google_meet_method')->orwhere('name', 'is_zoom')->first();
            if ($data && $setting) {
                if ($setting->name == 'google_meet_method' && $setting->val == 1) {
                    $meetLink = $this->generateMeetLink($request, $data['start_date_time'], $data['duration'], $data);
                } else {                    
                    $zoom_url = getzoomVideoUrl($data);
                    if (!empty($zoom_url) && isset($zoom_url['start_url']) && isset($zoom_url['join_url'])) {
                        $startUrl = $zoom_url['start_url'];
                        $joinUrl = $zoom_url['join_url'];

                        $data->start_video_link = $startUrl;
                        $data->join_video_link = $joinUrl;
                        $data->save();
                    }
                }
            }
        }

        if ($request->hasFile('file_url')) {
            storeMediaFile($data, $request->file('file_url'));
        }
        if ($request->is('api/*')) {

            $service_data = ClinicsService::where('id', $data['service_id'])->with('systemservice')->first();

            $clinic_data = Clinics::where('id', $data['clinic_id'])->first();

            $data['service_name'] = $service_data->name ?? '--';
            $data['clinic_name'] = $clinic_data->name ?? '--';
            $notification_data = [
                'id' => $data->id,
                'description' => $data->description,
                'appointment_duration' => $data->duration,
                'user_id' => $data->user_id,
                'user_name' => optional($data->user)->first_name ?? default_user_name(),
                'doctor_id' => $data->doctor_id,
                'doctor_name' => optional($data->doctor)->first_name,
                'appointment_date' => $data->start_date_time->format('d/m/Y'),
                'appointment_time' => $data->start_date_time->format('h:i A'),
                'appointment_services_names' => optional($data->clinicservice)->name ?? '--',
                'appointment_services_image' => optional($data->clinicservice)->file_url,
                'appointment_date_and_time' => $data->start_date_time->format('Y-m-d H:i'),
                'latitude' =>  null,
                'longitude' => null,
            ];
            $startTime = Carbon::parse($data->appointment_time);
            $endTime = $startTime->copy()->addMinutes($data->duration);
            $data['end_time'] = $endTime->format('H:i:s') ?? '-';
            $appointmentTime = new \DateTime($data->appointment_time);
            $data['appointment_time'] = $appointmentTime->format('H:i:s') ?? '-';
            $this->sendNotificationOnBookingUpdate('new_appointment', $notification_data);
            $message = 'Your Appointment has been booked successfully.';
            return response()->json(['message' => $message, 'data' => $data, 'status' => true], 200);
        } else {
            $notification_data = [
                'id' => $data->id,
                'description' => $data->description,
                'appointment_duration' => $data->duration,
                'user_id' => $data->user_id,
                'user_name' => optional($data->user)->first_name ?? default_user_name(),
                'doctor_id' => $data->doctor_id,
                'doctor_name' => optional($data->doctor)->first_name,
                'appointment_date' => $data->start_date_time->format('d/m/Y'),
                'appointment_time' => $data->start_date_time->format('h:i A'),
                'appointment_services_names' => optional($data->clinicservice)->name ?? '--',
                'appointment_services_image' => optional($data->clinicservice)->file_url,
                'appointment_date_and_time' => $data->start_date_time->format('Y-m-d H:i'),
                'latitude' =>  null,
                'longitude' => null,
            ];
            $this->sendNotificationOnBookingUpdate('new_appointment', $notification_data);

            $message = __('messages.create_form', ['form' => __('apponitment.singular_title')]);
            return response()->json(['message' => $message, 'data' => $data, 'status' => true], 200);
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $data = Appointment::findOrFail($id);
        $data['file_url'] = $data->file_url;
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
        $data = Appointment::findOrFail($id);

        $data->update($request->all());
        if ($request->file_url == null) {
            $data->clearMediaCollection('file_url');
        }

        if ($request->hasFile('file_url')) {
            storeMediaFile($data, $request->file('file_url'), 'file_url');
        }
        if ($request->is('api/*')) {
            $message = __('messages.update_form', ['form' => __('apponiment.singular_title')]);;
            return response()->json(['message' => $message, 'data' => $data, 'status' => true], 200);
        } else {
            $message = __('messages.update_form', ['form' => __('apponiment.singular_title')]);
            return response()->json(['message' => $message, 'status' => true], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $data = Appointment::findOrFail($id);

        $data->delete();

        $message =  __('messages.delete_form', ['form' => __('appointment.singular_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }
    public function updatePaymentStatus($id, Request $request)
    {

        if (isset($request->action_type) && $request->action_type == 'update-payment-status') {
            $status = $request->value;
        }

        $appointment_status = Appointment::where('id', $id)->first();

        AppointmentTransaction::where('appointment_id', $id)->update(['payment_status' => $request->value]);

        if ($status == 1) {

            CommissionEarning::where('commissionable_id', $id)->update(['commission_status' => 'unpaid']);

        }
        $message = __('appointment.payment_status_update');

        return response()->json(['message' => $message, 'status' => true]);
    }
    public function updateStatus($id, Request $request)
    {
        $appointment = Appointment::where('id', $id)->with('user', 'doctor')->first();
        
        $startDate = Carbon::parse($appointment['start_date_time']);
        $status = $request->status;

        if (isset($request->action_type) && $request->action_type == 'update-status') {

            $status = $request->value;
        }
        $appointment->status = $status;

        if ($status == 'check_in') {

            $encouter_details = $this->generateEncounter($appointment);
            $encounter_details = PatientEncounter::where('id', $encouter_details->id)->with('appointment')->first();
            if($encounter_details){
                $billing_record = $this->generateBillingRecord($encounter_details);
                $billing_record = BillingRecord::where('id', $billing_record->id)->with('clinicservice','patientencounter')->first();
                if($billing_record){
                    $billing_item = $this->generateBillingItem($billing_record);
                }
            }
        }

        if ($status == 'checkout') {
           
            if ($appointment ) {
                $encounter_details = PatientEncounter::where('appointment_id', $appointment->id)
                                                     ->with('appointment')
                                                     ->first();
    
                if ($encounter_details) {
                    $billing_details = BillingRecord::where('encounter_id', $encounter_details->id)
                                                    ->where('payment_status', 1)
                                                    ->first();
    
                    if ($billing_details) {
                        $encounter_details->update(['status' => 0]);
                    } else {
                        return response()->json(['message' => 'Payment not completed. Please complete the payment before checkout.', 'status' => false]);
                    }
                }
            }
        }
        $appointment->update();
        $notify_type = null;

        switch ($status) {
            case 'confirmed':
                $notify_type = 'accept_appointment';
                break;
            case 'rejected':
                $notify_type = 'reject_appointment';
                break;
            case 'checkout':
                $notify_type = 'checkout_appointment';
                break;
            case 'cancelled':
                $notify_type = 'cancel_appointment';
                break;
        }
        $notification_data = [
            'id' => $appointment->id,
            'description' => $appointment->description,
            'appointment_duration' => $appointment->duration,
            'user_id' => $appointment->user_id,
            'user_name' => optional($appointment->user)->first_name ?? default_user_name(),
            'doctor_id' => $appointment->doctor_id,
            'doctor_name' => optional($appointment->doctor)->first_name,
            'appointment_date' => $startDate->format('d/m/Y'),
            'appointment_time' => $startDate->format('h:i A'),
            'appointment_services_names' => ClinicsService::with('systemservice')->find($appointment->service_id)->systemservice->name ?? '--',
            'appointment_services_image' => optional($appointment->clinicservice)->file_url,
            'appointment_date_and_time' => $startDate->format('Y-m-d H:i'),
            'latitude' =>  null,
            'longitude' => null,
        ];
        $this->sendNotificationOnBookingUpdate($notify_type, $notification_data);
        $message = __('appointment.status_update');

        return response()->json(['message' => $message, 'status' => true]);
    }

    public function view()
    {
        return view('appointment::backend.appointment.view');
    }


    public function savePayment(Request $request)
    {

        $data = $request->all();
        $data['tip_amount'] = $data['tip'] ?? 0;
        $appointment = Appointment::findOrFail($data['id']);
        $serviceDetails = ClinicsService::where('id', $appointment->service_id)->with('vendor')->first();
        $vendor = $serviceDetails->vendor ?? null;
        $serviceData = $this->getServiceAmount($appointment->service_id, $appointment->doctor_id, $appointment->clinic_id);
        $tax = $data['tax_percentage'] ?? Tax::active()->whereNull('module_type')->orWhere('module_type', 'services')->where('status',1)->get();

        $transactionData = [
            'appointment_id' => $appointment->id,
            'transaction_type' => $data['transaction_type'] ?? 'cash',
            'total_amount' => $serviceData['total_amount'],
            'payment_status' => $data['payment_status'] ?? 0,
            'discount_value' => $serviceData['discount_value'] ?? 0,
            'discount_type' => $serviceData['discount_type'] ?? null,
            'discount_amount' => $serviceData['discount_amount'] ?? 0,
            'external_transaction_id' => $data['external_transaction_id'] ?? null,
            'tax_percentage' => json_encode($tax),
        ];

        $payment = AppointmentTransaction::updateOrCreate(
            ['appointment_id' => $appointment->id],
            $transactionData
        );

        

        $message = __('appointment.save_appointment');
        return response()->json(['message' => $message, 'data' => $payment, 'status' => true], 200);
    }
    public function joinGoogleMeet(Request $request)
    {
        $id = $request->id;
        $authUrl = Appointment::where('id', $id)->value('meet_link');
        return redirect($authUrl);
    }

    public function joinZoomMeet(Request $request)
    {
        $id = $request->id;
        $authUrl = Appointment::where('id', $id)->value('start_video_link');
        return redirect($authUrl);
    }


    public function generateMeetLink(Request $request, $startDateTime, $duration, $data)
    {
        $employee = User::find($data['doctor_id']);
        if ($employee) {
            $googleAccessToken = json_decode($employee->google_access_token, true);
            $accessToken = $googleAccessToken['access_token'] ?? null;
            $refreshToken = $googleAccessToken['refresh_token'] ?? null;
            $googleMeetSettings = Setting::whereIn('name', ['google_meet_method', 'google_clientid', 'google_secret_key'])
                ->pluck('val', 'name');
            $settings = $googleMeetSettings->toArray();
            $client = new GoogleClient([
                'client_id' => $settings['google_clientid'],
                'client_secret' => $settings['google_secret_key'],
                'redirect_uri' => 'postmessage',
                'access_type' => 'offline', // Use 'offline' for refresh token flow
                'prompt' => 'consent', // Use 'consent' to force user consent for refresh token
                'scopes' => ['https://www.googleapis.com/auth/calendar.events'],
            ]);
            
            $client->setAccessToken($accessToken);
            
            if ($client->isAccessTokenExpired()) {
                if ($refreshToken) {
                    $client->refreshToken($refreshToken);
                    $newAccessToken = $client->getAccessToken();
                    User::where('id', $data['doctor_id'])->update(['google_access_token' => json_encode($newAccessToken)]);

                    $request->session()->put('google_access_token', json_encode($newAccessToken));
                } else {
                    $authUrl = $client->createAuthUrl();
                    return redirect()->away($authUrl);
                }
            }
        }
        $service = new Calendar($client);
        $user = User::find($data->user_id);
        $center = ClinicServiceMapping::with('service', 'center')
            ->where('service_id', $data->service_id)
            ->where('clinic_id', $data->center_id)
            ->first();
        if ($employee && $center) {
            $clinicService = $center->service;
            $clinic = $center->center;

            $emailData = [
                'service_name' => $clinicService->name,
                'user_name' => "{$user->first_name} {$user->last_name}",
                'clinic_name' => $clinic->name,
                'doctor_name' => "{$employee->first_name} {$employee->last_name}",
                'appointment_date' => $data->appointment_date,
                'appointment_time' => $data->appointment_time,
            ];
        }
        $contentSettings = Setting::whereIn('name', ['content', 'google_event'])->pluck('val', 'name');

        $content = $contentSettings['content'] ?? '';
        $googleEvent = $contentSettings['google_event'] ?? '';

        $placeholders = [
            '{{appointment_date}}' => $emailData['appointment_date'],
            '{{appointment_time}}' => $emailData['appointment_time'],
            '{{patient_name}}' => $emailData['user_name'],
            '{{clinic_name}}' => $emailData['clinic_name'],
            '{{appointment_desc}}' => $emailData['clinic_name'],
            '{{service_name}}' => $emailData['clinic_name'],
        ];

        foreach ($placeholders as $placeholder => $value) {
            $content = str_replace($placeholder, $value, $content);
            $googleEvent = str_replace($placeholder, $value, $googleEvent);
        }
        $startDateTime = Carbon::parse($data->appointment_date . ' ' . $data->appointment_time)->format('Y-m-d\TH:i:s');
        $endDateTime = Carbon::parse($startDateTime)->addHour()->format('Y-m-d\TH:i:s');

        $event = new \Google\Service\Calendar\Event([
            'summary' => $googleEvent,
            'description' => $content,
            'start' => [
                'dateTime' => $startDateTime,
                'timeZone' => 'UTC',
            ],
            'end' => [
                'dateTime' => $endDateTime,
                'timeZone' => 'UTC',
            ],
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => uniqid(),
                    'conferenceSolutionKey' => [
                        'type' => 'hangoutsMeet',
                    ],
                ],
            ],
        ]);
        $calendarId = 'primary';
        $event = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);
        $meetingDetails = [
            'title' => $event->getSummary(),
            'description' => $event->getDescription(),
            'start' => $event->getStart()->getDateTime(),
            'end' => $event->getEnd()->getDateTime(),
            'location' => $event->getLocation(),
            'attendees' => $event->getAttendees(),
            'link' => $event->getHangoutLink(),
        ];
        $hangoutLink = $meetingDetails['link'];

        if (!empty($hangoutLink)) {
            $data->meet_link = $hangoutLink;
        }
        $data->save();

        $emails = User::whereIn('id', [$data['employee_id'], $data['user_id']])
            ->pluck('email')
            ->toArray();
        Mail::to($emails)->send(new AppointmentConfirmation($meetingDetails));
        return $meetingDetails;
    }
}
