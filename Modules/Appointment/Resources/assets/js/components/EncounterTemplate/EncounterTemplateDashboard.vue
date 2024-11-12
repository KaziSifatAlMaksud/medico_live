<template>
    <form @submit.prevent="formSubmit">
       <div class="offcanvas offcanvas-end offcanvas-w-80" tabindex="-1" id="encounter-template-offcanvas" aria-labelledby="form-offcanvasLabel">
           <FormHeader :createTitle="createTitle"></FormHeader>
   
           <div class="offcanvas-body" >
              <div class="row">
                  
                  <div class="col-lg-12 col-md-12">
                     <div class="card">
                        <div class="d-flex justify-content-between flex-wrap gap-3 card-header mb-4">
                           <h4 class="card-title mb-0">{{ $t('appointment.clinic_detail') }}</h4>
                           <div>
                              <div class="d-flex flex-wrap gap-3">
                                 <button class="btn btn-primary"  data-bs-dismiss="offcanvas" aria-label="Close">
                                    <i class="ph ph-caret-double-left me-1"></i>
                                    {{ $t('appointment.back') }}
                                 </button>
                               
                                 <!-- <button class="btn btn-primary">
                                    <i class="ph ph-person-simple-circle"></i>
                                    Body Chart
                                 </button> -->
                              </div>
                           </div>
                        </div>
                        <div class="card-body border-top">
                           <div class="collapse" id="uploadReport">
                              <div class="d-flex justify-content-between flex-wrap gap-3">
                                 <h5 class="mb-0">{{ $t('appointment.medical_report') }}</h5>
                                 <div class="d-flex align-items-center gap-3">
                                    <button class="btn btn-primary">
                                       <i class="ph ph-paper-plane-tilt me-1"></i>
                                       {{ $t('appointment.email') }}
                                    </button>
                                    <button class="btn btn-primary">
                                       <i class="ph ph-plus me-1"></i>
                                      {{ $t('appointment.add_medical_report') }}
                                    </button>
                                    <button class="btn btn-primary" data-bs-toggle="collapse" href="#uploadReport">
                                       <i class="ph ph-caret-double-left me-1"></i>
                                      {{ $t('appointment.back') }}
                                    </button>
                                 </div>
                              </div>
                           </div>
                           <div class="row">
                              <div class="col-lg-4 col-md-6" v-if="enableProblem">
                                 <div class="card bg-body">
                                    <div class="card-header mb-4">
                                       <h5 class="card-title">{{ $t('appointment.problems') }}</h5>
                                    </div>
                                    <div class="card-body medial-history-card border-top">
                                       <ul class="list-inline m-0 p-0">
   
                                          <li class="mb-3 pb-3 border-bottom" v-for="(problem, index) in selectedproblemList" :key="index">
                                              <div class="d-flex align-items-start justify-content-between gap-1">
                                                    <span>{{ index + 1 }}. {{ problem.title }}</span>
                                                    <button class="btn p-0 text-danger" @click="removeProblem(problem.id)">
                                                       <i class="ph ph-x-circle"></i>
                                                   </button>
                                              </div>
                                          </li>
                                         
                                       </ul>
                                    </div>
                                    <div class="card-footer border-top">
                                       <Multiselect id="problem" v-model="problem_id" :placeholder="$t('appointment.select_problems')" v-bind="multiselectOption" :options="problems.options" @select="selectProblem" class="form-group"></Multiselect>
                                       <p class="mt-2 mb-0 fs-12"><b> {{ $t('appointment.note_encounter_problem') }}</b></p>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-lg-4 col-md-6" v-if="enableobservation">
                                 <div class="card bg-body">
                                    <div class="card-header mb-4">
                                       <h5 class="card-title">{{ $t('appointment.observation') }}</h5>
                                    </div>
                                    <div class="card-body medial-history-card border-top">
                                       <ul class="list-inline m-0 p-0">
   
                                          <li class="mb-3 pb-3 border-bottom" v-for="(observation, index) in selectedObservationList" :key="index">
                                              <div class="d-flex align-items-start justify-content-between gap-1">
                                                    <span>{{ index + 1 }}. {{ observation.title }}</span>
                                                    <button class="btn p-0 text-danger" @click="removeObservation(observation.id)">
                                                       <i class="ph ph-x-circle"></i>
                                                   </button>
                                              </div>
                                          </li>
                                        
                                       </ul>
                                    </div>
                                    <div class="card-footer border-top">
                                       <Multiselect id="Observations" v-model="observation_id" :placeholder="$t('appointment.select_observation')" v-bind="multiselectOption" :options="observations.options" @select="selectObservation" class="form-group"></Multiselect>
                                       <p class="mt-2 mb-0 fs-12"><b>{{$t('appointment.note_encounter_observation')}}</b></p>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-lg-4" v-if="enablenote">
                                 <div class="card bg-body">
                                    <div class="card-header mb-4">
                                       <h5 class="card-title">{{$t('appointment.note')}}</h5>
                                    </div>
                                    <div class="card-body medial-history-card border-top">
                                       <ul class="list-inline m-0 p-0">
   
                                          <li class="mb-3 pb-3 border-bottom" v-for="(note, index) in notesList" :key="index">
                                              <div class="d-flex align-items-start justify-content-between gap-1">
                                                    <span>{{ index + 1 }}. {{ note.title }}</span>
                                                    <button class="btn p-0 text-danger" @click="removeNotes(note.id)">
                                                       <i class="ph ph-x-circle"></i>
                                                   </button>
                                              </div>
                                          </li>
                                                               
                                       </ul>
                                    </div>
                                    <div class="card-footer border-top">
                                       <textarea class="form-control h-auto" rows="2" :placeholder="$t('appointment.enter_note')" v-model="notes" name="notes" id="notes" style="min-height: max-content;"></textarea> 
                                       <button class="btn btn-primary mt-3 w-100" @click="addNotesValue()">
                                          <i class="ph ph-plus me-2"></i>{{$t('appointment.add')}}
                                       </button>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="card bg-body" v-if="enablePrescription">
                              <div class="card-header d-flex justify-content-between flex-wrap gap-3">
                                 <h5 class="card-title">{{$t('appointment.prescription')}}</h5>
                                 <div>
                                    <div class="d-flex align-items-center flex-wrap gap-3">
                                       <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#template_exampleModal"  @click="changeId(0)">
                                          <div class="d-inline-flex align-items-center gap-1">
                                             <i class="ph ph-plus"></i>
                                             {{$t('appointment.add_prescription')}}
                                          </div>
                                       </button>
               
                                    </div>
                                 </div>
                              </div>
                              <div class="card-body">
                                 <div class="table-responsive rounded">
                                    <table class="table table-lg m-0">
                                       <thead>
                                          <tr class="text-white">
                                             <th scope="col">{{$t('appointment.name')}}</th>
                                             <th scope="col">{{$t('appointment.frequency')}}</th>
                                             <th scope="col">{{$t('appointment.duration')}}</th>
                                             <th scope="col">{{$t('appointment.action')}}</th>
                                          </tr>
                                       </thead>
                                       <tbody >
                                          
                                          <tr  v-for="(prescription, index) in prescriptionList" :key="index">
                                             <td>
                                                <h6 class="text-primary">
                                               {{ prescription.name }}
                                                </h6>
                                                <p class="m-0">
                                                   {{ prescription.instruction }}
                                                </p>
                                             </td>
                                             <td>
                                                {{ prescription.frequency }}
                                               
                                             </td>
                                             <td>
                                                {{ prescription.duration }}
                                                
                                             </td>
                                             <td>
                                                <div class="d-flex align-items-center gap-3">
                                                   <button type="button" class="btn text-primary p-0 fs-5 me-2" data-bs-toggle="modal" data-bs-target="#template_exampleModal" @click="changeId(prescription.id)" aria-controls="form-offcanvas"><i class="ph ph-pencil-simple-line"></i></button>
                                                   <button type="button" class="btn text-danger p-0 fs-5" @click="destroyData(prescription.id, 'Are you sure you want to delete it?')" data-bs-toggle="tooltip"><i class="ph ph-trash"></i></button>
                                                </div>
                                             </td>
                                          </tr>
                                       </tbody>
                                    </table>
                                 </div>
                              </div>
                           </div>
   
                           <div class="card bg-body">
                              <div class="card-header">
                                 <h6 class="card-title mb-0">{{$t('appointment.other_information')}}</h6>
                              </div>
                              <div class="card-body">
                                 <div class="row">
                                   
                                    <textarea class="form-control h-auto" rows="3" :placeholder="$t('appointment.enter_other_details')" v-model="other_details" name="other_details" id="other_details" style="min-height: max-content;"></textarea> 
                           
                                 <!-- <div class="col-12 text-end mt-4">
                                    <button class="btn btn-primary" @click="addOtherDetails()">{{$t('appointment.save')}}</button>
                                 </div> -->
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
              </div>
   
           </div>

           <div class="offcanvas-footer border-top pt-4">
            <div class="d-grid d-sm-flex justify-content-sm-end gap-3">
              <button class="btn btn-white d-block" type="button" data-bs-dismiss="offcanvas">
                {{ $t('messages.cancel') }}
              </button>
              <button :disabled="IS_SUBMITED" class="btn btn-secondary" name="submit" @click="saveEncounterDetails('Encounter Template Save Successfully')">
                <template v-if="IS_SUBMITED">
                  <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                 {{ $t('appointment.loading') }}
                </template>
                <template v-else> {{ $t('messages.save') }}</template>
              </button>
            </div>
         </div>
         
      </div>
      <AddTemplatePrescription :template_id="TemplateId"  :id="tableId"  @submit="updateprescriptionDetail"></AddTemplatePrescription>
   </form>
   </template>
   
   <script setup>
    import { ref, onMounted ,reactive,computed } from 'vue'
   //  import { ENCOUNTER_DETAIL,GET_SEARCH_DATA,SAVE_OPTION_DATA,REMOVE_DATA,DELETE_PRESCRIPTION_URL} from '../../constant/encounter_dashboard'
    import {SAVE_TEMPLATE_OPTION_DATA,REMOVE_TEMPLATE_HISTROY_DATA, TEMPLATE_DETAIL,DELETE_PRESCRIPTION_URL,SAVE_OTHER_DETAIL_DATA} from '../../constant/encounter-template'
    import {GET_SEARCH_DATA} from '../../constant/encounter_dashboard'
    import { useModuleId, useRequest } from '@/helpers/hooks/useCrudOpration'
    import FormHeader from '@/vue/components/form-elements/FormHeader.vue'
    import FormFooter from '@/vue/components/form-elements/FormFooter.vue'
    import { useSelect } from '@/helpers/hooks/useSelect'
    import { buildMultiSelectObject } from '@/helpers/utilities'
    import { confirmSwal } from '@/helpers/utilities'
   
    import AddTemplatePrescription from '@/vue/components/Modal/AddTemplatePrescription.vue'
   
    import moment from 'moment'
   
    const tableId = ref(null)
   const changeId = (id) => {
     tableId.value = id
   }
   
    defineProps({
       createTitle: { type: String, default: '' }
     })
     const enableProblem = ref(false)
     const enableobservation = ref(false)
     const enablePrescription = ref(false)
     const enablenote = ref(false)
     const encounterdashboard = () => {
      const encounterdata = window.encounter;
      enableProblem.value = encounterdata['is_encounter_problem'] == '1'
      enableobservation.value = encounterdata['is_encounter_observation'] == '1'
      enablePrescription.value = encounterdata['is_encounter_prescription'] == '1'
      enablenote.value = encounterdata['is_encounter_note'] == '1'
   }
     
    const CURRENCY_SYMBOL = ref(window.defaultCurrencySymbol)
   
    const { getRequest, listingRequest, storeRequest,deleteRequest} = useRequest()
   
   
    const prescriptionList=ref([])
    const TemplateDetails = ref(null)
    const other_details=ref('');
   
     const TemplateId = useModuleId(() => {
       getRequest({ url: TEMPLATE_DETAIL, id: TemplateId.value }).then((res) => {
         if(res.status) {

            TemplateDetails.value = res.data
            selectedproblemList.value=res.data.selectedProblemList
            selectedObservationList.value=res.data.selectedObservationList
            prescriptionList.value=res.data.prescriptions
            notesList.value=res.data.notesList
            other_details.value=res.data.other_details
          
         }
       })
     }, 'patient-dashboard')
     
     const problems = ref({ options: [], list: [] })
     const observations = ref({ options: [], list: [] })
   
     const multiselectOption = ref({
     tag:true,
     searchable: true,
     //multiple: true,
     closeOnSelect: true,
     createOption: true,
     
   })
   
   
   const getproblems = () => {
   
      listingRequest({ url: GET_SEARCH_DATA, data: {type: 'encounter_problem'} }).then((res) => {
      problems.value.list=res.results
      problems.value.options = buildMultiSelectObject(res.results, {
       value: 'name',
       label: 'name'
     })
    })
   
   }
   
   const getObservations = () => {
   
         listingRequest({ url: GET_SEARCH_DATA, data: {type: 'encounter_observations'} }).then((res) => {
         observations.value.list=res.results
         observations.value.options = buildMultiSelectObject(res.results, {
         value: 'name',
         label: 'name'
        })
     })
   }
   
   
   const selectedProblemsComputed = computed(() => {
       return selectedproblemList.value;
   });
   
   const selectedproblemList=ref([]);
   
   const values=ref([]);
   
   const selectProblem = (value) => {
   
      values.value = {  
         template_id :TemplateDetails.value.id,
         type: 'encounter_problem',
         name: value
         };
   
         storeRequest({  url: SAVE_TEMPLATE_OPTION_DATA, body: values.value}).then((res) => { 
                            problems.value.list = res.data;
                            selectedproblemList.value=res.medical_histroy
                          
                       });  
   
         problem_id.value=''   
   
        }
   
   const removeProblem = (value) => {
   
      selectedproblemList.value = selectedproblemList.value.filter(problem => problem.id !== value);
   
      listingRequest({ url: REMOVE_TEMPLATE_HISTROY_DATA, data: {id:value,type:'encounter_problem'} }).then((res) => {
         selectedproblemList.value.list=res.medical_histroy
       
     })
      
   }
   
   
   const selectedObservationComputed = computed(() => {
       return selectedObservationList.value;
   });
   
   const selectedObservationList=ref([]);
   
   const observationvalues=ref([]);
   
   
   const selectObservation= (value) => {
   
      observationvalues.value = {  
   
          type:'encounter_observations',
          name: value,
          template_id :TemplateDetails.value.id,
       
      };
      
      storeRequest({  url: SAVE_TEMPLATE_OPTION_DATA, body: observationvalues.value}).then((res) => { 
                            observations.value.list = res.data;
                            selectedObservationList.value=res.medical_histroy
                          
                       });    
   
                       observation_id.value=''            
              }
      
   
   
    const removeObservation = (value) => {
   
      selectedObservationList.value = selectedObservationList.value.filter(observation => observation.id !== value);
   
      listingRequest({ url: REMOVE_TEMPLATE_HISTROY_DATA, data: {id:value,type:'encounter_observations'} }).then((res) => {
   
         selectedObservationList.value.list=res.medical_histroy
       
     })
   
   }
   
   const problem_id = ref('')
   const observation_id = ref('')
   const notes = ref('')
   const notedata = ref([]);
   const notesList=ref([]);
   
   const addNotesValue=()=>{

      if(notes.value !=''){

         notedata.value = {   
            type:'encounter_notes',
            name: notes.value,
            template_id :TemplateDetails.value.id,
         };

         storeRequest({ url: SAVE_TEMPLATE_OPTION_DATA, body: notedata.value}).then((res) => { 
               notesList.value=res.medical_histroy      
         });    

        notes.value=''
      }
    }
   
   const removeNotes = (value) => {

      notesList.value = notesList.value.filter(note => note.id !== value);

       listingRequest({ url: REMOVE_TEMPLATE_HISTROY_DATA, data: {id:value,type:'encounter_notes'} }).then((res) => {

       selectedObservationList.value.list=res.medical_histroy
   
     })
   };
   
   onMounted(() => {
      encounterdashboard()
     getproblems()
     getObservations()
     selectedproblemList.value=[]
     selectedObservationList.value=[]
     notesList.value=[]
     problem_id.value='' 
     notes.value='' 
     observation_id.value=''  
     tableId.value=0 
   })
   
   
   const updateprescriptionDetail = (e) => {
    
      prescriptionList.value=e.value
       
     }
   
   const destroyData = (id, message) => {
     confirmSwal({ title: message }).then((result) => {
       if (!result.isConfirmed) return
       deleteRequest({ url: DELETE_PRESCRIPTION_URL, id }).then((res) => {
         if (res.status) {
            prescriptionList.value=res.prescription
           Swal.fire({
             title: 'Prescription Deleted Successfully',
             text: res.message,
             icon: 'success',
             showClass: {
               popup: 'animate__animated animate__zoomIn'
             },
             hideClass: {
               popup: 'animate__animated animate__zoomOut'
             }
           })
         }
       })
     })
   }


   const OtherDetails=ref([]);


const addOtherDetails=()=>{

   OtherDetails.value = {  

      other_details: other_details.value,
      template_id :TemplateDetails.value.id,
     
};

  storeRequest({ url: SAVE_OTHER_DETAIL_DATA, body: OtherDetails.value}).then((res) => { 

             
          });    
          
  }
  const IS_SUBMITED = ref(false)
  const saveEncounterDetails=(message)=>{

    window.successSnackbar(message)

    bootstrap.Offcanvas.getInstance('#encounter-template-offcanvas').hide()

  }
   

  

  
   </script>