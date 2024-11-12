<?php

use Illuminate\Support\Facades\Route;
use Modules\Appointment\Http\Controllers\Backend\AppointmentsController;
use Modules\Appointment\Http\Controllers\Backend\ClinicAppointmentController;
use Modules\Appointment\Http\Controllers\Backend\PatientEncounterController;
use Modules\Appointment\Http\Controllers\Backend\EncounterTemplateController;
use Modules\Appointment\Http\Controllers\Backend\BillingRecordController;
use Modules\Appointment\Http\Controllers\Backend\ProblemsController;
use Modules\Appointment\Http\Controllers\Backend\ObservationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
/*
*
* Backend Routes
*
* --------------------------------------------------------------------
*/

Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth']], function () {
  /*
    * These routes need view-backend permission
    * (good if you want to allow more than one group in the backend,
    * then limit the backend features by different roles or permissions)
    *
    * Note: Administrator has all permissions so you do not have to specify the administrator role everywhere.
    */

  /*
     *
     *  Backend Appointments Routes
     *
     * ---------------------------------------------------------------------
     */

  Route::group(['prefix' => 'appointment', 'as' => 'appointment.'], function () {
    // // Route::get("index_list", [AppointmentsController::class, 'index_list'])->name("index_list");
    // // Route::get("index_data", [AppointmentsController::class, 'index_data'])->name("index_data");
    // // Route::get('export', [AppointmentsController::class, 'export'])->name('export');
    // // Route::post('/update-status/{id}', [AppointmentsController::class, 'updateStatus'])->name('updateStatus');
    // // Route::post('/update-payment-status/{id}', [AppointmentsController::class, 'updatePaymentStatus'])->name('updatePaymentStatus');
    // // Route::get('patient_list', [AppointmentsController::class, 'patient_list'])->name('patient_list');
    // // Route::get('view', [AppointmentsController::class, 'view'])->name('view');
    Route::post('save-payment', [AppointmentsController::class, 'savePayment'])->name('save_payment');
    // Route::post('/update-status/{id}', [AppointmentsController::class, 'updateStatus'])->name('updateStatus');
  });
  Route::resource("appointment", AppointmentsController::class);


  Route::group(['prefix' => 'appointments', 'as' => 'appointments.'], function () {
    Route::get("index_list", [ClinicAppointmentController::class, 'index_list'])->name("index_list");
    Route::get("index_data", [ClinicAppointmentController::class, 'index_data'])->name("index_data");
    Route::get('export', [ClinicAppointmentController::class, 'export'])->name('export');
    Route::get('view', [AppointmentsController::class, 'view'])->name('view');
    Route::post('/update-status/{id}', [AppointmentsController::class, 'updateStatus'])->name('updateStatus');
    Route::post('/update-payment-status/{id}', [AppointmentsController::class, 'updatePaymentStatus'])->name('updatePaymentStatus');
    Route::put("appointment_patient/{id}", [ClinicAppointmentController::class, 'appointment_patient'])->name("appointment_patient");
    Route::get("{id}/appointment_patient_data", [ClinicAppointmentController::class, 'appointment_patient_data'])->name("appointment_patient_data");
    Route::put("appointment_bodychart/{id}", [ClinicAppointmentController::class, 'appointment_bodychart'])->name("appointment_bodychart");
    Route::get("{id}/appointment_bodychart_data", [ClinicAppointmentController::class, 'appointment_bodychart_data'])->name("appointment_bodychart ");
    Route::get('patient_list', [ClinicAppointmentController::class, 'patient_list'])->name('patient_list');
    Route::get('patient_list.export', [ClinicAppointmentController::class, 'patientListExport'])->name('patient_list.export');
    Route::get('appointment-details/{id}', [ClinicAppointmentController::class, 'patientDeatails'])->name('patientDeatails');

    Route::get("index_patientdata", [ClinicAppointmentController::class, 'index_patientdata'])->name("index_patientdata");
    Route::get('clinicAppointmentDetail/{id}', [ClinicAppointmentController::class, 'appointmentDetail'])->name('clinicAppointmentDetail');
    Route::get('patient_list/{id}', [ClinicAppointmentController::class, 'patientDeatails'])->name('patientDeatails');
    Route::get('invoice_detail', [ClinicAppointmentController::class, 'invoice_detail'])->name('invoice_detail');
    Route::get('download_invoice', [ClinicAppointmentController::class, 'downloadPDf'])->name('download_invoice');
    Route::post('bulk-action', [ClinicAppointmentController::class, 'bulk_action'])->name('bulk_action');
  });

  Route::group(['prefix' => 'bodychart', 'as' => 'bodychart.'], function () {
    Route::get('bodychart_datatable/{id}', [ClinicAppointmentController::class, 'bodychart_datatable'])->name("bodychart_datatable");
    Route::delete('bodychartdestroy/{id}', [ClinicAppointmentController::class, 'bodychartdestroy'])->name("bodychartdestroy");
    Route::get("bodychart_image_list", [ClinicAppointmentController::class, 'bodychart_image_list'])->name("patient-record");
    Route::put("bodychart_form/appointment_bodychart/{id}", [ClinicAppointmentController::class, 'appointment_bodychart'])->name("appointment_bodychart");
    Route::get("bodychart_form/{id}/appointment_bodychart_data", [ClinicAppointmentController::class, 'appointment_bodychart_data'])->name("appointment_bodychart ");
    Route::get("bodychart_form/{id}/bodychart_templatedata", [ClinicAppointmentController::class, 'bodychart_templatedata'])->name("bodychart_templatedata");
    Route::get("bodychart_form/bodychart_image_list", [ClinicAppointmentController::class, 'bodychart_image_list'])->name("patient-record");
    Route::get('bodychart_form/{id}', [ClinicAppointmentController::class, 'bodychart_form'])->name("bodychart_form");
    Route::put("editbodychartview/appointment_upadtebodychart/{id}", [ClinicAppointmentController::class, 'appointment_upadtebodychart'])->name("appointment_upadtebodychart");
    Route::get("editbodychartview/{id}/bodychart_templatedata", [ClinicAppointmentController::class, 'bodychart_templatedata'])->name("bodychart_templatedata");
    Route::get("editbodychartview/{id}/appointment_bodychart_data", [ClinicAppointmentController::class, 'appointment_bodychart_data'])->name("appointment_bodychart ");
    Route::get("editbodychartview/bodychart_image_list", [ClinicAppointmentController::class, 'bodychart_image_list'])->name("patient-record");
    Route::get('editbodychartview/{id}', [ClinicAppointmentController::class, 'editbodychartview'])->name("editbodychartview");
    Route::post('bodychart-bulk-action', [ClinicAppointmentController::class, 'bodychart_bulk_action'])->name('bodychart_bulk_action');
  });
  Route::get('google_connect', [AppointmentsController::class, 'joinGoogleMeet'])->name('google_connect');
  Route::get('zoom_connect', [AppointmentsController::class, 'joinZoomMeet'])->name('zoom_connect');
  Route::get('callback', [AppointmentsController::class, 'Callback']);
  Route::get("patient-record", [ClinicAppointmentController::class, 'patient_record'])->name("patient-record");
  Route::get("bodychart/{id}", [ClinicAppointmentController::class, 'bodychart'])->name("bodychart");
  Route::resource("appointments", ClinicAppointmentController::class);

  Route::group(['prefix' => 'encounter', 'as' => 'encounter.'], function () {
    Route::get("index_list", [PatientEncounterController::class, 'index_list'])->name("index_list");
    Route::get("index_data", [PatientEncounterController::class, 'index_data'])->name("index_data");
    Route::get('export', [PatientEncounterController::class, 'export'])->name('export');
    Route::get('encounter-detail/{id}', [PatientEncounterController::class, 'encounterDetail'])->name('encounter_detail');
    Route::post('save-select-option', [PatientEncounterController::class, 'saveSelectOption'])->name('save_select_option');
    Route::get('remove-histroy-data', [PatientEncounterController::class, 'removeHistroyData']);
    Route::post('save-prescription', [PatientEncounterController::class, 'savePrescription']);
    Route::get('edit-prescription/{id}', [PatientEncounterController::class, 'editPrescription']);
    Route::post('update-prescription/{id}', [PatientEncounterController::class, 'updatePrescription']);
    Route::get('delete-prescription/{id}', [PatientEncounterController::class, 'deletePrescription']);
    Route::post('save-other-details', [PatientEncounterController::class, 'saveOtherDetails']);
    Route::post('save-medical-report', [PatientEncounterController::class, 'saveMedicalReport']);
    Route::get('edit-medical-report/{id}', [PatientEncounterController::class, 'editMedicalReport']);
    Route::post('update-medical-report/{id}', [PatientEncounterController::class, 'updateMedicalReport']);
    Route::get('delete-medical-report/{id}', [PatientEncounterController::class, 'deleteMedicalReport']);
    Route::get('get_report_data', [PatientEncounterController::class, 'GetReportData']);
    Route::post('send-medical-report', [PatientEncounterController::class, 'SendMedicalReport']);
    Route::post('send-prescription', [PatientEncounterController::class, 'sendPrescription']);
    Route::post('import-prescription', [PatientEncounterController::class, 'importPrescription']);
    Route::post('export-prescription', [PatientEncounterController::class, 'exportPrescriptionData']);
    Route::post('save-encounter-details', [PatientEncounterController::class, 'saveEncouterDetails']);
    Route::get('download-encounterinvoice', [Modules\Appointment\Http\Controllers\Backend\API\PatientEncounterController::class, 'encounterInvoice']);

    Route::get('download-prescription', [Modules\Appointment\Http\Controllers\Backend\API\PatientEncounterController::class, 'downloadPrescription']);



    Route::post('bulk-action', [PatientEncounterController::class, 'bulk_action'])->name('bulk_action');
    // // encounter module routes starts here...
    // Route::get('patient_encounter_list'  , [ PatientEncounterController::class,'index']);
    // Route::post('patient_encounter_save' ,   [PatientEncounterController::class,'save']);
    // Route::get('patient_encounter_edit'   , [ PatientEncounterController::class,'edit']);
    // Route::get('patient_encounter_delete' , [ PatientEncounterController::class,'delete']);
    // Route::get('patient_encounter_details', [ PatientEncounterController::class,'details']);
    // Route::post('save_custom_patient_encounter_field',  [PatientEncounterController::class,'saveCustomField']);
    // Route::post('patient_encounter_update_status',  [PatientEncounterController::class,'updateStatus']);
    // Route::get('print_encounter_bill_detail' ,   [PatientEncounterController::class,'printEncounterBillDetail']);
    // Route::get('encounter_extra_clinical_detail_fields' ,   [PatientEncounterController::class,'encounterExtraClinicalDetailFields']);
  });
  Route::resource("encounter", PatientEncounterController::class);


