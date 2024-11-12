<template>
  <form @submit="formSubmit" class="bussiness-hour">
    <div class="offcanvas offcanvas-end offcanvas-w-40" tabindex="-1" id="session-form-offcanvas"
      aria-labelledby="form-offcanvasLabel">
      <div class="offcanvas-header border-bottom" v-if="Doctor">
        <h6 class="m-0 h5">
          <span>{{ Title }}</span>
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <div class="form-group" v-if="clinic.options.length > 0">
          <label class="form-label">{{ $t('clinic.lbl_clinic_name') }}<span class="text-danger">*</span></label>
          <Multiselect id="clinic_id" v-model="clinic_id" v-bind="singleSelectOption" :options="clinic.options"
            @select="ClinicSelect" class="form-group"></Multiselect>
          <span class="text-danger">{{ errors.clinic_id }}</span>
        </div>
        <div class="card">
          <div class="card-body">
            <ul class="data-scrollbar list-group list-group-flush">
              <li v-if="isLoading" class="list-group-item p-0 mb-3">
                <p>Loading...</p>
              </li>
              <li v-else v-for="(day, index) in weekdays" class="list-group-item p-0 mb-3" :key="++index">
                <div class="form-group d-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center gap-1 col-md-3">
                    <div class="form-check">
                      <input class="form-check-input" :value="1" name="is_holiday" :id="`${index}-dayoff`"
                        type="checkbox" :true-value="1" :false-value="0" v-model="day.is_holiday"
                        :disabled="clinicHolidays.includes(day.day)"
                        :checked="day.is_holiday || clinicHolidays.includes(day.day)" />
                    </div>
                    <h6 class="text-capitalize m-0 session-day"> {{ day.day }}
                      <!-- <i v-if="index === 1" class="fa fa-copy copy-icon" aria-hidden="true" @click="handleCopy"></i> -->
                    </h6>

                  </div>
                  <div class="col-md-6 d-flex align-items-center gap-2" v-if="!day.is_holiday && !clinicHolidays.includes(day.day)">
                    <flat-pickr id="start_time" class="session-time" v-model="day.start_time" :config="config"
                      :disabled="day.is_holiday ? true : false || clinicHolidays.includes(day.day)"
                      :class="{ background_colour: day.is_holiday }"></flat-pickr>
                    <flat-pickr id="end_time" class="session-time" v-model="day.end_time" :config="config"
                      :disabled="day.is_holiday ? true : false || clinicHolidays.includes(day.day)"
                      :class="{ background_colour: day.is_holiday }"></flat-pickr>
                    <div v-if="day.startTimeError" class="text-danger">{{ day.startTimeError }}</div>
                    <div v-if="day.endTimeError" class="text-danger">{{ day.endTimeError }}</div>
                  </div>
                  <div v-else-if="clinicHolidays.includes(day.day)" :class="{'col-sm-9': clinicHolidays.includes(day.day) }">
                      <p class="m-0"> {{ $t('clinic.clinic_closed') }}</p>
                    </div>
                    <div v-else-if="day.is_holiday" :class="{'col-sm-9': day.is_holiday }">
                      <p class="m-0"> {{ $t('clinic.unavailable') }}</p>
                    </div>
                  <div class="col-md-3 text-end">
                  <template v-if="!day.is_holiday && !clinicHolidays.includes(day.day)">
                    <div>
                      <a @click="addInputField(day)" class="clickable-text text-secondary">
                        {{ $t('clinic.lbl_add_break') }}
                      </a>
                    </div>
                  </template>
                  </div>
                </div>
                <div v-for="(input, index) in day.breaks" :key="index"
                  class="form-group d-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center gap-1 col-md-3">
                     <div class="form-check"></div>
                     <h6> {{ $t('clinic.lbl_break') }}</h6>
                  </div>
                  <div class="col-md-6 d-flex align-items-center gap-2">
                    <flat-pickr id="start_break" class="session-time" v-model="input.start_break"
                      :config="config"></flat-pickr>
                    <flat-pickr id="end_break" class="session-time" v-model="input.end_break"
                      :config="config"></flat-pickr>
                  </div>
                  <div class="col-md-3 text-end">
                    <a class="btn btn-sm btn-primary" @click="deleteInputField(day, index)">{{ $t('messages.remove')
                      }}</a>
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
import { ref, onMounted, watchEffect, computed } from 'vue'
import { useForm, useField } from 'vee-validate'
import * as yup from 'yup'
import FlatPickr from 'vue-flatpickr-component'
import moment from 'moment'
import { CLINIC_LIST, LISTING_URL, STORE_URL, DOCTOR_DATA, CLINIC_SESSION_LIST } from '../constant/doctor-session'
import { useModuleId, useRequest } from '@/helpers/hooks/useCrudOpration'
import { useSelect } from '@/helpers/hooks/useSelect'
import FormFooter from '@/vue/components/form-elements/FormFooter.vue'

const singleSelectOption = ref({
  closeOnSelect: true,
  searchable: true,
  clearable: false
})

