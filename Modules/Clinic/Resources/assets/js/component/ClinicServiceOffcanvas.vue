<template>
  <form @submit="formSubmit">
    <div class="offcanvas offcanvas-end offcanvas-w-40" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel">
      <FormHeader :currentId="currentId" :editTitle="editTitle" :createTitle="createTitle"></FormHeader>
      <div class="offcanvas-body">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group create-service-image">
              <label class="form-label">{{ $t('clinic.clinic_image') }} </label>
              <ImageComponent :ImageViewer="image_url" v-model="file_url"/>
              <span class="text-danger">{{ errors.file_url }}</span>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group" v-if="enable_multi_vendor()==1">
              <label class="form-label">{{ $t('service.system_service_list') }} <span class="text-danger">*</span></label>
              <Multiselect id="system_service_id" v-model="system_service_id" :value="system_service_id" :placeholder="$t('service.system_service_list')" v-bind="singleSelectOption" :options="systemservice.options" class="form-group"></Multiselect>
              <span v-if="errorMessages['system_service_id']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['system_service_id']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors['system_service_id'] }}</span>
            </div>
            <div class="form-group" v-else>
              <label class="form-label" for="name">{{ $t('service.lbl_name') }} <span class="text-danger">*</span> </label>
              <InputField type="text" :is-required="true" :label="$t('service.lbl_name')" :placeholder="$t('service.lbl_name')"  v-model="name" :error-message="errors['name']" :error-messages="errorMessages['name']"></InputField>
            </div>

            <div class="form-group">
              <label class="form-label" for="category_id">{{ $t('service.lbl_category') }} <span class="text-danger">*</span> </label>
              <Multiselect id="category_id" v-model="category_id" :value="category_id" :placeholder="$t('service.lbl_category')" v-bind="singleSelectOption" :options="categories.options" @change="changeCategory" class="form-group" :disabled="selectedSystemService"></Multiselect>
              <span v-if="errorMessages['category_id']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['category_id']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors.category_id }}</span>
            </div>
            <div class="form-group">
              <label class="form-label" for="subcategory_id">{{ $t('service.lbl_subcategory') }}  </label>
              <Multiselect id="subcategory_id" v-model="subcategory_id" :placeholder="$t('service.lbl_subcategory')" :value="subcategory_id" v-bind="singleSelectOption" :options="subcategories.options" class="form-group" :disabled="selectedSystemService"></Multiselect>
              <span v-if="errorMessages['subcategory_id']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['subcategory_id']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors.subcategory_id }}</span>
            </div>
          </div>

          <div class="col-md-6" v-if="enable_multi_vendor()==1 && (role() === 'admin' || role() === 'demo_admin')">
            <div class="form-group" >
              <label class="form-label" for="vendor_id">{{ $t('clinic.clinic_admin') }} </label>
              <Multiselect class="form-group" v-model="vendor_id" :value="vendor_id" :placeholder="$t('clinic.clinic_admin')" :options="vendors.options"  v-bind="singleSelectOption"  @select="getClinic"   id="vendor_id"></Multiselect>
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
              <label class="form-label" for="clinic_id">{{ $t('clinic.singular_title') }} <span class="text-danger">*</span> </label>
              <input class="form-check-input" type="checkbox" id="all-clinics" v-model="selectAllClinics" @change="selectAllClinicsHandler">
              <Multiselect id="clinic_id" v-model="clinic_id" :placeholder="$t('clinic.singular_title')" :multiple="true" 
                :value="clinic_id" v-bind="multiSelectOption" :options="clinic_centers.options" class="form-group"></Multiselect>
              <span v-if="errorMessages['clinic_id']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['clinic_id']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors.clinic_id }}</span>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label" for="clinic_id">{{ $t('service.lbl_duration_min') }} <span class="text-danger">*</span> </label>
            <InputField type="text" :is-required="true" :label="$t('service.lbl_duration_min')" :placeholder="$t('service.lbl_duration_min')" v-model="duration_min" :error-message="errors['duration_min']" :error-messages="errorMessages['duration_min']"></InputField>
          </div>

          <div class="col-md-6">
            <label class="form-label" for="clinic_id">{{ $t('service.lbl_default_price') }} <span class="text-danger">*</span> </label>
             <InputField type="text" :is-required="true" :icon="`${CURRENCY_SYMBOL}`" :placeholder="$t('service.lbl_default_price')" :label="`${$t('service.lbl_default_price')} (${CURRENCY_SYMBOL})`" placeholder="" v-model="charges" :error-message="errors['charges']" :error-messages="errorMessages['charges']"></InputField>
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
            <div class="form-group" v-if="isGoogleMeetEnabled">
              <label class="form-label">{{ $t('service.lbl_type') }}</label>
              <Multiselect v-model="type" :value="type" :placeholder="$t('service.lbl_type')" v-bind="singleSelectOption" :options="types" id="type" :disabled="selectedSystemService" autocomplete="off" ></Multiselect>
            </div>
          </div>
          <div class="col-md-6">
            <div v-if="type == 'online' && isGoogleMeetEnabled" class="form-group">
              <label class="form-label">{{ $t('service.lbl_conslutcy') }}</label>
              <Multiselect v-model="is_video_consultancy" :placeholder="$t('service.lbl_conslutcy')" :value="is_video_consultancy" v-bind="singleSelectOption" :options="videoConslutcy" id="type" :disabled="selectedSystemService" autocomplete="off"></Multiselect>
            </div>
          </div>

          <div class="col-md-12">
            <div class="form-group">
              <label class="form-label" for="description">{{ $t('service.lbl_description') }}</label>
              <textarea class="form-control" v-model="description" id="description" :disabled="selectedSystemService" :placeholder="$t('service.lbl_description')"></textarea>
              <span v-if="errorMessages['description']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['description']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors.description }}</span>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label" for="category-discount">{{ $t('service.discount') }}</label>
              <div class="d-flex justify-content-between align-items-center form-control">
                <label class="form-label m-0" for="category-discount">{{ $t('service.discount') }}</label>
                <div class="form-check form-switch">
                  <input class="form-check-input" :value="discount" :checked="discount" name="discount" id="category-discount" type="checkbox" v-model="discount" />
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6" v-if="discount==1">
            <div class="form-group">
              <label class="form-label">{{ $t('service.lbl_discount_type') }} <span class="text-danger">*</span></label>
              <Multiselect id="discount_type" v-model="discount_type" :value="discount_type" v-bind="discount_type_data" class="form-group"></Multiselect>
              <span v-if="errorMessages['discount_type']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['discount_type']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors.discount_type }}</span>
            </div>
          </div>
          <div class="col-md-6" v-if="discount==1">
            <label class="form-label">{{ $t('service.lbl_discount_value') }} <span class="text-danger">*</span> </label>
            <InputField type="number" :is-required="true" :label="$t('service.lbl_discount_value')" :icon="`${CURRENCY_SYMBOL}`" placeholder="" v-model="discount_value" :error-message="errors['discount_value']" :error-messages="errorMessages['discount_value']"></InputField>
          </div>
          
          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label" for="category-status">{{ $t('service.lbl_status') }}</label>
              <div class="d-flex justify-content-between align-items-center form-control">
                <label class="form-label m-0" for="category-status">{{ $t('service.lbl_status') }}</label>
                <div class="form-check form-switch">
                  <input class="form-check-input" :value="status" :checked="status" name="status" id="category-status" type="checkbox" v-model="status" />
                </div>
              </div>
            </div>
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
import { ref, onMounted, computed,watch,reactive } from 'vue'
import { EDIT_URL, STORE_URL, UPDATE_URL, CATEGORY_LIST, CLINIC_CENTER_LIST, VENDOR_LIST,SUB_CATEGORY_LIST,SYSTEM_SERVICE_LIST, SETTING_DATA } from '../constant/clinic-service'

