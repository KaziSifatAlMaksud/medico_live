<?php

namespace App\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Clinic\Models\ClinicsCategory;
use Modules\Clinic\Models\ClinicsService;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Transformers\AppointmentResource;
use Modules\Clinic\Models\Clinics;
use Modules\Clinic\Transformers\CategoryResource;
use Modules\Clinic\Transformers\ClinicsResource;
use Modules\Clinic\Transformers\ServiceResource;
use Modules\Clinic\Trait\ClinicTrait;
use Modules\Earning\Models\EmployeeEarning;
use Modules\Commission\Models\CommissionEarning;
use Auth;
use Modules\Clinic\Models\Doctor;
use Modules\Clinic\Models\DoctorServiceMapping;
use Modules\Clinic\Models\Receptionist;
use Modules\Clinic\Models\DoctorClinicMapping;
use Modules\Slider\Models\Slider;
use Modules\Slider\Transformers\SliderResource;
use Carbon\Carbon;
use Modules\Clinic\Models\ClinicServiceMapping;

class DashboardController extends Controller
{
     use  ClinicTrait;

     public function DashboardDetail(Request $request)
     {
        $user = User::where('id', $request->user_id)->first();
         $data = $request->all();
 
          $clincs = Clinics::with('specialty','countries','states','cities',)->where('status', 1)->orderBy('updated_at','desc')->take(6)->get();
          $category = ClinicsCategory::where('status', 1)->take(6)->get();
          $service = ClinicsService::CheckMultivendor()->with('category', 'sub_category', 'vendor')->where('status', 1)->orderBy('updated_at','desc')->take(6)->get();
          $appointment = Appointment::with('user', 'doctor', 'clinicservice')->where('status', 'pending')->orderBy('updated_at','desc')->take(6)->get();
          $slider = SliderResource::collection(Slider::where('status', 1)->get());
 
          if($request->filled('latitude') && $request->filled('longitude') && $data['latitude'] !=null && $data['longitude'] !=null ) {
         
              $clincs = $this->getNearestclinic($data['latitude'], $data['longitude']);
              $clincs->take(6);
            }

            $appointment=[];

            if($request->has('user_id') && $request->user_id){

                $appointment = Appointment::where('user_id',$request->user_id)->with('user', 'doctor', 'clinicservice')->where('status', 'pending')->where('start_date_time', '>', now()->startOfDay())->orderBy('updated_at','desc')->take(6)->get();

            }
        $all_unread_count = isset($user->unreadNotifications) ? $user->unreadNotifications->count() : 0;  
         $responseData = [
             'category' => CategoryResource::collection($category),
             'featured_services' => ServiceResource::collection($service),
             'upcoming_appointment' => AppointmentResource::collection($appointment),
             'clinics' => ClinicsResource::collection($clincs),
             'slider' => $slider,
             'notification_count' => $all_unread_count,
         ];
 
         return response()->json([
             'status' => true,
             'data' => $responseData,
             'message' => __('messages.dashboard_detail'),
         ], 200);
     }


