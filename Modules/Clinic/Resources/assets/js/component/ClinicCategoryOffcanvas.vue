<template>
  <form @submit="formSubmit">
    <div class="offcanvas offcanvas-end offcanvas-w-40" tabindex="-1" id="clinic-category-offcanvas" aria-labelledby="form-offcanvasLabel">
      <div class="offcanvas-header border-bottom">
        <h4 class="offcanvas-title" id="form-offcanvasLabel">
          <template v-if="currentId != 0">
            <span v-if="parent_id !== null">{{ editNestedTitle }}</span> <span v-else>{{ editTitle }}</span>
          </template>
          <template v-else>
            <span v-if="parent_id !== null">{{ createNestedTitle }}</span> <span v-else>{{ createTitle }}</span>
          </template>
        </h4>
        <button type="button" class="btn-close-offcanvas" data-bs-dismiss="offcanvas" aria-label="Close"><i class="ph ph-x-circle"></i></button>
      </div>
      <div class="offcanvas-body">
        <div class="row">
          <div class="col-md-6">
            <label class="form-label">{{ $t('clinic.clinic_image') }} </label>
            <imageComponent :ImageViewer="image_url" v-model="file_url" />
            <span class="text-danger">{{ errors.file_url }}</span>
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ $t('category.lbl_name') }} <span class="text-danger">*</span></label>
            <InputField :is-required="true" :label="$t('category.lbl_name')" :placeholder="$t('category.lbl_name')" v-model="name" :error-message="errors.name" :error-messages="errorMessages['name']"></InputField>
            <div class="form-group">
              <label class="form-label" for="description">{{ $t('category.lbl_description') }}</label>
              <textarea class="form-control" v-model="description" :placeholder="$t('category.lbl_description')" id="description"></textarea>
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
              <label for="category" class="form-label">{{ $t('category.lbl_parent_category') }}</label>
              <Multiselect v-bind="singleSelectOption" v-model="parent_id" :placeholder="$t('category.lbl_parent_category')" :value="parent_id" :options="categories"> </Multiselect>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label">{{ $t('category.lbl_featured') }} </label>
              <div class="d-flex justify-content-between align-items-center form-control">
                <label class="form-label m-0" for="category-featured">{{ $t('category.lbl_featured') }}</label>
                <div class="form-check form-switch">
                  <input class="form-check-input" :value="1" name="featured" id="category-featured" type="checkbox" v-model="featured" />
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label">{{ $t('category.lbl_status') }} <span class="text-danger">*</span></label>
              <div class="d-flex justify-content-between align-items-center form-control">
                <label class="form-label m-0" for="category-status">{{ $t('category.lbl_status') }}</label>
                <div class="form-check form-switch">
                  <input class="form-check-input" :value="1" name="status" id="category-status" type="checkbox" v-model="status" />
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div v-for="field in customefield" :key="field.id">
              <FormElement v-model="custom_fields_data" :name="field.name" :label="field.label" :type="field.type" :required="field.required" :options="field.value" :field_id="field.id"></FormElement>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <div v-for="field in customefield" :key="field.id">
                <FormElement v-model="custom_fields_data" :name="field.name" :label="field.label" :type="field.type" :required="field.required" :options="field.value" :field_id="field.id"></FormElement>
              </div>
            </div>
          </div>
        </div>
      </div>
      <FormFooter :IS_SUBMITED="IS_SUBMITED"></FormFooter>
    </div>
  </form>
</template>

<script setup>
import { onMounted, onUnmounted, ref, watch, computed, reactive } from 'vue'
import * as yup from 'yup'
import { useField, useForm } from 'vee-validate'
import { INDEX_LIST_URL, EDIT_URL, STORE_URL, UPDATE_URL } from '../constant/clinic-category'
import { useRequest } from '@/helpers/hooks/useCrudOpration'
import { buildMultiSelectObject } from '@/helpers/utilities'
import FormFooter from '@/vue/components/form-elements/FormFooter.vue'
import InputField from '@/vue/components/form-elements/InputField.vue'
import FormElement from '@/helpers/custom-field/FormElement.vue'
import imageComponent from '@/vue/components/form-elements/imageComponent.vue'

