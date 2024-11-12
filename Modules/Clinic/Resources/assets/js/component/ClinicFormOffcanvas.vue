<template>
  <form @submit="formSubmit">
    <div class="offcanvas offcanvas-end offcanvas-w-40" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel">
      <FormHeader :currentId="currentId" :editTitle="editTitle" :createTitle="createTitle"></FormHeader>
      <div class="offcanvas-body">
        <div class="row">
          <div class="col-md-6">
            <label class="form-label">{{ $t('clinic.clinic_image') }}</label>
            <imageComponent :ImageViewer="image_url" v-model="file_url" />
            <span class="text-danger">{{ errors.file_url }}</span>
          </div>

          <div class="col-md-6">
            <label class="form-label">{{ $t('clinic.lbl_name') }} <span class="text-danger">*</span></label>

            <InputField :is-required="true" :label="$t('clinic.lbl_name')" :icon="'<i class=\'ph ph-hospital\'></i>'" :placeholder="$t('clinic.lbl_name')" v-model="name" :error-message="errors.name" :error-messages="errorMessages['name']"></InputField>
            <div class="form-group">
              <label class="form-label" for="description">{{ $t('clinic.lbl_description') }}</label>
              <textarea class="form-control placeholder-red" v-model="description" id="description" :placeholder="$t('clinic.lbl_description')"></textarea>
              <span v-if="errorMessages['description']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['description']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors.description }}</span>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ $t('clinic.lbl_Email') }} <span class="text-danger">*</span></label>
            <InputField :is-required="true" :label="$t('clinic.lbl_Email')" :icon="'<i class=\'ph ph-envelope\'></i>'" :placeholder="$t('clinic.lbl_Email')" v-model="email" :error-message="errors['email']" :error-messages="errorMessages['email']"></InputField>
          </div>
          <div class="col-md-6">
            <label class="form-label"> {{ $t('clinic.lbl_contact_number') }} <span class="text-danger">*</span> </label>
            <vue-tel-input type="number" :value="contact_number" @input="handleInput" v-bind="{ mode: 'international', maxLen: 15 }" :icon="'<i class=\'ph ph-phone\'></i>'"></vue-tel-input>
            <span class="text-danger">{{ errors['contact_number'] }}</span>
          </div>
          <div class="col-md-6" v-if="enable_multi_vendor() == 1 && (role() === 'admin' || role() === 'demo_admin')">
            <div class="form-group">
              <label class="form-label" for="vendor_id">{{ $t('clinic.clinic_admin') }} </label>
              <Multiselect class="form-group" v-model="vendor_id" :value="vendor_id" :options="vendors.options" v-bind="singleSelectOption" :placeholder="$t('clinic.clinic_admin')" id="vendor_id"></Multiselect>
              <span v-if="errorMessages['vendor_id']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['vendor_id']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors.vendor_id }}</span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label" for="system_service_category">{{ $t('clinic.speciality') }}</label>
              <Multiselect class="form-group" v-model="system_service_category" :value="system_service_category" :options="systemservicecategory.options" v-bind="singleSelectDataOption" @select="selectSpeciality" :placeholder="$t('clinic.speciality')" id="system_service_category"></Multiselect>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label">{{ $t('clinic.lbl_time_slot') }} <span class="text-danger">*</span></label>
              <Multiselect id="time_slot" v-model="time_slot" :value="time_slot" v-bind="slots_data" class="form-group" :placeholder="$t('clinic.lbl_time_slot')"></Multiselect>
              <span v-if="errorMessages['time_slot']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['time_slot']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors.time_slot }}</span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <div class="d-flex align-items-center justify-content-between gap-3 form-control">
                <label class="form-label m-0" for="category-status">{{ $t('clinic.lbl_status') }}</label>
                <div class="form-check form-switch">
                  <input class="form-check-input" :value="status" :checked="status" name="status" id="category-status" type="checkbox" v-model="status" />
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-12 my-3">
            <h3>{{$t('clinic.other_detail')}}</h3>
          </div>
          <!-- <div class="col-md-4 form-group"> -->
          <div class="col-md-12">
            <div class="form-group">
              <label class="form-label" for="address">{{ $t('clinic.lbl_address') }} <span class="text-danger">*</span></label>
              <textarea class="form-control" v-model="address" id="address" :placeholder="$t('clinic.lbl_address')"></textarea>
              <span v-if="errorMessages['address']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['address']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors.address }}</span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label class="form-label">{{ $t('clinic.lbl_country') }} <span class="text-danger">*</span></label>
              <Multiselect id="country-list" v-model="country" :value="country" v-bind="singleSelectOption" :options="countries.options" @select="getState" class="form-group" :placeholder="$t('clinic.lbl_country')"></Multiselect>
              <span v-if="errorMessages['country']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['country']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors['country'] }}</span>
            </div>
          </div>
          <div class="col-md-4 form-group">
            <label class="form-label">{{ $t('clinic.lbl_state') }} <span class="text-danger">*</span></label>
            <Multiselect id="state-list" v-model="state" :value="state" v-bind="singleSelectOption" :options="states.options" @select="getCity" class="form-group" :placeholder="$t('clinic.lbl_state')"></Multiselect>
            <span v-if="errorMessages['state']">
              <ul class="text-danger">
                <li v-for="err in errorMessages['state']" :key="err">{{ err }}</li>
              </ul>
            </span>
            <span class="text-danger">{{ errors['state'] }}</span>
          </div>
          <div class="col-md-4 form-group">
            <label class="form-label">{{ $t('clinic.lbl_city') }} <span class="text-danger">*</span></label>
            <Multiselect id="city-list" v-model="city" :value="city" v-bind="singleSelectOption" :options="cities.options" class="form-group" :placeholder="$t('clinic.lbl_city')"></Multiselect>
            <span v-if="errorMessages['city']">
              <ul class="text-danger">
                <li v-for="err in errorMessages['city']" :key="err">{{ err }}</li>
              </ul>
            </span>
            <span class="text-danger">{{ errors['city'] }}</span>
          </div>
          <div class="col-md-4">
            <label class="form-label m-0" for="category-status">{{ $t('clinic.lbl_postal_code') }} </label>
            <InputField type="text" :is-required="true" :label="$t('clinic.lbl_postal_code')" :placeholder="$t('clinic.lbl_postal_code')" v-model="pincode" :error-message="errors['pincode']" :error-messages="errorMessages['pincode']" :icon="'<i class=\'ph ph-map-pin-area\'></i>'"></InputField>
          </div>
          <div class="col-md-4">
            <label class="form-label m-0" for="category-status">{{ $t('clinic.lbl_lat') }}</label>
            <InputField :is-required="false" :label="$t('clinic.lbl_lat')" :placeholder="$t('clinic.lbl_lat')" v-model="latitude" :error-message="errors['latitude']" :error-messages="errorMessages['latitude']" :icon="'<i class=\'ph ph-globe\'></i>'"></InputField>
          </div>
          <div class="col-md-4">
            <label class="form-label m-0" for="category-status">{{ $t('clinic.lbl_long') }}</label>
            <InputField :is-required="false" :label="$t('clinic.lbl_long')" :placeholder="$t('clinic.lbl_long')" v-model="longitude" :error-message="errors['longitude']" :error-messages="errorMessages['longitude']" :icon="'<i class=\'ph ph-globe\'></i>'"></InputField>
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
import * as yup from 'yup'
import { useField, useForm } from 'vee-validate'
import { EDIT_URL, STORE_URL, UPDATE_URL, COUNTRY_URL, STATE_URL, CITY_URL, CLINIC_CATEGORY, VENDOR_LIST, SAVE_SPECIALITY } from '../constant/clinic'
import { useModuleId, useRequest } from '@/helpers/hooks/useCrudOpration'
import { VueTelInput } from 'vue3-tel-input'
import { useSelect } from '@/helpers/hooks/useSelect'
import InputField from '@/vue/components/form-elements/InputField.vue'
import FormHeader from '@/vue/components/form-elements/FormHeader.vue'
import FormFooter from '@/vue/components/form-elements/FormFooter.vue'
import imageComponent from '@/vue/components/form-elements/imageComponent.vue'

