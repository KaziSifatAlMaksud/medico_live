<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Appointment\Models\Appointment;
class ClinicAppointmentExport implements FromCollection, WithHeadings
{
    public array $columns;

    public array $dateRange;
    public $exportDoctorId;

    public function __construct($columns, $dateRange, $exportDoctorId)
    {
        $this->columns = $columns;
        $this->dateRange = $dateRange;
        $this->exportDoctorId = $exportDoctorId;
    }

    public function headings(): array
    {
        $modifiedHeadings = [];

        foreach ($this->columns as $column) {
            // Capitalize each word and replace underscores with spaces
            $modifiedHeadings[] = ucwords(str_replace('_', ' ', $column));
        }

        return $modifiedHeadings;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $userId = auth()->id();
        $doctorId = $this->exportDoctorId;
        
        if($this->exportDoctorId !== null){
            $query = Appointment::SetRole(auth()->user())->with('payment', 'commissionsdata','patientEncounter','cliniccenter')
                    ->where('doctor_id', $doctorId )
                    ->where('status','checkout')
                    ->WhereHas('payment', function ($paymentQuery) {
                        $paymentQuery->where('payment_status', 1); 
                    })
                    ->whereHas('commissionsdata', function ($commissionQuery) use ($doctorId) {
                        $commissionQuery->where('commission_status', 'unpaid')->where('employee_id', $doctorId);
                    });
        }
        else{
            $query = Appointment::SetRole(auth()->user())->with('payment', 'commissionsdata','patientEncounter','cliniccenter');
        }
        
        $query->whereRaw(
            'CAST(CONCAT(appointment_date, " ", appointment_time) AS DATETIME) >= ?',
            [$this->dateRange[0]]
        );
        
        $query->whereRaw(
            'CAST(CONCAT(appointment_date, " ", appointment_time) AS DATETIME) <= ?',
            [$this->dateRange[1]]
        );

        $query = $query->orderBy('id', 'asc');
        $status = config('appointment.STATUS');
        $payment_status = config('appointment.PAYMENT_STATUS');

        $query = $query->get();

        $newQuery = $query->map(function ($row) {
            $selectedData = [];

            foreach ($this->columns as $column) {
                switch ($column) {
                    case 'varification_status':
                        $selectedData[$column] = __('customer.msg_unverified');
                        if ($row['email_verified_at']) {
                            $selectedData[$column] = __('customer.msg_verified');
                        }
                        break;

                    case 'Patient Name':
                        $selectedData[$column]= optional($row->user)->full_name;

                        break;
                    case 'doctor':

                        $selectedData[$column]= optional($row->doctor)->full_name;
                        break;

                    case 'services':
                        $selectedData[$column]= optional($row->clinicservice)->name;
                        break;

                    case 'payment_status':
                        if ($row->appointmenttransaction) {
                            if ($row->appointmenttransaction->payment_status != 1) {
                                $selectedData[$column] = __('Pending');
                            } else {
                                $selectedData[$column] = __('Paid');
                            }
                        } else {
                            $selectedData[$column] = __('Unknown');
                        }
                        break;

                    case 'is_banned':
                        $selectedData[$column] = 'no';
                        if ($row[$column]) {
                            $selectedData[$column] = 'yes';
                        }
                        break;

                    default:
                        $selectedData[$column] = $row[$column];
                        break;
                }
            }

            return $selectedData;
        });

        return $newQuery;
    }
}
