<template>
    <div class="offcanvas offcanvas-end offcanvas-w-40 " tabindex="-1" id="doctorDetails-offcanvas" aria-labelledby="form-offcanvasLabel">
        <FormHeader :currentId="currentId" :editTitle="editTitle" :createTitle="$t('clinic.details')"></FormHeader>
        <div class="offcanvas-body">
            <h5>{{ $t('clinic.about_doctor') }}</h5>
            <div class="card">
                <div class="card-body p-5">
                    <div class="d-flex gap-3 align-items-center gap-4 flex-wrap">
                        <img :src="ImageViewer || defaultImage" class="img-fluid avatar avatar-80 avatar-rounded"/>
                        <div>
                            <h5 class="mb-3">{{ doctor.full_name }}</h5>
                            <div class="d-flex align-items-center flex-wrap gap-3">
                                <div class="d-inline-flex align-items-center gap-2 me-sm-2 me-0">
                                    <i class="ph ph-envelope mb-0 h5 align-middle"></i>
                                    <a :href="`mailto:${doctor.email}`" class="text-decoration-underline">{{ doctor.email }}</a>
                                </div>
                                <div class="d-inline-flex align-items-center gap-2">
                                    <i class="ph ph-phone mb-0 h5 align-middle"></i>
                                   <a :href="`tel:${doctor.mobile}`" class="text-decoration-underline">{{ doctor.mobile }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="my-5" v-if="doctor.profile && doctor.profile.about_self">
                        <h6>{{ $t('clinic.about') }}:</h6>
                        <p class="mb-0">{{ doctor.profile.about_self }}</p>
                    </div>
                    <div class="d-flex justify-content-between gap-2 flex-wrap mt-3">
                        <div>
                            <p class="mb-1">{{ $t('clinic.total_appointment') }}</p>
                            <h6 class="mb-0">{{ doctor.total_appointment }}</h6>
                        </div>
                        <div>
                            <p class="mb-1">{{ $t('clinic.expertize_in') }}</p>
                            <h6>{{doctor.specialization}}</h6>
                        </div>
                        <div>
                            <p class="mb-1">{{ $t('clinic.available_session_count') }}</p>
                            <h6 class="mb-0">{{ doctor.total_sessions }}</h6>
                        </div>
                        <div>
                            <p class="mb-1">{{ $t('clinic.experience') }}</p>
                            <h6 class="mb-0">{{ doctor.experience }}</h6>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2 gap-3">
                <h5 class="mb-0">{{ $t('clinic.services') }}</h5>
                <div v-if="doctor.doctor_service && doctor.doctor_service.length > 5">
                    <a @click.prevent="service_viewall()" class="text-secondary" role="button">{{ $t('clinic.view_all') }}</a>
                </div>
            </div>
            <div v-if="doctor.doctor_service && doctor.doctor_service.length > 0"> 
                <div class="card">
                    <div class="card-body p-5">
                        
                        <ul class="list-inline m-0 p-0">    
                            <li class="mb-3" v-for="(service, index) in doctor.doctor_service" :key="service.id">
                                <div v-if="index < 6" class="px-3 py-4 bg-body rounded-3">
                                    <div class="d-flex align-items-sm-center align-items-start justify-content-between flex-sm-row flex-column gap-3">
                                        <div class="flex-grow-1">
                                            <h5 class="mb-2">{{ service.clinicservice.name }}</h5>
                                            <div class="d-flex gap-3 flex-wrap">
                                                <p class="mb-0">{{ $t('clinic.total_appointments_done') }}: <span class="font-title">{{ getServiceAppointmentsCount(service, doctor.id) }}</span></p>
                                                <p class="mb-0">{{ $t('clinic.lbl_clinic_name') }}: <span class="font-title">{{ getCenterName(service) }}</span></p>
                                            </div>
                                        </div>
                                        <h3 class="mb-0 text-primary">$ {{ service.clinicservice.charges }}</h3>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div v-else>
                <div class="card">
                    <div class="card-body">
                        <p class="text-center">{{ $t('clinic.data_not_found') }}</p>
                    </div>
                </div>  
            </div>
            

            <div class="d-flex justify-content-between align-items-center mb-2 gap-3">
                <h5 class="mb-0">{{ $t('clinic.reviews') }}</h5>
                <div v-if="doctor.rating && doctor.rating.length > 0">
                    <a @click.prevent="rating_viewall()" class="text-secondary">{{ $t('clinic.view_all') }}</a>
                </div>
            </div>
            <div v-if="doctor.rating && doctor.rating.length > 0">
                <div v-for="(rating, index) in doctor.rating" :key="rating.id" class="card mb-3">
                    <div class="card-body p-3" v-if="index < 5">
                        <div class="d-flex align-items-sm-center align-items-start justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3 flex-sm-nowrap flex-wrap">
                                <b-badge class="rounded-4" variant="body">
                                    <div class="d-flex gap-1 align-items-center">
                                        <span class="text-warning align-baseline">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10" fill="none">
                                                <path d="M5.48815 0.322955L6.60139 2.67784C6.68341 2.84845 6.83994 2.96693 7.02048 2.99326L9.52102 3.37661C9.66705 3.3982 9.79958 3.4793 9.8891 3.60304C9.97761 3.72521 10.0156 3.88003 9.99412 4.03221C9.97661 4.15859 9.9201 4.27549 9.83358 4.36501L8.02169 6.21384C7.88917 6.34286 7.82915 6.53401 7.86116 6.72095L8.30726 9.32016C8.35477 9.634 8.15722 9.92995 7.86116 9.98945C7.73913 10.01 7.61411 9.9884 7.50408 9.92942L5.2736 8.70617C5.10807 8.61823 4.91253 8.61823 4.74699 8.70617L2.51651 9.92942C2.24245 10.0827 1.90288 9.97839 1.75035 9.69404C1.69384 9.58082 1.67383 9.45181 1.69234 9.32595L2.13843 6.72621C2.17044 6.5398 2.10993 6.3476 1.9779 6.21858L0.166008 4.3708C-0.0495379 4.15174 -0.0560393 3.79103 0.151505 3.56408C0.156006 3.55934 0.161007 3.55407 0.166008 3.54881C0.252027 3.45665 0.365051 3.3982 0.487077 3.38293L2.98761 2.99905C3.16765 2.9722 3.32419 2.85477 3.4067 2.6831L4.47993 0.322955C4.57545 0.120747 4.7735 -0.00510678 4.98854 0.000159056H5.05556C5.2421 0.0238553 5.40463 0.145496 5.48815 0.322955Z" fill="currentColor"/>
                                            </svg>
                                        </span>
                                        <span class="mb-0 text-primary lh-sm fs-12 fw-bold">{{ rating.rating }}</span>
                                    </div>
                                </b-badge> 
                                <h5 class="mb-0">{{ rating.title }}</h5>  
                            </div>
                            <div class="flex-shrink-0">
                                <span class="fs-12 fw-semibold">
                                    {{ formatTimeAgo(rating.updated_at) }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="d-inline-flex align-items-center gap-1">
                                <h6 class="mb-0 lh-1">By {{ rating.user.first_name }} {{ rating.user.last_name }}</h6>
                                <span class="text-success lh-1 align-middle">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <g>
                                            <path d="M6.13223 0.427846C6.41962 -0.142615 7.23403 -0.142615 7.52142 0.427846L8.29321 1.95973C8.4665 2.30381 8.86565 2.46914 9.23152 2.3484L10.8604 1.8109C11.467 1.61074 12.043 2.18663 11.8428 2.79322L11.3052 4.42214C11.1845 4.78801 11.3498 5.18713 11.6939 5.36047L13.2258 6.13223C13.7963 6.41962 13.7963 7.23403 13.2258 7.52142L11.6939 8.29321C11.3498 8.4665 11.1845 8.86565 11.3052 9.23152L11.8428 10.8604C12.043 11.467 11.467 12.043 10.8604 11.8428L9.23152 11.3052C8.86565 11.1845 8.4665 11.3498 8.29321 11.6939L7.52142 13.2258C7.23403 13.7963 6.41962 13.7963 6.13223 13.2258L5.36047 11.6939C5.18713 11.3498 4.78801 11.1845 4.42214 11.3052L2.79322 11.8428C2.18663 12.043 1.61074 11.467 1.8109 10.8604L2.3484 9.23152C2.46914 8.86565 2.30381 8.4665 1.95973 8.29321L0.427846 7.52142C-0.142615 7.23403 -0.142615 6.41962 0.427846 6.13223L1.95973 5.36047C2.30381 5.18713 2.46914 4.78801 2.3484 4.42214L1.8109 2.79322C1.61074 2.18663 2.18663 1.61074 2.79322 1.8109L4.42214 2.3484C4.78801 2.46914 5.18713 2.30381 5.36047 1.95973L6.13223 0.427846Z" fill="currentColor"/>
                                            <path d="M4.6665 6.99989L6.22206 8.55545L9.33317 5.44434" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </g>
                                        <defs>
                                            <clipPath>
                                                <rect width="14" height="14" fill="white"/>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                </span>
                            </div>
                            <p class="mt-2 mb-0 fs-12">{{ rating.review_msg }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <div class="card">
                    <div class="card-body">
                        <p class="text-center">{{ $t('clinic.data_not_found') }}</p>
                    </div>
                </div>  
            </div>
     
        </div>
        
    </div>
</template>

<script setup>
 import { ref, onMounted ,reactive } from 'vue'
 import { DOCTOR_URL} from '../constant/doctor'
 import { useModuleId, useRequest } from '@/helpers/hooks/useCrudOpration'
 import { useForm, useField } from 'vee-validate'
 import FormHeader from '@/vue/components/form-elements/FormHeader.vue'
 import moment from 'moment'
 const CURRENCY_SYMBOL = ref(window.defaultCurrencySymbol)

 const defaultImage='https://dummyimage.com/600x300/cfcfcf/000000.png';
 const { getRequest } = useRequest()

 const ImageViewer = ref(null)
 const doctor=ref({});

 const baseUrl = document.querySelector('meta[name="baseUrl"]').getAttribute('content');

 function service_viewall(){
    console.log(doctor.value.id)
  if (doctor && doctor.value && doctor.value.id) {
    window.location.href = `services?doctor_id=${doctor.value.id}`;
  } else {
    console.error('Doctor ID is not available');
  }
 }
 function rating_viewall(){
    if (doctor && doctor.value && doctor.value.id) {
    window.location.href = `doctors-review?doctor_id=${doctor.value.id}`;
    } else {
        console.error('Doctor ID is not available');
    }
 }

 const ClinicId = useModuleId(() => {

getRequest({ url: DOCTOR_URL, id: ClinicId.value }).then((res) => {
    if (res.status) {
        doctor.value = res.data;
        ImageViewer.value = doctor.value.profile_image
    }
    })
  }, 'doctor-details')

const getCenterName = (service) => {
  if (service.clinicservice && service.clinicservice.clinic_service_mapping) {
    const mapping = service.clinicservice.clinic_service_mapping;
    const centerNames = mapping.map(item => item.center && item.center.name).filter(Boolean);
    return centerNames.join(', ');
  }
  return '';
}

const getServiceAppointmentsCount = (service, doctorId) => {
    if (service.clinicservice && service.clinicservice.appointment_service && service.clinicservice.appointment_service.length > 0) {

        return service.clinicservice.appointment_service.reduce((count, appointment) => {
            if (appointment.doctor_id === doctorId && appointment.status === 'checkout') {
                return count + 1;
            }
            return count;
        }, 0);
    }
    return 0;
}

const formatTimeAgo = (timestamp) => {
      const timeDifference = new Date() - new Date(timestamp);
      if (timeDifference < 60 * 60 * 1000) {
        return Math.floor(timeDifference / (1000 * 60)) + 'm Ago';
      } else if (timeDifference < 24 * 60 * 60 * 1000) {
        return Math.floor(timeDifference / (1000 * 60 * 60)) + 'h Ago';
      } else {
        return Math.floor(timeDifference / (24 * 60 * 60 * 1000)) + 'd Ago';
      }
}

</script>