// props
const props = defineProps({
  createTitle: { type: String, default: '' },
  editTitle: { type: String, default: '' },
  customefield: { type: Array, default: () => [] }
})
const role = () => {
  return window.auth_role[0]
}

const enable_multi_vendor = () => {
  return window.multiVendorEnabled
}

const { getRequest, storeRequest, updateRequest, listingRequest } = useRequest()

const singleSelectOption = ref({
  closeOnSelect: true,
  searchable: true
})

const singleSelectDataOption = ref({
  closeOnSelect: true,
  searchable: true,
  createOption: true
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
        getClinicCategory(res.data.system_service_category)
        setFormData(res.data)
        getState(country.value)
        getCity(state.value)
      }
    })
  } else {
    setFormData(defaultData())
  }
})

const slots_data = ref({
  searchable: true,
  options: [
    { label: 5, value: 5 },
    { label: 10, value: 10 },
    { label: 15, value: 15 },
    { label: 20, value: 20 },
    { label: 25, value: 25 },
    { label: 30, value: 30 },
    { label: 35, value: 35 },
    { label: 40, value: 40 },
    { label: 45, value: 45 },
    { label: 55, value: 55 },
    { label: 60, value: 60 }
  ],
  closeOnSelect: true,
  createOption: false
})

const vendors = ref({ options: [], list: [] })
const getVendorList = () => {
  useSelect({ url: VENDOR_LIST }, { value: 'id', label: 'name' }).then((data) => (vendors.value = data))
}

