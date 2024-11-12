<template>
  <form @submit="formSubmit">
    <div class="offcanvas offcanvas-end" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel">
      <FormHeader :currentId="currentId" :editTitle="editTitle" :createTitle="createTitle"></FormHeader>
      <div class="offcanvas-body">
        <div class="row">
          <div class="col-md-6 form-group">
            <label class="form-label col-md-6">{{ $t('clinic.lbl_select_clinic') }} <span class="text-danger">*</span></label>
            <Multiselect id="clinic_id" v-model="clinic_id" :value="clinic_id" placeholder="Select Clinic" v-bind="singleSelectOption" :options="cliniclist.options"  @select="getDoctorList" class="form-group"></Multiselect>
            <span v-if="errorMessages['clinic_id']">
              <ul class="text-danger">
                <li v-for="err in errorMessages['clinic_id']" :key="err">{{ err }}</li>
              </ul>
            </span>
            <span class="text-danger">{{ errors['clinic_id'] }}</span>
          </div>

            <div class="col-md-6 form-group">
              <label class="form-label col-md-6">{{ $t('clinic.lbl_select_doctor') }} <span class="text-danger">*</span></label>
              <Multiselect id="doctor_id" v-model="doctor_id" :value="doctor_id" placeholder="Select Doctor" v-bind="singleSelectOption" :options="doctorlist.options" class="form-group"></Multiselect>
              <span v-if="errorMessages['doctor_id']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['doctor_id']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors['doctor_id'] }}</span>
            </div>

            <div class="col-md-6 form-group">
              <label class="form-label">{{ $t('clinic.lbl_select_service') }} <span class="text-danger">*</span></label>
              <Multiselect id="service_id" v-model="service_id" :value="service_id" placeholder="Select Service" v-bind="singleSelectOption" :options="countries.options" class="form-group"></Multiselect>
              <span v-if="errorMessages['service_id']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['service_id']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors['service_id'] }}</span>
            </div>


            <div class="col-md-6 form-group appointment_date">

                <label class="form-label" for="appointment_date">{{ $t('clinic.lbl_appointment_date') }} <span class="text-danger">*</span></label>
                <flat-pickr placeholder="Select Appointment Date" id="appointment_date" class="form-control" v-model="appointment_date" :value="appointment_date" :config="config"></flat-pickr>
                <span v-if="errorMessages['appointment_date']">
                  <ul class="text-danger">
                    <li v-for="err in errorMessages['appointment_date']" :key="err">{{ err }}</li>
                  </ul>
                </span>
                <span class="text-danger">{{ errors.appointment_date }}</span>
            </div>


            <div class="col-md-12 form-group">
                <div class="d-flex justify-content-between align-items-center col-md-6">
                  <label class="form-label" for="availble_slot">{{ $t('clinic.lbl_availble_slots') }} <span class="text-danger">*</span></label>
                </div>


                <span v-if="errorMessages['availble_slot']">
                  <ul class="text-danger">
                    <li v-for="err in errorMessages['availble_slot']" :key="err">{{ err }}</li>
                  </ul>
                </span>
                <span class="text-danger">{{ errors.availble_slot }}</span>
            </div>

            <div class="col-md-6 form-group">
              <label class="form-label">{{ $t('clinic.lbl_select_patient') }} <span class="text-danger">*</span></label>
              <Multiselect id="patient_id" v-model="patient_id" :value="patient_id" placeholder="Select Patient" v-bind="singleSelectOption" :options="countries.options" class="form-group"></Multiselect>
              <span v-if="errorMessages['patient_id']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['patient_id']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors['service_id'] }}</span>
            </div>

            <div class="form-group col-md-12">
              <div class="d-flex justify-content-between align-items-center">
                <label class="form-label" for="medical_report">{{ $t('clinic.lbl_medical_report') }} </label>
              </div>
              <input type="file" class="form-control" id="medical_report" ref="refInput" @change="fileUpload" accept=".jpeg, .jpg, .png, .gif, .pdf" />
              <span v-if="errorMessages['medical_report']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['medical_report']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors.medical_report }}</span>
            </div>

            <div v-for="field in customefield" :key="field.id">
              <FormElement v-model="custom_fields_data" :name="field.name" :label="field.label" :type="field.type" :required="field.required" :options="field.value" :field_id="field.id"></FormElement>
            </div>
        </div>
      </div>

      <FormFooter :IS_SUBMITED="IS_SUBMITED"></FormFooter>
    </div>
  </form>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useField, useForm } from 'vee-validate'
import { EDIT_URL, STORE_URL, UPDATE_URL, COUNTRY_URL, STATE_URL, CITY_URL, CLINIC_LIST,DOCTOR_LIST } from '../constant/clinic-appointment'
import InputField from '@/vue/components/form-elements/InputField.vue'
import { useModuleId, useRequest } from '@/helpers/hooks/useCrudOpration'
import { readFile } from '@/helpers/utilities'
import { VueTelInput } from 'vue3-tel-input'
import { useSelect } from '@/helpers/hooks/useSelect'
import * as yup from 'yup'
import FormHeader from '@/vue/components/form-elements/FormHeader.vue'
import FormFooter from '@/vue/components/form-elements/FormFooter.vue'
import FlatPickr from 'vue-flatpickr-component'

// props
const props = defineProps({
  createTitle: { type: String, default: '' },
  editTitle: { type: String, default: '' },
  customefield: { type: Array, default: () => [] },
  defaultImage: { type: String, default: 'https://dummyimage.com/600x300/cfcfcf/000000.png' }
})