const Title = ref(null)
const Doctor = ref([])
const isLoading=ref(false)
const DoctorId = useModuleId(() => {
  isLoading.value = true;
  getRequest({ url: DOCTOR_DATA, id: DoctorId.value }).then((res) => {
    if (res.data) {
      Doctor.value = res.data
      Title.value = res.data.first_name + ' ' + res.data.last_name + "'s Sessions"
    }
  })

  useSelect({ url: CLINIC_LIST, data: DoctorId.value }, { value: 'id', label: 'clinic_name' }).then((data) => {
    clinic.value = data
    if (data.options.length > 0) {
      clinic_id.value = data.options[0].value
    }
    //getClinicSession(clinic_id.value);
    getRequest({ url: LISTING_URL, id: { clinic_id: clinic_id.value, doctor_id: DoctorId.value } }).then((res) => {
      if (res.status) {
        if (res.data != '') {
          weekdays.value = res.data
        } else {
          getClinicSession(clinic_id.value);
        }
      }
    }).finally(() => {
      isLoading.value = false; // End loading
    });
  }).catch(() => {
    isLoading.value = false; // End loading on error
    getClinicSession(clinic_id.value);
  });
}, 'employee_assign')

const clinicHolidays = ref([]);
const clinicStartTime = ref([]);
const clinicEndTime = ref([]);
const getClinicSession = async () => {
  try {
    const res = await getRequest({ url: CLINIC_SESSION_LIST, id: clinic_id.value });

    if (res.status && res.data) {
      weekdays.value = res.data;
      clinicStartTime.value = res.data.map(day => day.start_time);
      clinicEndTime.value = res.data.map(day => day.end_time);
      clinicHolidays.value = res.data.filter(day => day.is_holiday).map(day => day.day);
    } else {
      console.log('No data available or status is false');
      weekdays.value = defaultData();
    }
  } catch (error) {
    console.error('Failed to fetch clinic session:', error);
  }
};


onMounted(() => {
  //getClinicSession();
})

const IS_SUBMITED = ref(false)

const config = ref({
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

const { storeRequest, getRequest } = useRequest()

const validationSchema = yup.object({
  clinic_id: yup.number().required(),
})

const { handleSubmit, errors } = useForm({ validationSchema })

const { value: clinic_id } = useField('clinic_id')

const clinic = ref({ options: [], list: [] })

// message
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

const ClinicSelect = (e) => {
  isLoading.value = true; 
  const ClinicId = clinic_id.value
  const doctorId = DoctorId.value
  getClinicSession(ClinicId);
  getRequest({ url: LISTING_URL, id: { clinic_id: ClinicId, doctor_id: doctorId } }).then((res) => {
    if (res.status) {
      if (res.data != '') {
        weekdays.value = res.data
      } else {
        getClinicSession(ClinicId);
      }
    }
  }).finally(() => {
      isLoading.value = false; // End loading
    });
}
watchEffect(() => {
  if (clinicStartTime && clinicEndTime && clinicStartTime.value && clinicEndTime.value) {
    if (Array.isArray(weekdays.value)) {
      weekdays.value.forEach((day, index) => {
        const doctorStartTime = moment(day.start_time, 'HH:mm');
        const doctorEndTime = moment(day.end_time, 'HH:mm');
        const clinicStartTimes = moment(clinicStartTime.value[index], 'HH:mm');
        const clinicEndTimes = moment(clinicEndTime.value[index], 'HH:mm');

        delete day.startTimeError;
        delete day.endTimeError;
        if (doctorStartTime.isBefore(clinicStartTimes) || doctorStartTime.isAfter(clinicEndTimes)) {
          day.startTimeError = `Doctor start time for ${day.day} must be between clinic operating hours`;
        }

        if (doctorEndTime.isBefore(clinicStartTimes) || doctorEndTime.isAfter(clinicEndTimes)) {
          day.endTimeError = `Doctor end time for ${day.day} must be between clinic operating hours`
        }
      });
    }
  }
});
const hasTimeSlotErrors = computed(() => {
  return weekdays.value.some(day => day.startTimeError || day.endTimeError);
});

const addInputField = (day) => {
  day.breaks.push({ start_break: moment().set({ hour: 0, minute: 0, second: 0 }).format('HH:mm'), end_break: moment().set({ hour: 0, minute: 0, second: 0 }).format('HH:mm') })
}

const deleteInputField = (day, index) => {
  day.breaks.splice(index, 1)
}

const reset_datatable_close_offcanvas = (res) => {
  if (res.status) {
    window.successSnackbar(res.message)
    bootstrap.Offcanvas.getInstance('#session-form-offcanvas').hide()
    weekdays.value = defaultData();
  } else {
    window.errorSnackbar(res.message)
    errorMessages.value = res.all_message
  }
}

//Form Submit
const formSubmit = handleSubmit((values) => {
  IS_SUBMITED.value = true
  values.weekdays = weekdays.value

  if (hasTimeSlotErrors.value) {
    window.errorSnackbar('Please correct the errors before submitting.');
    IS_SUBMITED.value = false
    return;
  }
  values.clinic_id = clinic_id.value
  values.doctor_id = DoctorId.value
  values.weekdays.forEach(day => {
    day.is_holiday = clinicHolidays.value.includes(day.day) || day.is_holiday === 1 ? 1 : 0;
  });
  storeRequest({ url: STORE_URL, body: values }).then((res) => {
    if (res.status) {
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
