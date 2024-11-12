<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Appointment\Models\BillingRecord;

class BillingExport implements FromCollection, WithHeadings
{
    public array $columns;

    public array $dateRange;

    public function __construct($columns, $dateRange)
    {
        $this->columns = $columns;
        $this->dateRange = $dateRange;
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
        $query = BillingRecord::SetRole(auth()->user());

        $query->whereDate('created_at', '>=', $this->dateRange[0]);

        $query->whereDate('created_at', '<=', $this->dateRange[1]);

        $query = $query->get();

        $newQuery = $query->map(function ($row) {
            $selectedData = [];

            foreach ($this->columns as $column) {
                switch ($column) {
                    case 'encounter_id':
                        $selectedData[$column]= $row->encounter_id;

                        break;
                  
                    case 'user_id':
                        $selectedData[$column]= optional($row->user)->full_name;

                        break;
                    case 'doctor_id':

                        $selectedData[$column]= optional($row->doctor)->full_name;
                        break;

                    case 'clinic_id':
                        $selectedData[$column]= optional($row->clinic)->name;
                        break;

                    case 'service_id':
                        $selectedData[$column]= optional($row->clinicservice)->name;
                        break;
                        
                    case 'total_amount':
                        $selectedData[$column]= $row->total_amount;
                        break; 
                   
                    case 'date':
                        $selectedData[$column]= $row->date;
                        break;
                              
                    case 'payment_status':
                        if ($row->payment_status) {
                            $payment_status =  'Paid';
                        } else {
                            $payment_status = 'Unpaid';
                        }
                        $selectedData[$column] = $payment_status;
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