// flatepicker

const config = ref({
  dateFormat: 'Y-m-d',
  static: true,
  minDate: 'today'
})

const ImageViewer = ref(null)
const profileInputRef = ref(null)
const fileUpload = async (e) => {
  let file = e.target.files[0]
  await readFile(file, (fileB64) => {
    ImageViewer.value = fileB64
    profileInputRef.value.value = ''
  })
  file_url.value = file
}

// Function to delete Images
const removeImage = ({ imageViewerBS64, changeFile }) => {
  imageViewerBS64.value = null
  changeFile.value = null
}

const { getRequest, storeRequest, updateRequest, listingRequest } = useRequest()

const singleSelectOption = ref({
  closeOnSelect: true,
  searchable: true
})

const handleInput = (phone, phoneObject) => {
  // Handle the input event
  if (phoneObject?.formatted) {
    contact_number.value = phoneObject.formatted
  }
}

// Edit Form Or Create Form
const currentId = useModuleId(() => {
  if (currentId.value > 0) {
    getRequest({ url: EDIT_URL, id: currentId.value }).then((res) => {
      if (res.status) {
        setFormData(res.data)
        getState(country.value)
        getCity(state.value)
      }
    })
  } else {
    setFormData(defaultData())
  }
})

// Default FORM DATA
const defaultData = () => {
  ImageViewer.value = props.defaultImage
  errorMessages.value = {}
  return {
    clinic_name: '',
    description: '',
    file_url: null,
    address: '',
    city: '',
    country: '',
    pincode: '',
    state: '',
    latitude: '',
    longitude: '',
    contact_number: '',
    status: 1
  }
}

const cliniclist = ref({ options: [], list: [] })

const getClinic = () => {
  useSelect({ url: CLINIC_LIST }, { value: 'id', label: 'clinic_name' }).then((data) => (cliniclist.value = data))
}

const doctorlist = ref({ options: [], list: [] })

const getDoctorList = (value) => {
  useSelect({ url: DOCTOR_LIST, data: value }, { value: 'id', label: 'doctor_name' }).then((data) => (doctorlist.value = data))
}






const countries = ref({ options: [], list: [] })

const getCountry = () => {
  useSelect({ url: COUNTRY_URL }, { value: 'id', label: 'name' }).then((data) => (countries.value = data))
}

const states = ref({ options: [], list: [] })

const getState = (value) => {
  useSelect({ url: STATE_URL, data: value }, { value: 'id', label: 'name' }).then((data) => (states.value = data))
}

const cities = ref({ options: [], list: [] })

const getCity = (value) => {
  useSelect({ url: CITY_URL, data: value }, { value: 'id', label: 'name' }).then((data) => (cities.value = data))
}

//  Reset Form
const setFormData = (data) => {
  ImageViewer.value = data.file_url || props.defaultImage
  resetForm({
    values: {
      clinic_name: data.clinic_name,
      description: data.description,
      address: data.address,
      file_url: data.file_url,
      city: data.city,
      state: data.state,
      country: data.country,
      pincode: data.pincode,
      latitude: data.latitude,
      longitude: data.longitude,
      contact_number: data.contact_number,
      status: data.status
    }
  })
}

const reset_datatable_close_offcanvas = (res) => {
  IS_SUBMITED.value = false
  if (res.status) {
    window.successSnackbar(res.message)
    renderedDataTable.ajax.reload(null, false)
    bootstrap.Offcanvas.getInstance('#form-offcanvas').hide()
    setFormData(defaultData())
    removeImage({ imageViewerBS64: ImageViewer, changeFile: file_url })
  } else {
    window.errorSnackbar(res.message)
    errorMessages.value = res.all_message
  }
}

// Validations
const validationSchema = yup.object({
  clinic_name: yup.string().required('Clinic name is required'),
  description: yup.string().required('Description is required'),
  address: yup.string().required('Address is required'),
  city: yup.string().required('City is required'),
  state: yup.string().required('State is required'),
  country: yup.string().required('Country is required'),
  pincode: yup.string().required('Pincode is required'),
  contact_number: yup.string().required('Contact number is required')
})

const { handleSubmit, errors, resetForm } = useForm({
  validationSchema
})

const { value: clinic_name } = useField('clinic_name')
const { value: description } = useField('description')
const { value: address } = useField('address')
const { value: city } = useField('city')
const { value: state } = useField('state')
const { value: country } = useField('country')
const { value: pincode } = useField('pincode')
const { value: contact_number } = useField('contact_number')
const { value: file_url } = useField('file_url')
const { value: latitude } = useField('latitude')
const { value: longitude } = useField('longitude')
const { value: status } = useField('status')
const errorMessages = ref({})

onMounted(() => {
  setFormData(defaultData())
  getCountry()
  getClinic()
})

const IS_SUBMITED = ref(false)
const formSubmit = handleSubmit((values) => {
  if (IS_SUBMITED.value) return false

  if (props.customefield > 0) {
    values.custom_fields_data = JSON.stringify(values.custom_fields_data)
  }

  if (currentId.value > 0) {
    console.log(values)

    updateRequest({ url: UPDATE_URL, id: currentId.value, body: values, type: 'file' }).then((res) => reset_datatable_close_offcanvas(res))
  } else {
    storeRequest({ url: STORE_URL, body: values, type: 'file' }).then((res) => reset_datatable_close_offcanvas(res))
  }
})

const removeLogo = () => removeImage({ imageViewerBS64: ImageViewer, changeFile: file_url })
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
</style>