Route::group(['prefix' => 'encounter-template', 'as' => 'encounter-template.'], function () {
  Route::get("index_list", [EncounterTemplateController::class, 'index_list'])->name("index_list");
  Route::get("index_data", [EncounterTemplateController::class, 'index_data'])->name("index_data");
  Route::get('export', [EncounterTemplateController::class, 'export'])->name('export');
  Route::post('bulk-action', [EncounterTemplateController::class, 'bulk_action'])->name('bulk_action');
  Route::post('update-status/{id}', [EncounterTemplateController::class, 'updateStatus'])->name('update_status');
  Route::get('template-detail/{id}', [EncounterTemplateController::class, 'templateDetail'])->name('template-detail');
  Route::post('save-template-histroy', [EncounterTemplateController::class, 'saveTemplateHistroy']);
  Route::get('remove-template-histroy', [EncounterTemplateController::class, 'removeTemplateHistroy']);
  Route::post('save-prescription', [EncounterTemplateController::class, 'savePrescription']);
  Route::get('edit-prescription/{id}', [EncounterTemplateController::class, 'editPrescription']);
  Route::post('update-prescription/{id}', [EncounterTemplateController::class, 'updatePrescription']);
  Route::get('delete-prescription/{id}', [EncounterTemplateController::class, 'deletePrescription']);
  Route::post('save-other-details', [EncounterTemplateController::class, 'saveOtherDetails']);
  Route::get('template-list', [EncounterTemplateController::class, 'index_list'])->name("index_list");
  Route::get('get-template-detail/{id}', [EncounterTemplateController::class, 'getTemplateDetails']);

  
});
Route::resource("encounter-template", EncounterTemplateController::class);