    public function VendorDashboardDetail(Request $request)
    {
        $user = User::where('id', $request->vendor_id)->first();
        $vendor_id = $request->has('vendor_id') ? $request->vendor_id : Auth::id();

        $clinic_count=Clinics::SetVendor()->where('status', 1)->count();
        $service_count=ClinicsService::SetVendor()->where('status', 1)->count();
        $total_appoinment = Appointment::with('cliniccenter')
                            ->whereHas('cliniccenter', function ($query) use ($vendor_id) {
                                $query->where('vendor_id', $vendor_id);
                            })->count();

        // $total_payout = EmployeeEarning::where('employee_id', $vendor_id)->sum('commission_amount');
        $total_earning = CommissionEarning::where('employee_id', $vendor_id)->where('commission_status', '!=', 'pending')->sum('commission_amount');

        $total_doctors = Doctor::where('vendor_id', $vendor_id)->where('status', 1)->count();
 
        $clincs = Clinics::SetVendor()->with('specialty','countries','states','cities',)->where('status', 1)->orderBy('updated_at','desc')->take(6)->get();
         
        $appointment = Appointment::with('cliniccenter')
                        ->whereHas('cliniccenter', function ($query) use ($vendor_id) {
                            $query->where('vendor_id', $vendor_id);
                        })
                        ->whereIn('status', ['pending', 'confirmed'])
                        ->where('start_date_time', '>', now()->startOfDay())
                        ->orderBy('updated_at', 'desc')
                        ->take(6)
                        ->get();
        $totalappointment_customer = Appointment::with(['cliniccenter', 'clinicservice'])
                                    ->whereHas('cliniccenter', function ($query) use ($vendor_id) {
                                        $query->where('vendor_id', $vendor_id);
                                    })
                                    ->distinct('user_id')->count();
        $all_unread_count = isset($user->unreadNotifications) ? $user->unreadNotifications->count() : 0; 
        $responseData = [
           
             'vendor_total_clinic'=> $clinic_count,
             'vendor_total_service'=> $service_count,
             'vendor_total_appoinment'=> $total_appoinment,
            //  'vendor_total_payout'=> $total_payout,
             'vendor_total_earning'=>$total_earning,
             'vendor_total_doctors' => $total_doctors,
             'vendor_total_patient' => $totalappointment_customer,
             'upcoming_appointment' => AppointmentResource::collection($appointment),
             'clinics' => ClinicsResource::collection($clincs),
             'notification_count' => $all_unread_count,
        ];
 
        return response()->json([
             'status' => true,
             'data' => $responseData,
             'message' => __('messages.dashboard_detail'),
        ], 200);
    }