import ImageComponent from '@/vue/components/form-elements/imageComponent.vue'
import { useField, useForm } from 'vee-validate'
import InputField from '@/vue/components/form-elements/InputField.vue'
import { useSelect } from '@/helpers/hooks/useSelect'
import { useModuleId, useRequest, useOnOffcanvasHide } from '@/helpers/hooks/useCrudOpration'
import * as yup from 'yup'
import { buildMultiSelectObject } from '@/helpers/utilities'
import FormHeader from '@/vue/components/form-elements/FormHeader.vue'
import FormFooter from '@/vue/components/form-elements/FormFooter.vue'
import FormElement from '@/helpers/custom-field/FormElement.vue'

// props
const props = defineProps({
  createTitle: { type: String, default: '' },
  editTitle: { type: String, default: '' },
  customefield: { type: Array, default: () => [] },

})

const role = () => {
    return window.auth_role[0];
}

const isReceptionist = computed(() => role() === 'receptionist');

const ImageViewer=ref(null)

const CURRENCY_SYMBOL = ref(window.defaultCurrencySymbol)
const { getRequest, storeRequest, updateRequest, listingRequest } = useRequest()
let selectedClinics = ref([])
let selectedCategory = ref([])
// Edit Form Or Create Form
const currentId = useModuleId(() => {
  if (currentId.value > 0) {
    getRequest({ url: EDIT_URL, id: currentId.value }).then((res) => {
      if (res.status) {
        if(enable_multi_vendor()==1){
          getClinic(res.data.vendor_id)
        }else{
          getClinic();
        }
        
        changeCategory(res.data.vendor_id.category_id)
        getCategoryList(res.data.system_service_id)
        setFormData(res.data)
        selectedClinics.value = res.data.clinic_id || []
        clinic_id.value = selectedClinics.value
        selectedCategory.value = res.data.category_id || []
        category_id.value = selectedCategory.value
        discount.value = discount.value
      }
    })
  } else {
    setFormData(defaultData())
  }
})