Route::group(['prefix' => 'billing-record', 'as' => 'billing-record.'], function () {
  Route::get("index_list", [BillingRecordController::class, 'index_list'])->name("index_list");
  Route::get("index_data", [BillingRecordController::class, 'index_data'])->name("index_data");
  Route::post('bulk-action', [BillingRecordController::class, 'bulk_action'])->name('bulk_action');
  Route::get('export', [BillingRecordController::class, 'export'])->name('export');
  Route::post('/update-status/{id}', [BillingRecordController::class, 'updateStatus'])->name('updateStatus');

  Route::post('/save-billing-details', [BillingRecordController::class, 'saveBillingDetails']);
  Route::get('billing-detail', [BillingRecordController::class, 'billing_detail'])->name('billing_detail');

  Route::get('edit-billing-detail', [BillingRecordController::class, 'EditBillingDetails']);

  Route::get('encounter_billing_detail', [BillingRecordController::class, 'encounter_billing_detail']);
  Route::post('/save-billing-items', [BillingRecordController::class, 'saveBillingItems']);
  Route::get('billing-item-details', [BillingRecordController::class, 'billing_item_detail'])->name('billing_item_detail');
  Route::get('edit-billing-item/{id}', [BillingRecordController::class, 'editBillingItem']);
  Route::get('delete-billing-item/{id}', [BillingRecordController::class, 'deleteBillingItem']);
 


});
Route::resource("billing-record", BillingRecordController::class);

Route::group(['prefix' => 'problems', 'as' => 'problems.'], function () {
  Route::get("index_list", [ProblemsController::class, 'index_list'])->name("index_list");
  Route::get("index_data", [ProblemsController::class, 'index_data'])->name("index_data");
  Route::post('bulk-action', [ProblemsController::class, 'bulk_action'])->name('bulk_action');
  
});
Route::resource("problems", ProblemsController::class);
Route::get("problem_fillter", [ProblemsController::class, 'problemFillter'])->name("problem_fillter");


Route::group(['prefix' => 'observation', 'as' => 'observation.'], function () {
  Route::get("index_list", [ObservationController::class, 'index_list'])->name("index_list");
  Route::get("index_data", [ObservationController::class, 'index_data'])->name("index_data");
  Route::post('bulk-action', [ObservationController::class, 'bulk_action'])->name('bulk_action');
  
});
Route::resource("observation", ObservationController::class);

});
