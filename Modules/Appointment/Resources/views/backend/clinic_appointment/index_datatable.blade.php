@extends('backend.layouts.app')

@section('title') {{ __($module_title) }} @endsection

@section('content')
<div class="table-content mb-5">
    <x-backend.section-header>
        <div class="d-flex flex-wrap gap-3">
            @if(auth()->user()->can('delete_clinic_appointment_list'))
            <x-backend.quick-action url="{{ route('backend.appointments.bulk_action') }}">
                <div class="">
                    <select name="action_type" class="form-control select2 col-12" id="quick-action-type" style="width:100%">
                        <option value="">{{ __('messages.no_action') }}</option>
                        
                        @can('delete_clinic_appointment_list')
                        <option value="delete">{{ __('messages.delete') }}</option>
                        @endcan
                    </select>
                </div>
                
            </x-backend.quick-action>
            @endif
            <div>
                <button type="button" class="btn btn-primary" data-modal="export">
                <i class="ph ph-download-simple me-1"></i>{{ __('messages.export') }}
                </button>
                {{-- <button type="button" class="btn btn-secondary" data-modal="import">--}}
                {{-- <i class="fa-solid fa-upload"></i> Import--}}
                {{-- </button>--}}
            </div>
        </div>
        <x-slot name="toolbar">

            <div>
                <div class="datatable-filter status-filter">
                    <select name="column_status" id="column_status" class="select2 form-control" data-filter="select" style="width: 100%">
                        <option value="">{{__('messages.all')}}</option>
                        <option value="pending" {{ $filter['status'] == 'pending' ? 'selected' : '' }}>
                            {{ __('appointment.pending') }}
                        </option>
                        <option value="confirmed" {{ $filter['status'] == 'confirmed' ? 'selected' : '' }}>
                            {{ __('appointment.confirmed') }}
                        </option>
                        <option value="check_in" {{ $filter['status'] == 'check_in' ? 'selected' : '' }}>
                            {{ __('appointment.check_in') }}
                        </option>
                        <option value="checkout" {{ $filter['status'] == 'checkout' ? 'selected' : '' }}>
                            {{ __('appointment.checkout') }}
                        </option>
                        <option value="cancelled" {{ $filter['status'] == 'cancelled' ? 'selected' : '' }}>
                            {{ __('appointment.cancelled') }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="input-group flex-nowrap">
                <span class="input-group-text" id="addon-wrapping"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..." aria-label="Search" aria-describedby="addon-wrapping">
            </div>
            <button class="btn btn-secondary d-flex align-items-center gap-1 btn-group" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample"><i class="ph ph-funnel"></i>{{__('messages.advance_filter')}}</button>

            @hasPermission('add_clinic_appointment_list')
            <x-buttons.offcanvas target='#form-offcanvas' title="{{ __('messages.create') }} {{ __($module_title) }}">
                {{ __('messages.new') }} </x-buttons.offcanvas>
            @endhasPermission

        </x-slot>
    </x-backend.section-header>
    <table id="datatable" class="table position-relative">
    </table>
</div>
<div data-render="app">
    <clinic-appointment-offcanvas create-title="{{ __('messages.create') }} {{ __($module_title) }}" edit-title="{{ __('messages.edit') }} {{ __($module_title) }}" :customefield="{{ json_encode($customefield) }}">
    </clinic-appointment-offcanvas>

    <patient-encounter-dashboard  create-title="{{ __('appointment.encouter_dashboard') }}" >
    </patient-encounter-dashboard>
    <appointment-offcanvas>
    </appointment-offcanvas>
</div>

<x-backend.advance-filter>
    <x-slot name="title">
        <h4>{{ __('service.lbl_advanced_filter') }}</h4>
    </x-slot>
    <div class="form-group datatable-filter">
        <label class="form-label" for="patient_name">{{__('clinic.patient')}}</label>
        <input type="text" name="patient_name" id="patient_name" class="form-control" placeholder="{{__('messages.patient_name')}}">
    </div>
    <div class="form-group datatable-filter">
        <label class="form-label" for="service_name">{{ __('service.singular_title') }}</label>
        <select name="service_name" id="service_name" class="form-control select2" data-filter="select">
            <option value="">{{ __('service.all') }} {{ __('service.singular_title') }}</option>
            @foreach ($service as $service)
                <option value="{{ $service->id }}">{{ $service->name }}</option>
            @endforeach
        </select>
    </div>
    @unless(auth()->user()->hasRole('doctor'))
    <div class="form-group datatable-filter">
        <label class="form-label" for="doctor_id">{{ __('clinic.doctor_title') }}</label>
        <select name="doctor_id" id="doctor_id" class="form-control select2" data-filter="select">
            <option value="">{{ __('service.lbl_doctor') }}  </option>
            @foreach ($doctor as $doctor)
                <option value="{{ $doctor->doctor_id }}">{{ optional($doctor->user)->full_name }}</option>
            @endforeach
        </select>
    </div>
    @endunless
    <button type="reset" class="btn btn-danger" id="reset-filter">{{ __('appointment.reset') }}</button>
</x-backend.advance-filter>
@endsection

@push ('after-styles')
<link rel="stylesheet" href="{{ mix('modules/appointment/style.css') }}">
<!-- DataTables Core and Extensions -->
<link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush
<style>
    .disabled-cell {
    background-color: #e9ecef;
    pointer-events: none;
    opacity: 0.5;
}
</style>
@push ('after-scripts')
<script src="{{ mix('modules/appointment/script.js') }}"></script>
<script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>
<script src="{{ asset('js/form-modal/index.js') }}" defer></script>

<!-- DataTables Core and Extensions -->
<script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
<script>
    const userRoles = @json(auth()->user()->roles->pluck('name')->toArray());
</script>
<script type="text/javascript" defer>

    const columns = [
        @unless(auth()->user()->hasRole('doctor') || auth()->user()->hasRole('receptionist') || auth()->user()->hasRole('user'))
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
            data: 'id',
            name: 'id',
            title: "{{ __('appointment.lbl_id') }}",
            searchable: false,
            orderable: true,

        },
        {
            data: 'user_id',
            name: 'user_id',
            title: "{{ __('sidebar.patient') }}",
            orderable: true,
        },
        {
            data: 'start_date_time',
            name: 'start_date_time',
            title: "{{ __('appointment.lbl_date_time') }}",
            orderable: true,
        },
        {
            data: 'services',
            name: 'services',
            title: "{{ __('appointment.lbl_service') }}",
            orderable: true,
            searchable: true,
            width: '10%'
        },
        {
            data: 'service_amount',
            name: 'service_amount',
            title: "{{ __('appointment.price') }}",
            orderable: true,
            searchable: true,
        },
        @unless(auth()->user()->hasRole('doctor'))
        {
            data: 'doctor_id',
            name: 'doctor_id',
            title: "{{ __('appointment.lbl_doctor') }}",
            orderable: true,
            searchable: true,
        },
        @endunless
        {
            data: 'updated_at',
            name: 'updated_at',
            title: "{{ __('appointment.lbl_update_at') }}",
            orderable: true,
            visible: false,
        },
        {
            data: 'status',
            name: 'status',
            orderable: true,
            searchable: true,
            title: "{{ __('appointment.lbl_status') }}",
            width: '5%',
            createdCell: function(td, cellData, rowData, row, col) {
                if (userRoles.includes('user')) {
                    $(td).addClass('disabled-cell');
                    $(td).attr('title', 'You do not have permission to edit this field');
                }
            }
        },
        {
            data: 'payment_status',
            name: 'payment_status',
            orderable: false,
            searchable: false,
            title: "{{ __('appointment.lbl_payment_status') }}",
            width: '10%',
            createdCell: function(td, cellData, rowData, row, col) {
                if (userRoles.includes('user')) {
                    $(td).addClass('disabled-cell');
                    $(td).attr('title', 'You do not have permission to edit this field');
                }
            }
        },

    ]

    
    const actionColumn = [
        @unless(auth()->user()->hasRole('user'))
        {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            title: "{{ __('appointment.lbl_action') }}",
            width: '5%'
        }
        @endunless
    ]
    

    const customFieldColumns = JSON.parse(@json($columns))

    let finalColumns = [
        ...columns,
        ...customFieldColumns,
        ...actionColumn
    ]

    document.addEventListener('DOMContentLoaded', (event) => {
        $('#patient_name').on('input', function() {
        window.renderedDataTable.ajax.reload(null, false);
    });
        initDatatable({
            url: '{{ route("backend.$module_name.index_data", ["user_id" => $user_id, "doctor_id" => $doctor_id, "clinic_id" => $clinic_id ]) }}',
            finalColumns,
            orderColumn: @if(auth()->user()->hasRole('doctor'))
                            [[5, "desc"]]
                        @else
                            [[7, "desc"]]
                        @endif,
            advanceFilter: () => {
                const patientFilter = $('#patient_name').val();
                return {
                    doctor_id: $('#doctor_id').val(),
                    patient_name: patientFilter,
                    service_id: $('#service_name').val(),
                }
            }
        });
        $('#reset-filter').on('click', function(e) {
    $('#doctor_id,#patient_name,#service_name').val('');
    window.renderedDataTable.ajax.reload(null, false);
});
    });
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