    public function searchList(Request $request)
    {
        $query = $request->input('query');
        $results = [];


        // Search in Employees // Need To Add Role Base
        $employees = User::role('doctor')->where(function ($queryBuilder) use ($query) {
            $queryBuilder->where('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%");
        })->get();
        $results['employees'] = $employees;

        // Search in Categories
        $categories = Category::where('name', 'like', "%{$query}%")->get();
        $results['categories'] = $categories;

        $subcategories = Category::where('name', 'like', "%{$query}%")
            ->orWhere('parent_id', 'like', "%{$query}%")
            ->get();
        $results['subcategory'] = $subcategories;

        // Search in Services
        $services = Service::where('name', 'like', "%{$query}%")->get();
        $results['services'] = $services;

        return response()->json($results);
    }



    // protected function getNearestclinic($latitude, $longitude)
    // {

    //       $nearestEntities = Clinics::selectRaw("*, latitude, longitude,
    //       ( 6371 * acos( cos( radians($latitude) ) *
    //       cos( radians( latitude ) ) *
    //       cos( radians( longitude ) - radians($longitude) ) +
    //       sin( radians($latitude) ) *
    //       sin( radians( latitude ) ) )
    //       ) AS distance")
    //           ->where('latitude', '!=', null)
    //           ->where('longitude', '!=', null)
    //           ->having("distance", "<=", 16) // Adjust distance as needed
    //           ->orderBy("distance", 'asc')
    //           ->forPage(1, 6)
    //           ->get();
     
    //     return $nearestEntities;
    // }

    public function doctorDashboardDetail(Request $request)
    {
        $clinic_id = $request->clinic_id;
        $doctor_id = $request->has('doctor_id') ? $request->doctor_id : Auth::id();
        $user = User::where('id', $doctor_id)->first();

        $appointment = Appointment::SetRole(auth()->user())->where('clinic_id', $clinic_id);

        $totalappointment = $appointment->where('doctor_id', $doctor_id)->count();

        $totalpatient = $appointment->distinct()->pluck('user_id')->count();

        $totalearning = EmployeeEarning::where('employee_id', $doctor_id)->sum('commission_amount');

        // $totalearning = CommissionEarning::where('employee_id', $doctor_id)->where('commission_status', '!=', 'pending')->sum('commission_amount');

        $doctorServices = DoctorServiceMapping::where('clinic_id', $clinic_id)
                                      ->where('doctor_id', $doctor_id)
                                      ->pluck('service_id');

        $total_service = $doctorServices->count();
        $service_ids = $doctorServices->toArray();

        $services = ClinicsService::CheckMultivendor()->with('category', 'sub_category', 'vendor')->whereIn('id', $service_ids)->where('status', 1)->orderBy('updated_at','desc')->take(5)->get();
        $services = ServiceResource::collection($services);
        $upcoming_appointment = $appointment->where('doctor_id', $doctor_id)->where('start_date_time', '>=', now()->startOfDay())->orderBy('updated_at','desc')->get();
        // dd($user);
        
        $all_unread_count = isset($user->unreadNotifications) ? $user->unreadNotifications->count() : 0; 
        $responseData = [
           
            'doctor_total_appointments' => $totalappointment,
            'doctor_total_patient' => $totalpatient,
            'doctor_total_earning' => $totalearning,
            'doctor_total_service_count' => $total_service,
            'doctor_services' => $services,
            'upcoming_appointment' => AppointmentResource::collection($upcoming_appointment),
            'notification_count' => $all_unread_count,
         ];
 
         return response()->json([
             'status' => true,
             'data' => $responseData,
             'message' => __('messages.dashboard_detail'),
         ], 200);
    }

    public function receptionistDashboardDetail(Request $request)
    {
        $user = User::where('id', $request->receptionist_id)->first();
        $receptionist_id = $request->has('receptionist_id') ? $request->receptionist_id : Auth::id();

        $clinic_id = Receptionist::CheckMultivendor()->where('receptionist_id', $receptionist_id)->pluck('clinic_id');

        $totalappointment = 0;
        $totalpatient = 0;
        $totalearning = 0;
        $totalassigndoctor = 0;
        $upcoming_appointment = [];
        $receptionist_clinic = [];
        if($clinic_id->isNotEmpty()){
            $appointment = Appointment::SetRole(auth()->user());
            $totalappointment = $appointment->where('clinic_id', $clinic_id)->count();
            $totalpatient = User::with('appointment')->whereHas('appointment.cliniccenter', function ($qry) use ($clinic_id) {
                $qry->where('clinic_id', $clinic_id);
            })->count();
            $totalearning = $appointment->with('appointmenttransaction')->whereHas('appointmenttransaction', function($q){
                                $q->where('payment_status' , '!=' , '0');
                            })
                                ->where('clinic_id', $clinic_id)
                                ->selectRaw('SUM(total_amount) as total_amount_sum')
                                ->first();

            $totalassigndoctor = DoctorClinicMapping::Where('clinic_id', $clinic_id)->count();

            $upcoming_appointment = Appointment::SetRole(auth()->user())->where('clinic_id', $clinic_id)->where('start_date_time', '>', now()->startOfDay())->orderBy('updated_at','desc')->get();
            $receptionist_clinic = Clinics::with('specialty','countries','states','cities',)->where('status', 1)->orderBy('updated_at','desc')->where('id', $clinic_id)->get();
            $totalservice = ClinicsService::with('ClinicServiceMapping')->whereHas('ClinicServiceMapping', function($q) use($clinic_id){
                $q->where('clinic_id', $clinic_id);
            })->where('status', 1)->orderBy('updated_at','desc')->count();
        }
        $all_unread_count = isset($user->unreadNotifications) ? $user->unreadNotifications->count() : 0; 
        $responseData = [
           
            'receptionist_total_appointments' => $totalappointment,
            'receptionist_total_patient' => $totalpatient,
            'receptionist_total_earning' => $totalearning->total_amount_sum ?? 0,
            'receptionist_total_assign_doctor' => $totalassigndoctor,
            'upcoming_appointment' => AppointmentResource::collection($upcoming_appointment),
            'receptionist_clinic' => ClinicsResource::collection($receptionist_clinic),
            'receptionist_total_service_count' => $totalservice,
            'notification_count' => $all_unread_count,
         ];
 
         return response()->json([
             'status' => true,
             'data' => $responseData,
             'message' => __('messages.dashboard_detail'),
         ], 200);
    }
}