const enable_multi_vendor = () => {
  return window.multiVendorEnabled
}


// Function to delete Images
const removeImage = ({ imageViewerBS64, changeFile }) => {
  imageViewerBS64.value = null
  changeFile.value = null
}


/*
 * Form Data & Validation & Handeling
 */
// Default FORM DATA
const defaultData = () => {
  errorMessages.value = {}
  return {
    system_service_id: '',
    name: '',
    description: '',
    duration_min: '',
    charges: '',
    subcategory_id:'',
    status: 1,
    discount:0,
    time_slot:'',
    vendor_id: '',
    category_id: '',
    discount_type:'fixed',
    discount_value:0,
    file_url: [],
    clinic_id: isReceptionist.value && clinic_centers.value.list.length > 0 ? [clinic_centers.value.list[0].id] : [], // Automatically select the first clinic for receptionist
    type: 'in_clinic',
    is_video_consultancy: 0,
    custom_fields_data: {}
  }
}
const image_url = ref()
//  Reset Form
const setFormData = (data) => {
  image_url.value = data.file_url
  resetForm({
    values: {
      system_service_id: data.system_service_id ?? '',
      name: data.name,
      description: data.description,
      duration_min: data.duration_min,
      charges: data.charges,
      status: data.status,
      time_slot:data.time_slot,
      subcategory_id:data.subcategory_id,
      vendor_id: data.vendor_id,
      category_id: data.category_id,
      discount:data.discount,
      file_url: data.file_url,
      discount_type: data.discount_type || 'fixed',
      discount_value:data.discount_value,
      clinic_id: data.clinic_id,
      type: data.type,
      is_video_consultancy: data.is_video_consultancy,
      custom_fields_data: data.custom_field_data,
    }
  })
}

// Reload Datatable, SnackBar Message, Alert, Offcanvas Close
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

