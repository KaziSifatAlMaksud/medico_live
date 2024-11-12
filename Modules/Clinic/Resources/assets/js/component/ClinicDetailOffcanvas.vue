<template>
    <div class="offcanvas offcanvas-end offcanvas-w-40 " tabindex="-1" id="clinicDetails-offcanvas" aria-labelledby="form-offcanvasLabel">
        <FormHeader :currentId="currentId" :editTitle="editTitle" :createTitle="$t('clinic.clinic_details')"></FormHeader>
        <div class="offcanvas-body">
            <h5>{{ $t('clinic.about_clinic') }}</h5>
            <div class="card">
            <div class="card-body">
              <div class="d-flex gap-3 align-items-start">
                <img :src="ImageViewer || defaultImage" class="img-fluid avatar avatar-80 avatar-rounded mb-2"/>
                    <div class="pt-2">
                        <h4>{{ clinic.name }}</h4>
                        <div class="d-flex gap-5">
                            <p class="d-flex align-items-center gap-2"><i class="ph ph-envelope text-dark"></i><span class="text-secondary border-bottom border-secondary">{{clinic.email }}</span></p>
                            <p class="d-flex align-items-center gap-2"><i class="ph ph-phone text-dark"></i><span class="text-primary border-bottom border-primary">{{ clinic.contact_number }}</span></p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <p class="d-flex align-items-center gap-2"><i class="ph ph-map-pin text-dark"></i>{{clinic.address }}</p>
                        </div>
                        <div class="d-flex gap-5">
                            <p class="m-0">{{ $t('clinic.lbl_postal_code') }}: <span class="text-dark">{{ clinic.pincode }}</span></p>
                            <p class="m-0">{{ $t('clinic.lbl_city') }}: <span class="text-dark">{{clinic.city }}</span></p>
                            <p class="m-0">{{ $t('clinic.lbl_state') }}: <span class="text-dark">{{clinic.state }}</span></p>
                            <p class="m-0">{{ $t('clinic.lbl_country') }}: <span class="text-dark">{{clinic.country }}</span></p>
                        </div>
                    </div>
            </div>
            <div class="my-5">
                 <div v-if="clinic.description"> 
                    <h6 class="mb-1">{{ $t('clinic.lbl_description') }}:</h6>
                    <p>{{ clinic.description }}</p>
                 </div>
                 </div>
                 <div class="d-flex gap-5">
                    <p class="m-0">{{ $t('clinic.speciality') }}: <span class="text-dark">{{ clinic.system_service_category }}</span></p>
                    <p class="m-0" v-if="clinic.time_slot">{{ $t('clinic.time_slot') }}: <span class="text-dark">{{ clinic.time_slot }} Min.</span></p>
                 </div>
            </div>
          </div>
          <div v-if="clinicSessions && clinicSessions.open_days && clinicSessions.open_days.length && clinicSessions.close_days && clinicSessions.close_days.length">
            <div class="d-flex justify-content-between align-items-center mt-5">
                <h5>{{ $t('clinic.sessions') }}</h5>
            </div>
            <div class="card">
                <div class="card-body">
                    <div v-for='openDay in clinicSessions.open_days' :key="openDay.day">
                        <p class="d-flex justify-content-between gap-3 border-bottom pb-3 mb-3">
                            <span>{{ formatDayRange(openDay.day) }}</span>
                            <span v-if="openDay.start_time && openDay.end_time" class="text-end text-dark">
                            {{ formatTime(openDay.start_time) }} - {{ formatTime(openDay.end_time) }}
                            </span>
                            <span v-else>
                                {{ $t('clinic.closed') }}
                            </span>
                        </p>
                    </div>

                    <div v-for='closeDay in clinicSessions.close_days' :key="closeDay">
                        <p class="d-flex justify-content-between">
                            {{ closeDay }}
                            <span class="text-danger">
                                {{ $t('clinic.closed') }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
          </div>

        </div>
        
</div>
</template>

<script setup>
 import { ref, onMounted ,reactive } from 'vue'
 import { CLINIC_URL} from '../constant/clinic'
 import { useModuleId, useRequest } from '@/helpers/hooks/useCrudOpration'
 import { useForm, useField } from 'vee-validate'
 import FormHeader from '@/vue/components/form-elements/FormHeader.vue'
 import moment from 'moment'
 const CURRENCY_SYMBOL = ref(window.defaultCurrencySymbol)

 const defaultImage='https://dummyimage.com/600x300/cfcfcf/000000.png';
 const { getRequest } = useRequest()

 const ImageViewer = ref(null)
 const clinic = ref({});
 const clinicSessions = ref({});

 const ClinicId = useModuleId(() => {

 getRequest({ url: CLINIC_URL                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             , id: ClinicId.value }).then((res) => {
    if (res.status) {
        clinic.value = res.data;
        clinicSessions.value = clinic.value.clinic_sessions
        console.log(clinicSessions)
        ImageViewer.value = clinic.value.file_url
    }
    })
  }, 'clinic-details')

 const formatDayRange = (days) =>{
    const formattedDays = days.split(',').map(day => day.trim());
    if (formattedDays.length === 2) {
      return formattedDays.join(' - ');
    }
    if (formattedDays.length > 2) {
      return `${formattedDays[0]} - ${formattedDays[formattedDays.length - 1]}`;
    }
    return formattedDays[0];
 }

 const formatTime = (time) =>{
    return new Date(`2000-01-01T${time}`).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }).replace(/^0/, '');
 }
</script>
