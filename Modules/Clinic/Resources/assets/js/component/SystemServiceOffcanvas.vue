<template>
    <form @submit="formSubmit">
      <div class="offcanvas offcanvas-end offcanvas-w-40" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel">
        <FormHeader :currentId="currentId" :editTitle="editTitle" :createTitle="createTitle"></FormHeader>
        <div class="offcanvas-body">
          <div class="row">
            <div class="col-md-6 create-service-image">
              <label class="form-label" for="subcategory_id">{{ $t('clinic.clinic_image') }}  </label>
              <ImageComponent  :ImageViewer="image_url" v-model="file_url"/>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label" for="subcategory_id">{{ $t('service.lbl_name') }} <span class="text-danger">*</span> </label>
                <InputField class="" type="text" :is-required="true" :label="$t('service_package.lbl_name')" :placeholder="$t('service_package.lbl_name')" v-model="name" :error-message="errors['name']" :error-messages="errorMessages['name']" :icon="'<i class=\'ph ph-first-aid-kit\'></i>'"></InputField>
              </div>

              <div class="form-group">
                <label class="form-label" for="category_id">{{ $t('service.lbl_category') }} <span class="text-danger">*</span> </label>
                <Multiselect id="category_id" v-model="category_id" :value="category_id" v-bind="singleSelectOption"   :placeholder="$t('service.lbl_category')" :options="categories.options" @select="changeCategory" class="form-group"></Multiselect>
                <span v-if="errorMessages['category_id']">
                  <ul class="text-danger">
                    <li v-for="err in errorMessages['category_id']" :key="err">{{ err }}</li>
                  </ul>
                </span>
                <span class="text-danger">{{ errors.category_id }}</span>
              </div>

              <div class="form-group">
                <label class="form-label" for="subcategory_id">{{ $t('service.lbl_subcategory') }}  </label>
                <Multiselect id="subcategory_id" v-model="subcategory_id"  :placeholder="$t('service.lbl_subcategory')" :value="subcategory_id" v-bind="singleSelectOption" :options="subcategories.options" class="form-group"></Multiselect>
                <span v-if="errorMessages['subcategory_id']">
                <ul class="text-danger">
                    <li v-for="err in errorMessages['subcategory_id']" :key="err">{{ err }}</li>
                </ul>
                </span>
                <span class="text-danger">{{ errors.subcategory_id }}</span>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label class="form-label" for="description">{{ $t('service.lbl_description') }}</label>
                <textarea class="form-control" v-model="description" id="description" :placeholder="$t('service.lbl_description')"></textarea>
                <span v-if="errorMessages['description']">
                  <ul class="text-danger">
                    <li v-for="err in errorMessages['description']" :key="err">{{ err }}</li>
                  </ul>
                </span>
                <span class="text-danger">{{ errors.description }}</span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group" v-if="isGoogleMeetEnabled">
                <label class="form-label">{{ $t('service.lbl_type') }}</label>
                <Multiselect v-model="type" :value="type" v-bind="singleSelectOption" :options="types" id="type" autocomplete="off"></Multiselect>
              </div>
            </div>
            <div class="col-md-6">
              <div v-if="type == 'online' && isGoogleMeetEnabled" class="form-group">
                <label class="form-label">{{ $t('service.lbl_conslutcy') }}</label>
                <Multiselect v-model="is_video_consultancy" :value="is_video_consultancy" v-bind="singleSelectOption" :options="videoConslutcy" id="type" autocomplete="off"></Multiselect>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label" for="subcategory_id">{{ $t('category.lbl_featured') }}  </label>
                <div class="d-flex justify-content-between align-items-center form-control">
                  <label class="form-label m-0" for="category-featured">{{ $t('messages.yes') }}</label>
                  <div class="form-check form-switch">
                    <input class="form-check-input" :value="featured" :checked="featured" name="featured" id="category-featured" type="checkbox" v-model="featured" />
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label" for="subcategory_id">{{ $t('service.lbl_status') }}  </label>
                <div class="d-flex justify-content-between align-items-center form-control">
                  <label class="form-label m-0" for="category-status">{{ $t('messages.active') }}</label>
                  <div class="form-check form-switch">
                    <input class="form-check-input" :value="status" :checked="status" name="status" id="category-status" type="checkbox" v-model="status" />
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div v-for="field in customefield" :key="field.id">
                <FormElement v-model="custom_fields_data" :name="field.name" :label="field.label" :type="field.type" :required="field.required" :options="field.value" :field_id="field.id"></FormElement>
              </div>
            </div>
          </div>
        </div>
        <FormFooter :IS_SUBMITED="IS_SUBMITED"></FormFooter>
      </div>
    </form>
  </template>

  <script setup>
  import { ref, onMounted, computed,watch } from 'vue'
  import { EDIT_URL, STORE_URL, UPDATE_URL, CATEGORY_LIST, SUB_CATEGORY_LIST,SETTING_DATA } from '../constant/system-service'

  import { useField, useForm } from 'vee-validate'
  import InputField from '@/vue/components/form-elements/InputField.vue'
  import { useSelect } from '@/helpers/hooks/useSelect'
  import { useModuleId, useRequest, useOnOffcanvasHide } from '@/helpers/hooks/useCrudOpration'
  import * as yup from 'yup'
  import { buildMultiSelectObject } from '@/helpers/utilities'
  import { readFile } from '@/helpers/utilities'
  import FormHeader from '@/vue/components/form-elements/FormHeader.vue'
  import FormFooter from '@/vue/components/form-elements/FormFooter.vue'
  import FormElement from '@/helpers/custom-field/FormElement.vue'
  import ImageComponent from '@/vue/components/form-elements/imageComponent.vue'
  // props
  const props = defineProps({
    createTitle: { type: String, default: '' },
    editTitle: { type: String, default: '' },
    customefield: { type: Array, default: () => [] },
    customData: { type: String, default: '' }
  })

  const { getRequest, storeRequest, updateRequest, listingRequest } = useRequest()

  const customData = JSON.parse(props.customData)

  let selectedCategory = ref([])
  // Edit Form Or Create Form
  const currentId = useModuleId(() => {
    if (currentId.value > 0) {
      getRequest({ url: EDIT_URL, id: currentId.value }).then((res) => {
        if (res.status) {
          setFormData(res.data)

          changeCategory(category_id.value)
          getCategoryList()

          selectedCategory.value = res.data.category_id || []
          category_id.value = selectedCategory.value

        }
      })
    } else {
      setFormData(defaultData())
    }
  })


  const image_url = ref()

  /*
   * Form Data & Validation & Handeling
   */
  // Default FORM DATA
  const defaultData = () => {
    errorMessages.value = {}
    return {
      name: '',
      description: '',
      subcategory_id:'',
      status: 1,
      category_id: '',
      file_url: null,
      custom_fields_data: {},
      featured:0,
      is_video_consultancy: 0,
      type: 'in_clinic',
    }
  }

  //  Reset Form
  const setFormData = (data) => {
    image_url.value = data.file_url
    resetForm({
      values: {
        name: customData.name || data.name,
        description: customData.description || data.description,
        status: data.status,
        subcategory_id:data.subcategory_id,
        category_id: data.category_id,
        file_url: data.file_url,
        custom_fields_data: data.custom_field_data,
        featured:data.featured,
        is_video_consultancy: data.is_video_consultancy,
        type: data.type
      }
    })
  }

  // Reload Datatable, SnackBar Message, Alert, Offcanvas Close
  const reset_datatable_close_offcanvas = (res) => {
    IS_SUBMITED.value = false
    customData.name='';
    customData.description ='';
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

  const numberRegex = /^\d+$/
  // Validations
  const validationSchema = yup.object({
    name: yup.string().required('Name is required'),
    category_id: yup.string().required('Category is a required field').matches(/^\d+$/, 'Only numbers are allowed'),
  })

  const { handleSubmit, errors, resetForm } = useForm({
    validationSchema
  })
  const { value: name } = useField('name')
  const { value: description } = useField('description')
  const { value: status } = useField('status')
  const { value: category_id } = useField('category_id')
  const { value: subcategory_id } = useField('subcategory_id')
  const { value: file_url } = useField('file_url')
  const { value: custom_fields_data } = useField('custom_fields_data')
  const { value: type } = useField('type')
  const { value: is_video_consultancy } = useField('is_video_consultancy')
  const { value: featured } = useField('featured')



  const errorMessages = ref({})

  const singleSelectOption = ref({
    closeOnSelect: true,
    searchable: true
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

  const subcategories = ref({ options: [], list: [] })

  const changeCategory = (value) => {
    useSelect({ url: SUB_CATEGORY_LIST, data: { id: value } }, { value: 'id', label: 'name' }).then((data) => (subcategories.value = data))
  }

  const getCategoryList = (value) => {
    category_id.value = []
    useSelect({ url: CATEGORY_LIST, data: { id: value } }, { value: 'id', label: 'name' }).then((data) => (categories.value = data))
  }

  const set_data = ref(null);

const getSettingData = () => {
  return listingRequest({ url: SETTING_DATA }).then((res) => {
    set_data.value = res;
  });
};


  onMounted(() => {
    getCategoryList()
    setFormData(defaultData())
    getSettingData()
  })

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





