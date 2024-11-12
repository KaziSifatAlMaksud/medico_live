<?php

namespace Modules\Appointment\database\seeders;


use Illuminate\Database\Seeder;

class AppointmentPatientRecordsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('appointment_patient_records')->delete();
        
        \DB::table('appointment_patient_records')->insert(array (
            0 => 
            array (
                'id' => 1,
                'subjective' => 'sksdiahdausdyajdh',
                'objective' => 'sksdiahdausdyajdh',
                'assessment' => 'sksdiahdausdyajdh',
                'plan' => 'sksdiahdausdyajdh',
                'appointment_id' => 1,
                'patient_id' => 3,
                'created_by' => NULL,
                'updated_by' => NULL,
                'deleted_by' => NULL,
                'created_at' => '2024-03-31 22:07:24',
                'updated_at' => '2024-03-31 22:07:24',
            ),
            1 => 
            array (
                'id' => 2,
                'subjective' => 'adad',
                'objective' => 'asdasdas',
                'assessment' => 'dasdad',
                'plan' => 'asdasda',
                'appointment_id' => 2,
                'patient_id' => 4,
                'created_by' => NULL,
                'updated_by' => NULL,
                'deleted_by' => NULL,
                'created_at' => '2024-03-31 22:07:35',
                'updated_at' => '2024-03-31 22:07:35',
            ),
            2 => 
            array (
                'id' => 3,
                'subjective' => 'sadas',
                'objective' => 'dasdasd',
                'assessment' => 'asdad',
                'plan' => 'adada',
                'appointment_id' => 3,
                'patient_id' => 6,
                'created_by' => NULL,
                'updated_by' => NULL,
                'deleted_by' => NULL,
                'created_at' => '2024-03-31 22:07:43',
                'updated_at' => '2024-03-31 22:07:43',
            ),
            3 => 
            array (
                'id' => 4,
                'subjective' => 'dad',
                'objective' => 'adad',
                'assessment' => 'asdasd',
                'plan' => 'asdasdas',
                'appointment_id' => 4,
                'patient_id' => 5,
                'created_by' => NULL,
                'updated_by' => NULL,
                'deleted_by' => NULL,
                'created_at' => '2024-03-31 22:07:53',
                'updated_at' => '2024-03-31 22:07:53',
            ),
            4 => 
            array (
                'id' => 5,
                'subjective' => 'dfff',
                'objective' => 'fffff',
                'assessment' => 'fff',
                'plan' => 'fffff',
                'appointment_id' => 5,
                'patient_id' => 6,
                'created_by' => NULL,
                'updated_by' => NULL,
                'deleted_by' => NULL,
                'created_at' => '2024-03-31 22:08:02',
                'updated_at' => '2024-03-31 22:08:02',
            ),
        ));
        
        
    }
}