// props
const props = defineProps({
  createTitle: { type: String, default: '' },
  editTitle: { type: String, default: '' },
  createNestedTitle: { type: String, default: '' },
  editNestedTitle: { type: String, default: '' },
  customefield: { type: Array, default: () => [] },
  categoryId: { type: Number, default: '' },
  currentId: { type: Number, default: 0 },
  isSubCategory: { type: Boolean, default: false },
  customData: { type: String, default: '' }
})

const { getRequest, storeRequest, updateRequest } = useRequest()

const singleSelectOption = ref({
  closeOnSelect: true,
  searchable: true,
  clearable: true
})

const multiSelectOption = ref({
  mode: 'tags',
  closeOnSelect: true,
  searchable: true
})
const categories = ref([])
const category_name = ref(null)

const getCategories = () => {
  getRequest({ url: INDEX_LIST_URL }).then((res) => (categories.value = buildMultiSelectObject(res, { value: 'id', label: 'name' })))
}

const customData = reactive(JSON.parse(props.customData))

// Edit Form Or Create Form
const currentId = ref(0)
const updatecurrentId = (e) => {
  setFormData(defaultData())
  currentId.value = Number(e.detail.form_id)
  parent_id.value = e.detail.parent_id || null
  category_name.value = null
  if (props.isSubCategory) {
    getCategories()
    parent_id.value = -1
  }
}

watch(
  currentId,
  () => {
    if (currentId.value > 0) {
      getRequest({ url: EDIT_URL, id: currentId.value }).then((res) => {
        if (res.status) {
          setFormData(res.data)
        }
      }, 'clinic_category')
    } else {
      setFormData(defaultData())
    }
  },
  { deep: true }
)

onMounted(() => {
  getCategories()
  setFormData(defaultData())
})
onMounted(() => document.addEventListener('crud_change_id', updatecurrentId))
onUnmounted(() => document.removeEventListener('crud_change_id', updatecurrentId))
/*
 * Form Data & Validation & Handeling
 */

// Default FORM DATA
const defaultData = () => {
  errorMessages.value = {}
  return {
    name: '',
    parent_id: props.categoryId ?? null,
    status: true,
    file_url: [],
    featured: false,
    description: '',
    custom_fields_data: {}
  }
}
const image_url = ref()
//  Reset Form
const setFormData = (data) => {
  category_name.value = data.category_name
  image_url.value = data.file_url
  resetForm({
    values: {
      name: customData.name || data.name,
      parent_id: data.parent_id,
      status: data.status ? true : false,
      file_url: data.file_url,
      featured: data.featured ? true : false,
      description: customData.description || data.description,
      custom_fields_data: data.custom_field_data
    }
  })
}

// Reload Datatable, SnackBar Message, Alert, Offcanvas Close
const errorMessages = ref({})

const reset_datatable_close_offcanvas = (res) => {
  IS_SUBMITED.value = false
  customData.name='';
  customData.description ='';
  if (res.status) {
    window.successSnackbar(res.message)
    renderedDataTable.ajax.reload(null, false)
    bootstrap.Offcanvas.getInstance('#clinic-category-offcanvas').hide()
    setFormData(defaultData())
    
  
  
  } else {
    window.errorSnackbar(res.message)
    errorMessages.value = res.all_message
  }
 
}

const numberRegex = /^\d+$/
// Validations
const validationSchema = yup.object({
  name: yup.string().required('Name is a required field'),
})

const { handleSubmit, errors, resetForm } = useForm({ validationSchema })
const { value: name } = useField('name')
const { value: description } = useField('description')
const { value: parent_id } = useField('parent_id')
const { value: status } = useField('status')
const { value: featured } = useField('featured')
const { value: file_url } = useField('file_url')

const { value: custom_fields_data } = useField('custom_fields_data')

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