const numberRegex = /^\d+$/
// Validations
const validationSchema = yup.object({
  system_service_id: yup.string().test('system_service_id', "System Service is required", function(value) {
    if (enable_multi_vendor() == 1 && !value) {
        return false; 
    }
    return true;  
  }),
  name: yup.string().test('name', "Service name is a required field", function(value) {
    if (enable_multi_vendor() != 1 && !value) {
        return false; 
    }
    return true;  
  }),

  duration_min: yup.string().required('Service Duration ( Mins ) is a required field').matches(/^\d+$/, 'Only numbers are allowed'),
  charges: yup.string().required('Price is a required field').matches(/^\d+$/, 'Only numbers are allowed'),
  time_slot:yup.string().required('Time Slot is a required field'),
  category_id: yup.string().required('Category is a required field'),

  clinic_id: yup.array().test('clinic_id', 'Clinic is a required field', function (value) {
    if (value.length == 0) {
      return false
    }
    return true
  }),
  file_url: yup.array()
    .transform((value) => {
      // Ensure file_url is always an array
      return Array.isArray(value) ? value : (value ? [value] : []);
    })
    .min(1, 'Image is required').required('Image is required'),
    
discount_value: yup.number().typeError('Discount value must be a number')
    .min(0, 'Discount value must be greater than 0')
    .test('discount_value_check', function(value) {
        const price = this.parent.charges;    
        const discountType = this.parent.discount_type;
        const discount = this.parent.discount;

              if(discount === 1 || discount === true){
                if(value >= 1 && value !== null){
                  if (discountType === 'fixed') {
                    if (value !== undefined && price !== undefined && value >= price) {
                        return this.createError({ message: 'Fixed discount value must be less than price' });
                    }
                  } else if (discountType === 'percentage') {
                      if (value !== undefined && value >= 100) {
                          return this.createError({ message: 'Percentage discount value must be less than 100' });
                      }
                  }
                }else{
                  return this.createError({ message: 'Discount value must be greater than 0' });
                }
               
              }
               
        return true;
    })
  .nullable()


})

const { handleSubmit, errors, resetForm } = useForm({
  validationSchema
})
const { value: system_service_id } = useField('system_service_id')
const { value: name } = useField('name')
const { value: description } = useField('description')
const { value: duration_min } = useField('duration_min')
const { value: charges } = useField('charges')
const { value: status } = useField('status')
const { value: vendor_id } = useField('vendor_id')
const { value: category_id } = useField('category_id')
const { value: subcategory_id } = useField('subcategory_id')
const { value: time_slot } = useField('time_slot')
const { value: discount } = useField('discount')
const { value: discount_value } = useField('discount_value')
const { value: discount_type } = useField('discount_type')
const { value: file_url } = useField('file_url')
const { value: clinic_id } = useField('clinic_id')
const { value: type } = useField('type')
const { value: is_video_consultancy } = useField('is_video_consultancy')
const { value: custom_fields_data } = useField('custom_fields_data')




const errorMessages = ref({})

const singleSelectOption = ref({
  closeOnSelect: true,
  searchable: true
})

const multiSelectOption = ref({
  mode: 'tags',
  closeOnSelect: true,
  searchable: true
})

const slots_data = ref({
  searchable: true,
  options: [
    { label: 'Default Clinic Time Slot', value: 'clinic_slot' },
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
    { label: 60, value: 60 },

  ],
  closeOnSelect: true,
  createOption: false
})


const discount_type_data = ref({
  searchable: true,
  options: [
    { label: 'Percentage', value: 'percentage' },
    { label: 'Fixed', value: 'fixed' },
  ],
  closeOnSelect: true,
  createOption: false
})

const videoConslutcy = [
  { label: 'Yes', value: '1' },
  { label: 'No', value: '0' }
]

const types = [
  { label: 'In Clinic', value: 'in_clinic' },
  { label: 'Online', value: 'online' }
]

