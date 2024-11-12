@extends('backend.layouts.app')

@section('title') {{ __($module_title) }} @endsection

@section('content')
<div class="table-content mb-5">
    <x-backend.section-header>
        <div class="d-flex flex-wrap gap-3">
           
            <div>
                <button type="button" class="btn btn-primary" data-modal="export">
                <i class="ph ph-download-simple me-1"></i> {{ __('messages.export') }}
                </button>
                {{-- <button type="button" class="btn btn-secondary" data-modal="import">--}}
                {{-- <i class="fa-solid fa-upload"></i> Import--}}
                {{-- </button>--}}
            </div>
        </div>
        <x-slot name="toolbar">

            <div>
                <div class="datatable-filter">
                    <select name="column_status" id="column_status" class="select2 form-control" data-filter="select" style="width: 100%">
                        <option value="">{{__('messages.all')}}</option>
                        <option value="0" {{ $filter['payment_status'] == '0' ? 'selected' : '' }}>
                            {{ __('appointment.pending') }}
                        </option>
                        <option value="1" {{ $filter['payment_status'] == '1' ? 'selected' : '' }}>
                            {{ __('appointment.paid') }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="input-group flex-nowrap">
                <span class="input-group-text" id="addon-wrapping"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..." aria-label="Search" aria-describedby="addon-wrapping">
            </div>
            <button class="btn btn-secondary d-flex align-items-center gap-1 btn-group" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample"><i class="ph ph-funnel"></i>{{__('messages.advance_filter')}}</button>

            @hasPermission('add_billing_record')
            <x-buttons.offcanvas target='#form-offcanvas' title="{{ __('messages.create') }} {{ __($module_title) }}">
                {{ __('messages.new') }}</x-buttons.offcanvas>
            @endhasPermission

        </x-slot>
    </x-backend.section-header>
    <table id="datatable" class="table table-responsive">
    </table>
</div>
<div data-render="app">

    <billing-record-offcanvas create-title="{{ __('messages.create') }} {{ __($module_title) }}" edit-title="{{ __('messages.edit') }} {{ __($module_title) }}">
    </billing-record-offcanvas>
    <patient-encounter-dashboard  create-title="{{ __('appointment.encouter_dashboard') }}" >
    </patient-encounter-dashboard>

</div>
<x-backend.advance-filter>
    <x-slot name="title">
        <h4>{{ __('service.lbl_advanced_filter') }}</h4>
    </x-slot>
    <div class="form-group datatable-filter">
        <div class="row align-items-center">
            <div class="col-3">
                <label class="form-label" for="patient_name">{{__('clinic.patient')}}</label>
            </div>
            <div class="col-9">
                <select name="patient_name" id="patient_name" class="form-control select2" data-filter="select"
                    data-ajax--url="{{ route('backend.get_search_data', ['type' => 'customers']) }}"
                        data-ajax--cache="true">
                </select>
            </div>
        </div>
    </div>
    <div class="form-group datatable-filter">
        <div class="row align-items-center">
            <div class="col-3">
                <label for="form-label" class="form-label"> {{ __('clinic.lbl_clinic') }}</label>
            </div>
            <div class="col-9">
                <select  id="clinic_name" name="clinic_name" data-filter="select"
                    class="select2 form-control"
                    data-ajax--url="{{ route('backend.get_search_data', ['type' => 'clinic_name']) }}"
                    data-ajax--cache="true">
                </select>
            </div>
        </div>
     </div>
     <div class="form-group datatable-filter">
        <div class="row align-items-center">
            <div class="col-3">
                <label for="form-label" class="form-label"> {{ __('clinic.doctors') }}</label>
            </div>
            <div class="col-9">
                <select  id="doctor_name" name="doctor_name" data-filter="select"
                    class="select2 form-control"
                    data-ajax--url="{{ route('backend.get_search_data', ['type' => 'doctors']) }}"
                    data-ajax--cache="true">
                </select>
            </div>
        </div>
     </div>
     <div class="form-group datatable-filter">
        <div class="row align-items-center">
            <div class="col-3">
                <label class="form-label" for="service_name">{{ __('service.singular_title') }}</label>
            </div>
            <div class="col-9">
                <select name="service_name" id="service_name" class="form-control select2" data-filter="select">
                    <option value=""></option>
                    @foreach ($service as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <button type="reset" class="btn btn-danger" id="reset-filter">{{ __('appointment.reset') }}</button>
</x-backend.advance-filter>
@endsection

@push ('after-styles')
<link rel="stylesheet" href="{{ mix('modules/appointment/style.css') }}">
<!-- DataTables Core and Extensions -->
<link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push ('after-scripts')
<script src="{{ mix('modules/appointment/script.js') }}"></script>
<script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>
<script src="{{ asset('js/form-modal/index.js') }}" defer></script>

<!-- DataTables Core and Extensions -->
<script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>

<script type="text/javascript" defer>
    const columns = [
        // {
        //     name: 'check',
        //     data: 'check',
        //     title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
        //     width: '0%',
        //     exportable: false,
        //     orderable: false,
        //     searchable: false,
        // },

        {
            data: 'encounter_id',
            name: 'encounter_id',
            title: "{{ __('appointment.lbl_encounter_id') }}"
        },

        {
            data: 'user_id',
            name: 'user_id',
            title: "{{ __('sidebar.patient') }}"
        },

        {
            data: 'clinic_id',
            name: 'clinic_id',
            title: "{{ __('appointment.lbl_clinic') }}",
            orderable: true,
            searchable: true,
        },

        {
            data: 'doctor_id',
            name: 'doctor_id',
            title: "{{ __('appointment.lbl_doctor') }}",
            orderable: true,
            searchable: true,
        },

        {
            data: 'service_id',
            name: 'service_id',
            title: "{{ __('appointment.lbl_service') }}",
            orderable: true,
            searchable: true,
        },

        {
            data: 'total_amount',
            name: 'total_amount',
            title: "{{ __('appointment.lbl_total_amount') }}",
            orderable: true,
            searchable: true,
        },


        {
            data: 'date',
            name: 'date',
            title: "{{ __('appointment.lbl_date') }}",
            orderable: true,
            searchable: true,
        },

        {
            data: 'payment_status',
            name: 'payment_status',
            title: "{{ __('appointment.lbl_payment_status') }}",
            orderable: true,
            searchable: true,
        },


        {
            data: 'updated_at',
            name: 'updated_at',
            title: "{{ __('appointment.lbl_update_at') }}",
            orderable: true,
            visible: false,
        },
      
    ]


    const actionColumn = [{
        data: 'action',
        name: 'action',
        orderable: false,
        searchable: false,
        title: "{{ __('appointment.lbl_action') }}",
        width: '5%'
    }]



    let finalColumns = [
        ...columns,
        ...actionColumn
    ]

    document.addEventListener('DOMContentLoaded', (event) => {

        initDatatable({
            url: '{{ route("backend.$module_name.index_data") }}',
            finalColumns,
            orderColumn: [[ 8, 'desc' ]],
            advanceFilter: () => {
                return {
                    patient_name: $('#patient_name').val(),
                    clinic_name: $('#clinic_name').val(),
                    doctor_name: $('#doctor_name').val(),
                    service_name: $('#service_name').val(),
                }
            }
        });
        $('#reset-filter').on('click', function(e) {
            $('#patient_name').val('');
            $('#clinic_name').val('');
            $('#doctor_name').val('');
            $('#service_name').val('');
            window.renderedDataTable.ajax.reload(null, false);
        });
    })


    function resetQuickAction() {
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

        $('#quick-action-type').change(function() {
            resetQuickAction()
        });
</script>
@endpush
