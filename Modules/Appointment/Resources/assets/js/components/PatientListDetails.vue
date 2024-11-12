<template>

    <div class="offcanvas offcanvas-end w-50" tabindex="-1" id="PatientList-Deatils-form" aria-labelledby="form-offcanvasLabel">
      <FormHeader :currentId="currentId" :editTitle="editTitle" createTitle="Details"></FormHeader>
      <div class="offcanvas-body">
        <h5 class="ms-3">Patient Information</h5>
          <div class="card p-3 m-3">
            <div class="card-body">
              <div class="d-flex gap-3 align-items-center">
                      <img :src="ImageViewer || defaultImage" class="img-fluid avatar avatar-120 avatar-rounded mb-2"/>
                    <div class="pt-2">
                        <h5>{{ patient.full_name }}</h5>
                        <div class="d-flex gap-5">
                            <p><i class="fas fa-envelope me-2"></i><span class="text-secondary">{{patient.email }}</span></p>
                          <p> <i class="fas fa-phone me-2"></i><span class="text-primary">{{ patient.mobile }}</span></p>
                      </div>
                    </div>
                 </div>
                 <div class="mb-3 mt-3">
                  <h6>About:</h6>
                  <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Modi maiores nostrum eligendi earum dolorum. Necessitatibus nemo dolores unde obcaecati, ab aliquam similique consequuntur delectus est odio aperiam deleniti reprehenderit maiores?</p>
                 </div>
                 <div class="d-flex justify-content-between">
                   <div>
                    <p class="mb-1">Total Review</p>
                    <h6>36</h6>
                  </div>
                  <div>
                    <p class="mb-1">Total Appointment</p>
                      <h6>In Clnic</h6>
                  </div>
                  <div>
                    <p class="mb-1">Other Details</p>
                    <h6>Thyreid,PCOD</h6>
                  </div>
                 </div>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="ms-3">Appointments</h5>
          <a href="#" class="stretched-link">View All</a>
          </div>
          <div class="card m-3 col-md-6">
            <div class="card-body">
              <div class="row">
                <div class="col-8">
                  <div class="d-flex text-danger gap-3">
                      <p>{{ appointment.date }}</p> |
                      <p>{{ appointment.time }}</p>
                    </div>
                    <div>
                      <h5>{{ appointment.name }}</h5>
                      <p>Customer Name: {{ patient.full_name }}</p>
                    </div>
                </div>
                <div class="col-4">
                 
                  <h5 class="text-primary">{{ CURRENCY_SYMBOL }}{{appointment.total_amount }}</h5>
                </div>
              </div>

          </div>
          </div>
      </div>
    </div>
</template>


<script setup>
import { ref ,reactive} from 'vue'
import moment from 'moment'
import { APPOINTMNET_URL} from '../constant/clinic-appointment'
import { useModuleId, useRequest } from '@/helpers/hooks/useCrudOpration'
import FormHeader from '@/vue/components/form-elements/FormHeader.vue'

const CURRENCY_SYMBOL = ref(window.defaultCurrencySymbol)

const defaultImage='https://dummyimage.com/600x300/cfcfcf/000000.png';
const { getRequest } = useRequest()
const ImageViewer = ref(null)
const patient=ref({});
const appointment=reactive({
  date:'',
  time:'',
  name:'',
  type:'',
  total_amount:'',
});

const ClinicId = useModuleId(() => {
getRequest({ url: APPOINTMNET_URL,id: ClinicId.value }).then((res) => {
      if (res.status) {
      patient.value=res.data.user;
      appointment.date= moment(res.data.appointment_date).format('DD MMM YYYY'),
      appointment.time= moment(res.data.appointment_time, 'HH:mm:ss').format('hh:mm A'),
      appointment.name=res.data.clinicservice.name
      appointment.total_amount=res.data.total_amount
      ImageViewer.value = patient.value.profile_image
      }
    })
  }, 'patient-details')


</script>
