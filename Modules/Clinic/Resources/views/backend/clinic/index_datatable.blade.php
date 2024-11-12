@extends('backend.layouts.app')

@section('title') {{ __($module_title) }} @endsection

@section('content')
<div class="table-content mb-3">
    <x-backend.section-header>
        <div class="d-flex flex-wrap gap-3">

        
           @php
            $permissionsToCheck = ['edit_clinics_center', 'delete_clinics_service'];
           @endphp

           @if(collect($permissionsToCheck)->contains(fn ($permission) => auth()->user()->can($permission)))

            <x-backend.quick-action url="{{ route('backend.clinics.bulk_action') }}">
                <div class="">
                    <select name="action_type" class="form-control select2 col-12" id="quick-action-type"
                        style="width:100%">
                        <option value="">{{ __('messages.no_action') }}</option>
                        @hasPermission('edit_clinics_center')
                        <option value="change-status">{{ __('messages.status') }}</option>
                        @endhasPermission

                        @hasPermission('delete_clinics_service')
                        <option value="delete">{{ __('messages.delete') }}</option>
                        @endhasPermission
                    </select>
                </div>

                <div class="select-status d-none quick-action-field" id="change-status-action">
                    <select name="status" class="form-control select2" id="status" style="width:100%">
                        <option value="" selected>{{ __('messages.select_status') }}</option>
                        <option value="1" >{{ __('messages.active') }}</option>
                        <option value="0">{{ __('messages.inactive') }}</option>
                    </select>
                </div>
            </x-backend.quick-action>
        @endif
            <div>
                <button type="button" class="btn btn-primary" data-modal="export">
                <i class="ph ph-download-simple me-1"></i> {{ __('messages.export') }}
                </button>

                </div>
        </div>
        <x-slot name="toolbar">

            <div>
                <div class="datatable-filter">
                    <select name="column_status" id="column_status" class="select2 form-control"
                        data-filter="select" style="width: 100%">
                        <option value="">{{ __('messages.all') }}</option>
                        <option value="0" {{ $filter['status'] == '0' ? 'selected' : '' }}>
                            {{ __('messages.inactive') }}</option>
                        <option value="1" {{ $filter['status'] == '1' ? 'selected' : '' }}>
                            {{ __('messages.active') }}</option>
                    </select>
                </div>
            </div>

            <div class="input-group flex-nowrap">
                <span class="input-group-text" id="addon-wrapping"><i
                        class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..." aria-label="Search"
                    aria-describedby="addon-wrapping">
            </div>
            <button class="btn btn-secondary d-flex align-items-center gap-1 btn-group" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample"><i class="ph ph-funnel"></i>{{__('messages.advance_filter')}}</button>
            @hasPermission('add_clinics_center')
                <x-buttons.offcanvas target='#form-offcanvas' title="{{ __('messages.create') }} {{ __('clinic.singular_title') }}">
                {{ __('messages.new') }}  </x-buttons.offcanvas>
            @endhasPermission

        </x-slot>
    </x-backend.section-header>
    <table id="datatable" class="table table-responsive">
    </table>
</div>
<div data-render="app">
    <clinic-form-offcanvas create-title="{{ __('messages.create') }} {{ __('clinic.singular_title') }}"
    default-image="{{default_file_url()}}"
    edit-title="{{ __('messages.edit') }} {{ __('clinic.singular_title') }}" :customefield="{{ json_encode($customefield) }}">
    </clinic-form-offcanvas>

    <clinic-session-offcanvas></clinic-session-offcanvas>
     <clinic-gallery-offcanvas></clinic-gallery-offcanvas>
     <clinic-details-offcanvas></clinic-details-offcanvas>
</div>

