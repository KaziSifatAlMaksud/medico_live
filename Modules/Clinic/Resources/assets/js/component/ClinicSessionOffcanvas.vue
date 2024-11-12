<template>
    <form @submit="formSubmit" class="bussiness-hour">
      <div class="offcanvas offcanvas-end offcanvas-w-40" tabindex="-1" id="clinic-session-form" aria-labelledby="form-offcanvasLabel">
        <div class="offcanvas-header border-bottom">
          <h6 class="m-0 h5">
            <span>{{ Title }}</span>
          </h6>
          <button type="button" class="btn-close-offcanvas" data-bs-dismiss="offcanvas" aria-label="Close"><i class="ph ph-x-circle"></i></button>
        </div>
        <div class="offcanvas-body">
          <div class="card">
            <div class="card-body">
              <ul class="list-group list-group-flush">
                <li v-for="(day, index) in weekdays" class="list-group-item p-0 mb-3" :key="++index">
                  <div class="form-group row align-items-center gy-1">
                    <div class="col-sm-3">
                      <div class="d-flex align-items-center justify-content-sm-start justify-content-center gap-1">
                        <div class="form-check">
                          <input class="form-check-input" :value="1" name="is_holiday" :id="`${index}-dayoff`" type="checkbox" :true-value="1" :false-value="0" v-model="day.is_holiday" />
                        </div>
                        <h6 class="text-capitalize m-0">{{ index }}. {{ day.day }}
                          <!-- <i v-if="index === 1" class="fa fa-copy copy-icon" aria-hidden="true" @click="handleCopy"></i> -->
                        </h6>
                      </div>
                    </div>
                    <div :class="{ 'col-sm-6': !day.is_holiday, 'col-sm-9': day.is_holiday }" v-if="!day.is_holiday">
                      <div class="d-flex align-items-center justify-content-sm-end justify-content-center gap-2">
                        <flat-pickr id="start_time" class="session-time" v-model="day.start_time" :config="start_config" :disabled="day.is_holiday ? true : false" :class="{ background_colour: day.is_holiday }"></flat-pickr>
                        <flat-pickr id="end_time" class="session-time" v-model="day.end_time" :config="end_config" :disabled="day.is_holiday ? true : false" :class="{ background_colour: day.is_holiday }"></flat-pickr>
                      </div>
                    </div>
                    <div v-else-if="day.is_holiday" :class="{'col-sm-9': day.is_holiday }">
                      <p class="m-0"> {{ $t('clinic.clinic_closed') }}</p>
                    </div>
                    <template v-if="!day.is_holiday">                      
                      <div class="col-sm-3 text-sm-end text-center">
                        <a @click="addInputField(day)" class="clickable-text text-primary">
                           {{ $t('clinic.lbl_add_break') }}
                        </a>
                      </div>
                    </template>                    
                  </div>
                  <div v-for="(input, index) in day.breaks" :key="index" class="d-flex align-items-center justify-content-center">
                    <div class="empty-check-div flex-shrink-0 d-sm-inline-block d-none"></div>
                    <div class="form-group flex-grow-1">
                      <div class="row align-items-center gy-1">
                        <div class="col-sm-3 text-sm-start text-center">
                          <h6 class="mb-0">{{ $t('clinic.lbl_break') }}</h6>
                        </div>
                        <div class="col-sm-6">
                          <div class="d-flex align-items-center justify-content-sm-start justify-content-center gap-2">
                            <flat-pickr id="start_break" class="session-time" v-model="input.start_break" :config="start_config"></flat-pickr>
                            <flat-pickr id="end_break" class="session-time" v-model="input.end_break" :config="end_config"></flat-pickr> 
                          </div>
                        </div>
                        <div class="col-sm-3 text-sm-end text-center">
                          <a class="text-danger" type="button" @click="deleteInputField(day, index)">{{ $t('messages.remove') }}</a>
                        </div>
                      </div>
                    </div>               
                  </div> 
                </li>
              </ul>
            </div>
          </div>
        </div>        
        <FormFooter :IS_SUBMITED="IS_SUBMITED"></FormFooter>
      </div>
    </form>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  import { LISTING_URL, STORE_URL,CLINIC_DATA } from '../constant/clinic-session'
  import { useModuleId, useRequest } from '@/helpers/hooks/useCrudOpration'
  import FlatPickr from 'vue-flatpickr-component'
  import { useForm } from 'vee-validate'
  import moment from 'moment'
  import * as yup from 'yup'
  import FormFooter from '@/vue/components/form-elements/FormFooter.vue'
  
  const Title = ref(null)
  const Clinic = ref([])
  
  const ClinicId = useModuleId(() => {
    getRequest({ url: CLINIC_DATA, id: ClinicId.value }).then((res) => {
      if (res.data) {
        Clinic.value = res.data
        Title.value = res.data.name +"'s Sessions"
      }
    })
    getRequest({ url: LISTING_URL, id: { clinic_id: ClinicId.value} }).then((res) => {
      if (res.status) {
        if (res.data != '') {
          weekdays.value = res.data
        } else {
          weekdays.value = defaultData()
        }
      }
    })

  }, 'clinic_session')
  
  const IS_SUBMITED = ref(false)
  
  const start_config = ref({
    dateFormat: 'H:i:S',
    altInput: true,
    altFormat: 'h:i K',
    enableTime: true,
    noCalendar: true,
    defaultHour: '09', // Update default hour to 9
    defaultMinute: '00',
    defaultSeconds: '00',
    static: true
  })

  const end_config = ref({
    dateFormat: 'H:i:S',
    altInput: true,
    altFormat: 'h:i K',
    enableTime: true,
    noCalendar: true,
    defaultHour: '18', // Update default hour to 6pm
    defaultMinute: '00',
    defaultSeconds: '00',
    static: true
  })
  
  
  const { storeRequest, getRequest } = useRequest()
  
  const validationSchema = yup.object({
  
  })
  
  const { handleSubmit, errors } = useForm({ validationSchema })
  const display_submit_message = (res) => {
  IS_SUBMITED.value = false
  if (res.status) {
    window.successSnackbar(res.message)
  } else {
    window.errorSnackbar(res.message)
  }
}
  
  const defaultData = () => {
    return [
      { day: 'monday', start_time: moment().set({ hour: 9, minute: 0, second: 0 }).format('HH:mm'), end_time: moment().set({ hour: 18, minute: 0, second: 0 }).format('HH:mm'), is_holiday: false, breaks: [] },
      { day: 'tuesday', start_time: moment().set({ hour: 9, minute: 0, second: 0 }).format('HH:mm'), end_time: moment().set({ hour: 18, minute: 0, second: 0 }).format('HH:mm'), is_holiday: false, breaks: [] },
      { day: 'wednesday', start_time: moment().set({ hour: 9, minute: 0, second: 0 }).format('HH:mm'), end_time: moment().set({ hour: 18, minute: 0, second: 0 }).format('HH:mm'), is_holiday: false, breaks: [] },
      { day: 'thursday', start_time: moment().set({ hour: 9, minute: 0, second: 0 }).format('HH:mm'), end_time: moment().set({ hour: 18, minute: 0, second: 0 }).format('HH:mm'), is_holiday: false, breaks: [] },
      { day: 'friday', start_time: moment().set({ hour: 9, minute: 0, second: 0 }).format('HH:mm'), end_time: moment().set({ hour: 18, minute: 0, second: 0 }).format('HH:mm'), is_holiday: false, breaks: [] },
      { day: 'saturday', start_time: moment().set({ hour: 9, minute: 0, second: 0 }).format('HH:mm'), end_time: moment().set({ hour: 18, minute: 0, second: 0 }).format('HH:mm'), is_holiday: false, breaks: [] },
      { day: 'sunday', start_time: moment().set({ hour: 9, minute: 0, second: 0 }).format('HH:mm'), end_time: moment().set({ hour: 18, minute: 0, second: 0 }).format('HH:mm'), is_holiday: false, breaks: [] }
    ]
  }
  const weekdays = ref(defaultData())
  
  const handleCopy = () => {
    weekdays.value.forEach((day, index) => {
      if (index !== 0) {
        day.start_time = weekdays.value[0].start_time
        day.end_time = weekdays.value[0].end_time
        day.is_holiday = weekdays.value[0].is_holiday
        day.breaks = [...weekdays.value[0].breaks]
      }
    })
  }
  

  const breaks = ref([])
  
  const addInputField = (day) => {
    day.breaks.push({ start_break: moment().set({ hour: 0, minute: 0, second: 0 }).format('HH:mm'), end_break: moment().set({ hour: 0, minute: 0, second: 0 }).format('HH:mm') })
  }
  
  const deleteInputField = (day, index) => {
    day.breaks.splice(index, 1)
  }
  const reset_datatable_close_offcanvas = (res) => {
    IS_SUBMITED.value = false
    if (res.status) {
      window.successSnackbar(res.message)
      bootstrap.Offcanvas.getInstance('#clinic-session-form').hide()
    } else {
      window.errorSnackbar(res.message)
      errorMessages.value = res.all_message
    }
  }
  //Form Submit
  const formSubmit = handleSubmit((values) => {
  
    IS_SUBMITED.value = true
    values.weekdays = weekdays.value
    values.clinic_id = ClinicId.value
  
    storeRequest({ url: STORE_URL, body: values }).then((res) => {
      if (res.status) {
        weekdays.value = res.data
        display_submit_message(res)
        reset_datatable_close_offcanvas(res)
      }
    })
  })
  </script>
  <style>
  .multiselect-clear {
    display: none !important;
  }
  .clickable-text {
    display: inline-block;
    cursor: pointer;
  }
  .background_colour {
    background-color: #50494917 !important;
    cursor: not-allowed;
  }
  .copy-icon {
    color: gray;
  }
  </style>

  