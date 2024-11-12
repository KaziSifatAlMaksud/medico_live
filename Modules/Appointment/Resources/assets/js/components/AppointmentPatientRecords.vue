<template>
  <form @submit="formSubmit">
<h4>{{ $t('messages.soap') }}</h4>
    <p class="mt-2 mb-4"><span class="fw-bold">{{ $t('messages.note') }}: </span>{{ $t('messages.soap_description') }}</p>
      <div class="row">
            <div class="mb-3">
                <label for="subjective" class="form-label">{{ $t('messages.subjective') }} <span class="text-danger">*</span></label>
                <textarea id="subjective" rows="4" cols="12" class="form-control" v-model="subjective"></textarea>
                <span v-if="errorMessages['subjective']">
                  <ul class="text-danger">
                    <li v-for="err in errorMessages['subjective']" :key="err">{{ err }}</li>
                  </ul>
                </span>
                <span class="text-danger">{{ errors.subjective }}</span>
            </div>
            <div class="mb-3">
                <label for="objecvtive" class="form-label">{{ $t('messages.objecvtive') }} <span class="text-danger">*</span></label>
                <textarea id="objecvtive" rows="4" cols="12" class="form-control" v-model="objective"></textarea>
                <span v-if="errorMessages['objective']">
                  <ul class="text-danger">
                    <li v-for="err in errorMessages['objective']" :key="err">{{ err }}</li>
                  </ul>
                </span>
                <span class="text-danger">{{ errors.objective }}</span>
            </div>
            <div class="mb-3">
                <label for="assessment" class="form-label">{{ $t('messages.assesment') }} <span class="text-danger">*</span></label>
                <textarea id="assessment" rows="4" cols="12" class="form-control" v-model="assessment"></textarea>
                <span v-if="errorMessages['assessment']">
                  <ul class="text-danger">
                    <li v-for="err in errorMessages['assessment']" :key="err">{{ err }}</li>
                  </ul>
                </span>
                <span class="text-danger">{{ errors.assessment }}</span>
            </div>
            <div class="mb-3">
                <label for="plan" class="form-label">{{ $t('messages.plan') }} <span class="text-danger">*</span></label>
                <textarea id="plan" rows="4" cols="12" class="form-control" v-model="plan"></textarea>
                <span v-if="errorMessages['plan']">
                  <ul class="text-danger">
                    <li v-for="err in errorMessages['plan']" :key="err">{{ err }}</li>
                  </ul>
                </span>
                <span class="text-danger">{{ errors.plan }}</span>
            </div>
      </div>

      <!-- <button class="btn btn-primary" v-if="enable_bodychart==1" @click.prevent="bodychart()">{{ $t('messages.body_chart') }}</button> -->

      <div class="d-grid d-md-flex gap-3 p-3">          
          <button class="btn btn-outline-primary d-block" type="button" data-bs-dismiss="offcanvas"  @click.prevent="closeoffcanvas()">
            <i class="fa-solid fa-angles-left"></i>
            {{ $t('messages.close') }}
          </button>
          <button :disabled="isDisabled" class="btn btn-primary" name="submit">
            <template v-if="IS_SUBMITED">
              <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                {{ $t('appointment.loading') }}
            </template>
            <template v-else> <i class="fa-solid fa-floppy-disk"></i> {{ $t('messages.save') }}</template>
          </button>
        </div>

  </form>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useField, useForm } from 'vee-validate'
import {  APPOINTMNET_RECORD_STORE_URL,APPOINTMNET_RECORD_EDIT_URL} from '../constant/clinic-appointment'
import { useRequest } from '@/helpers/hooks/useCrudOpration'
import * as yup from 'yup'

// props
const props = defineProps({
  appointment_id: { type: Number, default: 0 },
  encounter_id: { type: Number, default: 0 },
  patient_id: { type: Number, default: 0 },
  createTitle: { type: String, default: '' },
  editTitle: { type: String, default: '' },
  customefield: { type: Array, default: () => [] },
  defaultImage: { type: String, default: 'https://dummyimage.com/600x300/cfcfcf/000000.png' }
})


const { getRequest, updateRequest} = useRequest()


if(props.encounter_id>0){
  getRequest({ url: APPOINTMNET_RECORD_EDIT_URL, id: props.encounter_id }).then((res) => {
      if (res.status) {
        setFormData(res.data)
      }else{
        setFormData(defaultData())
      }
    })
  }

  onMounted(() => {
  setFormData(defaultData())
})

// Default FORM DATA
const defaultData = () => {
  errorMessages.value = {}
  return {
    subjective:'',
    objective:'',
    assessment:'',
    plan:'',
  }
}


const IS_SUBMITED = ref(false)
//  Reset Form
const setFormData = (data) => {
  resetForm({
    values: {
    subjective:data.subjective,
    objective:data.objective,
    assessment:data.assessment,
    plan:data.plan,
    }
  })
}

  const validationSchema = yup.object({
    subjective: yup.string().required('Subjective is a required field' ),
    objective: yup.string().required('Objective is a required field'),
    assessment: yup.string().required('Assessment is a required field'),
    plan: yup.string().required('Plan is  a required field')
})

const { handleSubmit, errors, resetForm } = useForm({
  validationSchema
})


const { value: subjective } = useField('subjective')
const { value: objective } = useField('objective')
const { value: assessment } = useField('assessment')
const { value: plan } = useField('plan')

const errorMessages = ref({})

function bodychart(){
window.location.href = `bodychart/${props.encounter_id}`;
}

function closeoffcanvas(){
window.location.href = `appointments`;
}



const formSubmit = handleSubmit((values) => {
  IS_SUBMITED.value = true
   values.appointment_id=props.appointment_id
   values.encounter_id=props.encounter_id
   values.patient_id=props.patient_id
   updateRequest({ url: APPOINTMNET_RECORD_STORE_URL,  id:props.encounter_id, body: values,type: 'file' }).then((res) => {
  
      IS_SUBMITED.value = false
      if (res.status) {
        window.location.href = `appointments`;
      }else{
        setFormData(defaultData())
      }
    })
})

const enable_bodychart = window.bodyChartEnabled;


</script>

<style scoped>
@media only screen and (min-width: 768px) {
  .offcanvas {
    width: 80%;
  }
}

@media only screen and (min-width: 1280px) {
  .offcanvas {
    width: 60%;
  }
}

.selected_slot {
  background-color: #3498db;
  color: #fff;
}
</style>
