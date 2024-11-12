<?php

namespace Modules\Clinic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Clinic\Models\Doctor;
use App\Models\User;
use Modules\Clinic\Transformers\DoctorResource;
use Modules\Clinic\Models\DoctorClinicMapping;
use Modules\Clinic\Transformers\DoctorSessionListResource;
use Auth;
use Modules\Clinic\Models\DoctorSession;
use Modules\Clinic\Models\DoctorRating;
use Modules\Clinic\Transformers\DoctorReviewResource;
use Modules\Clinic\Transformers\DoctorDetailsResource;
use Modules\Commission\Models\Commission;
use Modules\Clinic\Transformers\CommissionResource;
use Modules\Clinic\Transformers\PatientResource;
use Modules\Clinic\Transformers\PatientDetailsResource;
use Modules\Earning\Models\EmployeeEarning;
use Modules\Clinic\Transformers\PayoutHistoryResource;
use Modules\Clinic\Models\Receptionist;
use Modules\Clinic\Transformers\ReceptionistResource;
use Modules\Clinic\Models\DoctorServiceMapping;

class DoctorController extends Controller
{

    public function DoctorList(Request $request){

        $perPage = $request->input('per_page', 10);

        if(auth()->user()){
            $doctor_list = Doctor::CheckMultivendor()->SetRole(auth()->user())->with('user','doctorCommission','doctorService','doctorclinic');
        }
        else{
            $doctor_list = Doctor::CheckMultivendor()->with('user','doctorCommission','doctorService','doctorclinic')->where('status',1);
        }

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $doctor_list->whereHas('user', function ($query) use ($searchTerm) {
                $query->where('first_name', 'like', "%{$searchTerm}%")
                ->orWhere('last_name', 'like', "%{$searchTerm}%")
                ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        } 

        if($request->filled('clinic_id')){

            $clinicId=$request->clinic_id;
            $doctor_list->whereHas('doctorclinic', function ($query) use ($clinicId) {
                $query->where('clinic_id',$clinicId);
            });
        }

        if($request->filled('service_id')){

            $serviceId=$request->service_id;
            $doctor_list->whereHas('doctorService', function ($query) use ($serviceId) {
                $query->where('service_id',$serviceId);
            });
        }
    
        if($request->filled('vendor_id')){
            $doctor_list->where('vendor_id',$request->vendor_id);
         }
        
        $doctor = $doctor_list->orderBy('updated_at', 'desc');

        if($perPage=='all'){
            
            $doctor = $doctor->get();
   
           }else{
   
            $doctor = $doctor->paginate($perPage); 
           }
      
        $responseData = DoctorResource::collection($doctor);

        if($request->filled('doctor_id')){
    
            $doctor= $doctor_list->where('doctor_id',$request->doctor_id)->first();
            $responseData =New DoctorDetailsResource($doctor);

            return response()->json([
                'status' => true,
                'data' => $responseData,
                'message' => __('clinic.doctor_details'),
            ], 200);
        }
        
        return response()->json([
            'status' => true,
            'data' => $responseData,
            'message' => __('clinic.doctor_list'),
        ], 200);
    }


     public function DoctorSessionList(Request $request){

        $perPage = $request->input('per_page', 10);

        if(auth()->user()->hasRole('doctor')){
            $doctor_session_list=DoctorClinicMapping::CheckMultivendor()->with('doctorsession')->where('doctor_id', auth()->user()->id);
        }
        else{
            $doctor_session_list=DoctorClinicMapping::CheckMultivendor()->with('doctorsession');
        }

        $doctor_session_list = $doctor_session_list->orderBy('updated_at', 'desc');

        $doctor_session_list  = $doctor_session_list ->paginate($perPage);
        if($request->filled('clinic_id')){

            $clinicId=$request->clinic_id;
            $doctor_session_list = $doctor_session_list->where('clinic_id',$clinicId);
        }

        $responseData = DoctorSessionListResource::collection($doctor_session_list);
        
        return response()->json([
            'status' => true,
            'data' => $responseData,
            'message' => __('clinic.doctor_list'),
        ], 200);

      

     }

    public function saveRating(Request $request)
    {
        $user = auth()->user();
        $rating_data = $request->all();
        $rating_data['user_id'] = $user->id;
        $result = DoctorRating::updateOrCreate(['id' => $request->id], $rating_data);

        $message = __('clinic.rating_update');
        if ($result->wasRecentlyCreated) {
            $message = __('clinic.rating_add');
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    public function deleteRating(Request $request)
    {
        $user = auth()->user();
        $rating = DoctorRating::where('id', $request->id)->where('user_id', $user->id)->first();
        if ($rating == null) {
            $message = __('clinic.rating_notfound');

            return response()->json(['status' => false, 'message' => $message]);

        }
        $message = __('clinic.rating_delete');
        $rating->delete();

        return response()->json(['status' => true, 'message' => $message]);
    }

    public function getRating(Request $request)
    {
        $doctor_id = $request->doctor_id;

        $perPage = $request->input('per_page', 10);

        if(!empty($request->clinic_id)) {
            $clinic_doctors = DoctorClinicMapping::where('clinic_id', $request->clinic_id)->pluck('doctor_id');
            $reviews = DoctorRating::with('clinic_service')->whereIn('doctor_id', $clinic_doctors);

        } 
        if($request->has('doctor_id')){
            $reviews = DoctorRating::with('clinic_service')->where('doctor_id', $doctor_id);
        }
        if($request->has('service_id')){
            $reviews = DoctorRating::with('clinic_service')->where('service_id', $request->service_id);
        }
        
        $reviews = $reviews->orderBy('updated_at', 'desc')->paginate($perPage);
        $review = DoctorReviewResource::collection($reviews);

        return response()->json([
            'status' => true,
            'data' => $review,
            'message' => __('clinic.review_list'),
        ], 200);
    }

    public function doctorCommissionList(Request $request){
        $perPage = $request->input('per_page', 10);
        $commission_list = Commission::where('type', 'doctor_commission')->where('status', 1);  
        
        $commission = $commission_list->orderBy('updated_at', 'desc');

        $commission = $commission->paginate($perPage);
        $responseData = CommissionResource::collection($commission);
        
        return response()->json([
            'status' => true,
            'data' => $responseData,
            'message' => __('clinic.commission_list'),
        ], 200);
    }

    public function doctorPayoutHistory(Request $request){
        $perPage = $request->input('per_page', 10);
        $payout_data = EmployeeEarning::doctorRole(auth()->user())->with('employee')->where('user_type', 'doctor')->orderBy('updated_at','desc');
        $payout_data = $payout_data->paginate($perPage);
        $responseData = PayoutHistoryResource::collection($payout_data);
        
        return response()->json([
            'status' => true,
            'data' => $responseData,
            'message' => __('clinic.commission_list'),
        ], 200);
    }


    public function patientList(Request $request){
        
        $perPage = $request->input('per_page', 10);
        $patient_list = User::role('user')->setRolePatients(auth()->user())->with('profile', 'cities', 'states', 'countries');
        if ($request->has('clinic_id')) {
            $clinicId = $request->input('clinic_id');
            $patient_list->whereHas('appointment', function ($query) use ($clinicId) {
                $query->whereHas('cliniccenter', function ($subQuery) use ($clinicId) {
                    $subQuery->where('clinic_id', $clinicId);
                });
            });
        }
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $patient_list->where(function ($query) use ($searchTerm) {
                $query->where('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('last_name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        // $patient_list = $patient_list->where('user_type', 'user')->where('status', 1);  
        
        $patient = $patient_list->orderBy('updated_at', 'desc');

        if($perPage=='all'){
            
            $patient = $patient->get();

        }else{

            $patient = $patient->paginate($perPage);
        }

        $responseData = PatientResource::collection($patient);

        if($request->filled('patient_id')){
    
            $patient= $patient_list->where('id',$request->patient_id)->first();
            $responseData =New PatientDetailsResource($patient);

            return response()->json([
                'status' => true,
                'data' => $responseData,
                'message' => __('clinic.patient_details'),
            ], 200);
        }
        
        return response()->json([
            'status' => true,
            'data' => $responseData,
            'message' => __('clinic.patient_list'),
        ], 200);
    }

        
    public function receptionistList(Request $request){
        
        $perPage = $request->input('per_page', 10);
        $receptionist_list = Receptionist::with('users', 'clinics');

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $receptionist_list->where('first_name', 'like', "%{$searchTerm}%")
                ->orWhere('last_name', 'like', "%{$searchTerm}%")
                ->orWhere('email', 'like', "%{$searchTerm}%");
        } 
        
        $receptionist = $receptionist_list->orderBy('updated_at', 'desc');

        if($perPage=='all'){
            
            $receptionist = $receptionist->get();

        }else{

            $receptionist = $receptionist->paginate($perPage);
        }

        $responseData = ReceptionistResource::collection($receptionist);

        if($request->filled('receptionist_id')){
    
            $receptionist = $receptionist_list->where('id',$request->receptionist_id)->first();
            $responseData = New ReceptionistResource($receptionist);

            return response()->json([
                'status' => true,
                'data' => $responseData,
                'message' => __('clinic.receptionist_details'),
            ], 200);
        }
        
        return response()->json([
            'status' => true, 
            'data' => $responseData,
            'message' => __('clinic.receptionist_list'),
        ], 200);
    }


    public function doctorSession(Request $request){

        $doctor_session=[];

        if($request->has('clinic_id') && $request->clinic_id && $request->has('doctor_id') && $request->doctor_id){

            $doctor_session=DoctorSession::where('doctor_id', $request->doctor_id)->where('clinic_id',$request->clinic_id)->get();

        }

        return response()->json([
            'status' => true,
            'data' => $doctor_session,
            'message' => __('clinic.doctor_session'),
        ], 200);

    }


    public function assignDoctor(Request $request){
        $user = auth()->user();
        $service_id = $request->service_id;
        $clinic_id = $request->clinic_id;
        $assign_doctors = $request->assign_doctors; 

        if(!empty($assign_doctors)) {

            foreach($assign_doctors as $doctor) {
                $data = [
                    'service_id' => $service_id,
                    'clinic_id' => $clinic_id,
                    'doctor_id' => $doctor['doctor_id'], 
                    'charges' => $doctor['price'], 
                ];

                DoctorServiceMapping::updateOrCreate(
                    ['service_id' => $service_id, 'clinic_id' => $clinic_id, 'doctor_id' => $doctor['doctor_id']],
                    $data
                );
            }
        }
        if($user->hasRole('doctor')){
            $message = __('clinic.price_update');
        }else{
            $message = __('clinic.assign_doctor');
        }
    
        return response()->json([
            'status' => true,

            'message' => $message,
        ], 200);
    }
    

  
  
}