<x-backend.advance-filter>
    <x-slot name="title">
        <h4>{{ __('service.lbl_advanced_filter') }}</h4>
    </x-slot>

     <div class="form-group datatable-filter">
         <label class="form-label" for="form-label"> {{ __('clinic.lbl_clinic') }}</label>
         <select  id="clinic_name" name="clinic_name" data-filter="select"
             class="select2 form-control"
             data-ajax--url="{{ route('backend.get_search_data', ['type' => 'clinic_name']) }}"
             data-ajax--cache="true">
         </select>
     </div>

     @if(multiVendor() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')))

    <div class="form-group datatable-filter">
        <label class="form-label" for="form-label">{{ __('clinic.clinic_admin') }}</label>
        <select  id="column_clinic_admin" name="column_clinic_admin" data-filter="select"
            class="select2 form-control"
            data-ajax--url="{{ route('backend.get_search_data', ['type' => 'clinic_admin']) }}"
            data-ajax--cache="true">
        </select>
    </div>

    @endif

     <div class="form-group datatable-filter">
         <label class="form-label" for="form-label"> {{ __('clinic.speciality') }}</label>
         <select  id="column_category" name="column_category" data-filter="select"
             class="select2 form-control"
             data-ajax--url="{{ route('backend.get_search_data', ['type' => 'system_category']) }}"
             data-ajax--cache="true">
         </select>
     </div>

     <div class="form-group datatable-filter">
         <label class="form-label" for="form-label"> {{ __('clinic.country') }}</label>
         <select  id="column_country" name="column_country" data-filter="select"
             class="select2 form-control"
             data-ajax--url="{{ route('backend.get_search_data', ['type' => 'country']) }}"
             data-ajax--cache="true">
         </select>
     </div>

     <div class="form-group datatable-filter">
         <label class="form-label" for="form-label"> {{ __('clinic.state') }}</label>
         <select  id="column_state" name="column_state" data-filter="select"
             class="select2 form-control"
             >
         </select>
     </div>

     <div class="form-group datatable-filter">
         <label class="form-label" for="form-label"> {{ __('clinic.city') }}</label>
         <select  id="column_city" name="column_city" data-filter="select"
             class="select2 form-control"
             >
         </select>
     </div>

    <button type="reset" class="btn btn-danger" id="reset-filter">{{ __('appointment.reset') }}</button>
</x-backend.advance-filter>
@endsection


@push ('after-styles')
{{-- <link rel="stylesheet" href="{{ mix('modules/world/style.css') }}"> --}}
<!-- DataTables Core and Extensions -->
<link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">

@endpush

@push ('after-scripts')
<script src="{{ mix('modules/clinic/script.js') }}"></script>
<script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>
<script src="{{ asset('js/form-modal/index.js') }}" defer></script>

<!-- DataTables Core and Extensions -->
<script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>

<script type="text/javascript" defer>
   const columns = [
            @unless(auth()->user()->hasRole('doctor'))
            {
                name: 'check',
                data: 'check',
                title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                width: '0%',
                exportable: false,
                orderable: false,
                searchable: false,
            },
            @endunless
            {
                data: 'updated_at',
                name: 'updated_at',
                width: '15%',
                visible: false
            },
            {
                data: 'clinic_name',
                name: 'clinic_name',
                title: "{{ __('clinic.lbl_name') }}"
            },

            {
                data: 'system_service_category',
                name: 'system_service_category',
                title: "{{ __('clinic.speciality') }}"
            },
            @if(multiVendor() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')))
            {
                data: 'vendor_id',
                name: 'vendor_id',
                title: "{{ __('clinic.clinic_admin') }}"
            },
            @endif
            {
                data: 'contact_number',
                name: 'contact_number',
                title: "{{ __('clinic.lbl_contact_number') }}"
            },
            { 
                data: 'description',
                name: 'description',
                title: "{{ __('clinic.lbl_description') }}",
                className: 'description-column'


            },
            @unless(auth()->user()->hasRole('doctor'))
               {
                   data: 'status',
                   name: 'status',
                   orderable: false,
                   searchable: true,
                   title: "{{ __('clinic.status') }}",
                   width: '5%'
               },
            @endunless



    ]

    const actionColumn = [{
        data: 'action',
        name: 'action',
        orderable: false,
        searchable: false,
        title: "{{ __('service.lbl_action') }}",
        width: '5%'
    }]
    const customFieldColumns = JSON.parse(@json($columns))

    let finalColumns = [
        ...columns,
        ...customFieldColumns,
        ...actionColumn
    ]

    document.addEventListener('DOMContentLoaded', (event) => {
        initDatatable({
            url: '{{ route("backend.clinics.index_data") }}',
            finalColumns,
            orderColumn: [
                @if(auth()->user()->hasRole('doctor'))
                [0, "desc"]
                @else
                [1, "desc"]
                @endif
            ],
            advanceFilter: () => {
                return {
                    clinic_name: $('#clinic_name').val(),
                    category_name: $('#column_category').val(),
                    clinic_admin: $('#column_clinic_admin').val(),
                    country:$('#column_country').val(),
                    state:$('#column_state').val(),
                    city:$('#column_city').val()
                };
            }
        });

        // Reset filter button functionality
        $('#reset-filter').on('click', function(e) {
            $('#clinic_name').val('');
            $('#column_category').val('');
            $('#column_clinic_admin').val('');
            $('#column_country').val('');
            $('#column_state').val('');
            $('#column_city').val('');
           window.renderedDataTable.ajax.reload(null, false);
        });
    });


    function resetQuickAction () {
    const actionValue = $('#quick-action-type').val();
        if (actionValue != '') {
            $('#quick-action-apply').removeAttr('disabled');

            if (actionValue == 'change-status') {
                $('.quick-action-field').addClass('d-none');
                $('#change-status-action').removeClass('d-none');
            } else {
                $('.quick-action-field').addClass('d-none');
            }
        } else {
            $('#quick-action-apply').attr('disabled', true);
            $('.quick-action-field').addClass('d-none');
        }
        }

        $('#quick-action-type').change(function () {
            resetQuickAction()
        });

        $(document).on('update_quick_action', function() {
            // resetActionButtons()
        })


        $(document).on('change', '#column_country', function() {
           var country = $(this).val();
            $('#column_state').empty();
            $('#column_city').empty();
            stateName(country);
        })


        $(document).on('change', '#column_state', function() {
           var state = $(this).val();
           $('#column_city').empty();
           cityName(state);
        })


    function stateName(country) {
        var state = $('#column_state');
       var state_route = "{{ route('backend.get_search_data', [ 'type' => 'state','sub_type' =>'']) }}" + country;
       state_route = state_route.replace('amp;', '');
       $.ajax({
            url: state_route,
            success: function(result) {

            $('#column_state').empty();

            $.each(result.results, function(index, state) {
                $('#column_state').append($('<option>', {
                    value: state.id,
                    text: state.text
                }));
            });

            $('#column_state').select2({
                width: '100%'
            });


            if (country) {
                $("#column_state").val(state).trigger('change');
            }
         }
      });
    }



    function cityName(state) {
        var city = $('#column_city');
        var city_route = "{{ route('backend.get_search_data', [ 'type' => 'city','sub_type' =>'']) }}" + state;
        city_route = city_route.replace('amp;', '');
        $.ajax({
             url: city_route,
             success: function(result) {

             $('#column_city').empty();

             $.each(result.results, function(index, city) {
                 $('#column_city').append($('<option>', {
                     value: city.id,
                     text: city.text
                 }));
             });

             $('#column_city').select2({
                 width: '100%'
             });


             if (state) {
                 $("#column_city").val(city).trigger('change');
             }
          }
       });
     }


</script>
@endpush