const systemservicecategory = ref({ options: [], list: [] })
const getClinicCategory = (value) => {
  useSelect({ url: CLINIC_CATEGORY, data: { id: value } }, { value: 'name', label: 'name' }).then((data) => (systemservicecategory.value = data))
}

// Default FORM DATA
const defaultData = () => {
  errorMessages.value = {}
  return {
    name: '',
    description: '',
    address: '',
    city: '',
    country: '',
    pincode: '',
    state: '',
    latitude: '',
    longitude: '',
    contact_number: '',
    email: '',
    time_slot: '',
    system_service_category: '',
    vendor_id: '',
    status: 1,
    file_url: []
  }
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

const values = ref([])
const selectSpeciality = (value) => {
  values.value = { name: value }
  storeRequest({ url: SAVE_SPECIALITY, body: values.value }).then((res) => {
  })
}
const image_url = ref()
//  Reset Form
const setFormData = (data) => {
  image_url.value = data.file_url
  resetForm({
    values: {
      name: data.name,
      description: data.description,
      address: data.address,
      city: data.city,
      state: data.state,
      country: data.country,
      pincode: data.pincode,
      latitude: data.latitude,
      longitude: data.longitude,
      email: data.email,
      time_slot: data.time_slot,
      contact_number: data.contact_number,
      system_service_category: data.system_service_category,
      vendor_id: data.vendor_id,
      status: data.status,
      file_url: data.file_url
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
  } else {
    window.errorSnackbar(res.message)
    errorMessages.value = res.all_message
  }
}

const EMAIL_REGX = /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/
const numberRegex = /^\d+$/

// Validations
const validationSchema = yup.object({
  name: yup.string().required('Name is required'),
  address: yup.string().required('Address is required'),
  // pincode: yup
  //   .string()
  //   .required('Postal Code is required')
  //   .matches(/^[0-9]+$/, 'Postal Code must be a positive number'),
  contact_number: yup
    .string()
    .required('Contact Number is a required field')
    .matches(/^(\+?\d+)?(\s?\d+)*$/, 'Contact Number must contain only digits'),
  // system_service_category: yup.string().required('Speciality is a required'),
  email: yup
    .string()
    .required('Email is a required field')
    .test('is-string', 'Only alphabetic characters are allowed at the beginning', (value) => !numberRegex.test(value))
    .matches(EMAIL_REGX, 'Must be a valid email'),
  country: yup.string().required('Country is required'),
  state: yup.string().required('State is required'),
  city: yup.string().required('City is required'),
  time_slot:yup.string().required('Time Slot is a required field'),
})

const { handleSubmit, errors, resetForm } = useForm({
  validationSchema
})

const { value: name } = useField('name')
const { value: description } = useField('description')
const { value: address } = useField('address')
const { value: city } = useField('city')
const { value: state } = useField('state')
const { value: email } = useField('email')
const { value: time_slot } = useField('time_slot')
const { value: country } = useField('country')
const { value: pincode } = useField('pincode')
const { value: contact_number } = useField('contact_number')
const { value: latitude } = useField('latitude')
const { value: longitude } = useField('longitude')
const { value: system_service_category } = useField('system_service_category')
const { value: vendor_id } = useField('vendor_id')
const { value: status } = useField('status')
const { value: file_url } = useField('file_url')
const errorMessages = ref({})

onMounted(() => {
  setFormData(defaultData())
  getCountry()
  getClinicCategory()
  getVendorList()
})

const IS_SUBMITED = ref(false)
const formSubmit = handleSubmit((values) => {

  IS_SUBMITED.value = true

  if (props.customefield > 0) {
    values.custom_fields_data = JSON.stringify(values.custom_fields_data)
  }

  if (currentId.value > 0) {
    updateRequest({ url: UPDATE_URL, id: currentId.value, body: values, type: 'file' }).then((res) => reset_datatable_close_offcanvas(res))
  } else {
    storeRequest({ url: STORE_URL, body: values, type: 'file' }).then((res) => reset_datatable_close_offcanvas(res))
  }
})
</script>

<style>
.uppy-Dashboard-AddFiles-info {
  display: none !important;
}

.uppy-Dashboard {
  height: 12rem;
  width: 23rem;
}

.uppy-Dashboard-progressindicators {
  display: none;
}
</style>
