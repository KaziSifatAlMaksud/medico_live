<template>
  <div class="offcanvas offcanvas-end w-50" tabindex="-1" id="appointment-offcanvas" aria-labelledby="form-offcanvasLabel">
    <FormHeader :currentId="currentId" :editTitle="editTitle" createTitle="Details"></FormHeader>
    <div class="offcanvas-body">
      <h5 class="ms-3">Appointment Information</h5>
      <div class="card p-3 m-3">
        <div class="card-body">
          <div class="d-flex gap-3 align-items-center">
            <img :src="ImageViewer || defaultImage" class="img-fluid avatar avatar-120 avatar-rounded mb-2" />
            <div class="pt-2">
              <h5>{{ patient.full_name }}</h5>
              <div class="d-flex gap-5">
                <p>
                  <i class="fas fa-envelope me-2"></i><span class="text-secondary">{{ patient.email }}</span>
                </p>
                <p>
                  <i class="fas fa-phone me-2"></i><span class="text-primary">{{ patient.mobile }}</span>
                </p>
              </div>
              <div class="d-flex gap-3">
                <p>Date And Time:</p>
                <h6>{{ appointment.date }}</h6>
                ,
                <h6>{{ appointment.time }}</h6>
              </div>
            </div>
          </div>
          <div class="mb-3 mt-3">
            <div class="d-flex justify-content-between">
              <div>
                <p class="mb-1">Services:</p>
                <h6>{{ appointment.name }}</h6>
              </div>

              <div>
                <p class="mb-1">Doctor Name</p>
                <h6>--</h6>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-between">
            <div>
              <p class="mb-1">Clinic Name:</p>
              <h6>{{ appointment.clinic_name }}</h6>
            </div>
            <div>
              <p class="mb-1">Location</p>
              <h6>{{ appointment.address }}</h6>
            </div>
            <div>
              <p class="mb-1">Other Details</p>
              <h6>Thyreid,PCOD</h6>
            </div>
          </div>
        </div>
      </div>
      <div class="d-flex justify-content-between align-items-center">
        <h6 class="ms-3">Patient SOAP</h6>
        <a href="#" class="text-secondary">Edit</a>
      </div>

      <div class="card p-3 m-3">
        <div class="card-body">
          <div class="d-grid gap-3">
            <div class="d-flex gap-3 align-items-center">
              <span><i class="fa-regular fa-circle-check"></i> Weight loss, decreased appetite</span>
              <span><i class="fa-regular fa-circle-check"></i> Weight loss, decreased appetite</span>
            </div>
            <div class="d-flex gap-3 align-items-center">
              <span><i class="fa-regular fa-circle-check"></i> Weight loss, decreased appetite</span>
              <span><i class="fa-regular fa-circle-check"></i> Weight loss, decreased appetite</span>
            </div>
            <div class="d-flex gap-3 align-items-center">
              <span><i class="fa-regular fa-circle-check"></i> Weight loss, decreased appetite</span>
              <span><i class="fa-regular fa-circle-check"></i> Weight loss, decreased appetite</span>
            </div>
          </div>
        </div>
      </div>

      <div class="card p-3 m-3">
        <div class="card-body">
          <div class="d-grid gap-3">
            <div class="d-flex align-items-center justify-content-between">
              <p>Pyment Method</p>
              <p>Cash</p>
            </div>
            <div class="d-flex align-items-center justify-content-between">
              <p>Subtotal</p>
              <p>91.00</p>
            </div>
            <div class="d-flex align-items-center justify-content-between">
              <p>Discount</p>
              <p>-20%(24.50)</p>
            </div>
            <div class="d-flex align-items-center justify-content-between">
              <p>Tip</p>
              <p>20.00</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import { CLINIC_APPOINTMNET_URL } from '../constant/clinic-appointment'
import { useModuleId, useRequest } from '@/helpers/hooks/useCrudOpration'
import { useForm, useField } from 'vee-validate'
import FormHeader from '@/vue/components/form-elements/FormHeader.vue'
import moment from 'moment'
const CURRENCY_SYMBOL = ref(window.defaultCurrencySymbol)

// const { getRequest } = useRequest()

// const patient=ref({});
// const service=ref({});
const defaultImage = 'https://dummyimage.com/600x300/cfcfcf/000000.png'
const { getRequest } = useRequest()
const ImageViewer = ref(null)
const patient = ref({})
const appointment = reactive({
  date: '',
  time: '',
  name: '',
  type: '',
  clinic_name: '',
  address: '',
  total_amount: ''
})

const ClinicId = useModuleId(() => {
  // console.log("id",ClinicId.value);
  getRequest({ url: CLINIC_APPOINTMNET_URL, id: ClinicId.value }).then((res) => {
    if (res.status) {
      patient.value = res.data.user
      ;(appointment.date = moment(res.data.appointment_date).format('DD MMM YYYY')), (appointment.time = moment(res.data.appointment_time, 'HH:mm:ss').format('hh:mm A')), (appointment.name = res.data.clinicservice.name)
      appointment.clinic_name = res.data.cliniccenter.clinic_name
      appointment.address = res.data.cliniccenter.address
      appointment.total_amount = res.data.total_amount
      ImageViewer.value = patient.value.profile_image
    }
  })
}, 'appointment-details')
</script>