const categories = ref({ options: [], list: [] })

const clinic_centers = ref({ options: [], list: [] })

const subcategories = ref({ options: [], list: [] })

const changeCategory = (value) => {
  useSelect({ url: SUB_CATEGORY_LIST, data: { id: value } }, { value: 'id', label: 'name' }).then((data) => (subcategories.value = data))
}



const getClinic = (value) => {
  clinic_id.value = [];
  useSelect({ url: CLINIC_CENTER_LIST, data: { id: value } }, { value: 'id', label: 'clinic_name' }).then((data) => {
    clinic_centers.value = data
    console.log(clinic_centers.value)
    if (isReceptionist.value && clinic_centers.value.list.length > 0) {
      clinic_id.value = [clinic_centers.value.list[0].id] // Automatically select the first clinic for receptionist
    }
  })
}

const getCategoryList = (value) => {
  category_id.value = []
  useSelect({ url: CATEGORY_LIST, data: { id: value } }, { value: 'id', label: 'name' }).then((data) => (categories.value = data))
}
const getsetting = ref('')
const getSetting = (value) => {
  useSelect({ url: CATEGORY_LIST, data: { id: value } }, { value: 'id', label: 'name' }).then((data) => (categories.value = data))
}

const vendors = ref({ options: [], list: [] })
const getVendorList = () => {
  useSelect({ url: VENDOR_LIST, data: { system_service: 'clinic' } }, { value: 'id', label: 'name' }).then((data) => (vendors.value = data))
}

const systemservice = ref({ options: [], list: [] })

const getSystemServiceList = () => {
  useSelect({ url: SYSTEM_SERVICE_LIST }, { value: 'id', label: 'name' }).then((data) => (systemservice.value = data))
}

const set_data = ref(null);

const getSettingData = () => {
  return listingRequest({ url: SETTING_DATA }).then((res) => {
    set_data.value = res;
  });
};


const selectedSystemService = computed(() => systemservice.value.list.find((service) => service.id == system_service_id.value) ?? null)

watch(selectedSystemService, (newValue, oldValue) => {
  if (newValue !== null) {
     changeCategory( newValue.category_id)

     category_id.value= newValue.category_id,
     subcategory_id.value= newValue.subcategory_id,
     description.value= newValue.description,
     type.value= newValue.type,
     is_video_consultancy.value= newValue.is_video_consultancy
  }
});



const selectAllClinics = ref(false);

const selectAllClinicsHandler = () => {
    if (selectAllClinics.value) {
      if (clinic_centers.value && clinic_centers.value.options) {
            clinic_id.value = clinic_centers.value.options.map(option => option.value);
        }
    } else {
        clinic_id.value = [];
    }
}

onMounted(() => {
  getSystemServiceList()
  getCategoryList()
  changeCategory()
  getVendorList()
  getClinic()
  getSettingData()
  setFormData(defaultData())
})
watch([clinic_id], ([newClinics], [oldClinics]) => {
  selectAllClinics.value = newClinics.length === clinic_centers.value.options.length;

});

watch([set_data], ([newValue], [oldValue]) => {

if (newValue === 1) {
  console.log('Google Meet is enabled');
} else {
  console.log('Google Meet is not enabled');
}

});

const isGoogleMeetEnabled = computed(() => {
return set_data.value === 1;
});

// Form Submit
const IS_SUBMITED = ref(false)
const formSubmit = handleSubmit((values) => {
  
  IS_SUBMITED.value = true

  values.custom_fields_data = JSON.stringify(values.custom_fields_data)
  if (currentId.value > 0) {
    updateRequest({ url: UPDATE_URL, id: currentId.value, body: values, type: 'file' }).then((res) => reset_datatable_close_offcanvas(res))
  } else {
    storeRequest({ url: STORE_URL, body: values, type: 'file' }).then((res) => reset_datatable_close_offcanvas(res))
  }
})
</script>


