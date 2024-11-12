@extends('backend.layouts.app')

@section('title')
    {{ __($module_title) }}
@endsection


@push('after-styles')
<link rel="stylesheet" href="{{ mix('modules/constant/style.css') }}">
@endpush
@section('content')
<div class="table-content mb-5">
        <x-backend.section-header>
            <div class="d-flex flex-wrap gap-3">
            @if(auth()->user()->can('edit_doctors') || auth()->user()->can('delete_doctors'))
                <x-backend.quick-action url='{{ route("backend.$module_name.bulk_action") }}'>
                    <div class="">
                        <select name="action_type" class="form-control select2 col-12" id="quick-action-type" style="width:100%">
                            <option value="">{{ __('messages.no_action') }}</option>
                            @hasPermission('edit_doctors')
                            <option value="change-status">{{ __('messages.status') }}</option>
                            @endhasPermission
                            @hasPermission('delete_doctors')
                            <option value="delete">{{ __('messages.delete') }}</option>
                            @endhasPermission
                        </select>
                    </div>
                    <div class="select-status d-none quick-action-field" id="change-status-action">
                        <select name="status" class="form-control select2" id="status" style="width:100%">
                            <option value="" selected>{{ __('messages.select_status') }}</option>
                            <option value="1">{{ __('messages.active') }}</option>
                            <option value="0">{{ __('messages.inactive') }}</option>
                        </select>
                    </div>
                </x-backend.quick-action>
                @endif
                <div>
                    <button type="button" class="btn btn-primary" data-modal="export">
                    <i class="ph ph-download-simple me-1"></i> {{ __('messages.export') }}
                    </button>
      {{--          <button type="button" class="btn btn-secondary" data-modal="import">--}}
      {{--            <i class="ph ph-upload me-1"></i> Import--}}
      {{--          </button>--}}
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
                    <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..."
                        aria-label="Search" aria-describedby="addon-wrapping">
                </div>

                <button class="btn btn-secondary d-flex align-items-center gap-1 btn-group" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample"><i class="ph ph-funnel"></i>{{__('messages.advance_filter')}}</button>

                @hasPermission('add_doctors')

                 <x-buttons.offcanvas target='#form-offcanvas' title="{{ __('Create') }} {{ __($create_title) }}" class=" d-flex align-items-center gap-1">{{ __('messages.new') }}</x-buttons.offcanvas>

                @endhasPermission
                

            </x-slot>
        </x-backend.section-header>
        <table id="datatable" class="table table-responsive">
        </table>
</div>

<div data-render="app">

    <doctor-offcanvas type="{{ __('staff') }}"  
    default-image="{{default_file_url()}}"
    create-title="{{ __('messages.create') }} {{ __($create_title) }}" edit-title="{{ __('messages.edit') }} {{ __($create_title) }}" :customefield="{{ json_encode($customefield) }}">
    </doctor-offcanvas>
    <doctor-details-offcanvas>
    </doctor-details-offcanvas>
    <clinic-list-form-offcanvas></clinic-list-form-offcanvas>
    <employee-slot-mapping-form-offcanvas></employee-slot-mapping-form-offcanvas>
    <change-password :create-title="$t('users.change_password')"></change-password>

    <div data-render="app">
    <doctor-session-offcanvas create-title="{{ __('messages.create') }} {{ __($module_title) }}"
        edit-title="{{ __('messages.edit') }} {{ __($module_title) }}">
    </doctor-session-offcanvas>
  </div>

    <send-push-notification create-title="Send Push Notification"></send-push-notification>
</div>
<x-backend.advance-filter>
    <x-slot name="title">
        <h4>{{ __('service.lbl_advanced_filter') }}</h4>
    </x-slot>
    @unless(auth()->user()->hasRole('doctor'))
    <div class="form-group datatable-filter">
        <label class="form-label" for="doctor_name">{{__('clinic.doctor_name')}}</label>
        <input type="text" name="doctor_name" id="doctor_name" class="form-control" placeholder="{{ __('service.all') }} {{__('clinic.doctors')}}">

      
    </div>
    <div class="form-group datatable-filter">
        <label class="form-label" for="email">{{__('clinic.lbl_Email')}}</label>
        <input type="text" name="email" id="email" class="form-control" placeholder="{{ __('service.all') }} {{__('clinic.lbl_Email')}}">
      
    </div>
    <div class="form-group datatable-filter">
        <label class="form-label" for="contact">{{__('clinic.lbl_contact_number')}}</label>
        <input type="text" name="contact" id="contact" class="form-control" placeholder="{{ __('service.all') }} {{__('clinic.lbl_contact_number')}}">
      
    </div>
    
    <div class="form-group datatable-filter">
        <label class="form-label w-100" for="column_clinic">{{__('clinic.lbl_gender')}}</label>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="gender" id="male" value="male" data-filter="select"/> 
            <label class="form-check-label" for="male"> {{__('clinic.lbl_male')}} </label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="gender" id="female" value="female" data-filter="select"/>
            <label class="form-check-label" for="female"> {{__('clinic.lbl_female')}} </label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="gender" id="intersex" value="intersex" data-filter="select"/>
            <label class="form-check-label" for="intersex"> {{__('Intersex')}} </label>
        </div>
    </div>
    @endunless
    @unless(auth()->user()->hasRole('receptionist'))
    <div class="form-group datatable-filter">
        <label class="form-label" for="column_clinic">{{__('clinic.lbl_clinic')}}</label>
        <select name="column_clinic" id="column_clinic" class="form-control select2" data-filter="select">
            <option value="">{{ __('service.all') }} {{__('clinic.singular_title')}}</option>
            @foreach ($clinic as $clinic)
                <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
            @endforeach
        </select>
    </div>
    @endunless
    @if(multiVendor() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')))
    <div class="form-group datatable-filter">
        <label class="form-label" for="vendor">{{__('clinic.clinic_admin')}}</label>
        <select name="vendor" id="vendor" class="form-control select2" data-filter="select">
            <option value="">{{ __('service.all') }} {{__('clinic.clinic_admin')}}</option>
            @foreach ($vendor as $vendor)
                <option value="{{ $vendor->id }}">{{ $vendor->full_name }}</option>
            @endforeach
        </select>
    </div> 
    @endif
    <button type="reset" class="btn btn-danger" id="reset-filter">{{ __('appointment.reset') }}</button>
</x-backend.advance-filter>
@endsection

@push('after-styles')
<!-- DataTables Core and Extensions -->
<link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push('after-scripts')
<script src="{{ mix('modules/clinic/script.js') }}"></script>
<script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>
<script src="{{ asset('js/form-modal/index.js') }}" defer></script>
<!-- DataTables Core and Extensions -->
<script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
<script type="text/javascript" defer>
    const columns = [{
            name: 'check',
            data: 'check',
            title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
            width: '0%',
            exportable: false,
            orderable: false,
            searchable: false,
        },
        {
            data: 'doctor_id',
            name: 'doctor_id',
            title: "{{__('clinic.lbl_name')}}",
            orderable: true,
            searchable: true,
        },

        {
            data: 'mobile',
            name: 'mobile',
            title: "{{__('clinic.lbl_phone_number')}}"
        },
        @unless(auth()->user()->hasRole('receptionist'))
        {
            data: 'clinic_id',
            name: 'clinic_id',
            title: "{{__('clinic.lbl_clinic_center')}}",
            orderable: false,
            searchable: false,
        },
        @endunless
        {
            data: 'email_verified_at',
            name: 'email_verified_at',
            orderable: false,
            searchable: false,
            title: "{{ __('clinic.lbl_verification_status') }}"
        },



        @if(auth()->user()->can('edit_doctors'))

        {
            data: 'status',
            name: 'status',
            orderable: true,
            searchable: true,
            title: "{{ __('clinic.lbl_status') }}"
        },

        @endif

        {
            data: 'updated_at',
            name: 'updated_at',
            width: '15%',
            visible: false
        },
    ]

      const actionColumn = [{
        data: 'action',
        name: 'action',
        width:'5%',
        orderable: false,
        searchable: false,
        title: "{{ __('clinic.lbl_action') }}"
    }]

    const customFieldColumns = JSON.parse(@json($columns))

    let finalColumns = [
        ...columns,
        ...customFieldColumns,
        ...actionColumn

    ]
    document.addEventListener('DOMContentLoaded', (event) => {
        let selectedGender = null;

    $('input[name="gender"]').change(function() {
        selectedGender = $(this).val(); 
    });

    $('#doctor_name').on('input', function() {
        window.renderedDataTable.ajax.reload(null, false);
    });
    $('#email').on('input', function() {
        window.renderedDataTable.ajax.reload(null, false);
    });
    $('#contact').on('input', function() {
        window.renderedDataTable.ajax.reload(null, false);
    });
        initDatatable({
            url: '{{ route("backend.$module_name.index_data") }}',
            finalColumns,
            orderColumn: [[ 6, 'desc' ]],
            advanceFilter: () => {
                const doctorNameFilter = $('#doctor_name').val();
                const emailFilter = $('#email').val();
                const contactFilter = $('#contact').val();
                return {
                    clinic_name: $('#column_clinic').val(),
                    doctor_name: doctorNameFilter,
                    contact : contactFilter,
                    email : emailFilter,
                    vendor_id : $('#vendor').val(),
                    gender: selectedGender
                }
            }
        });
    $('#reset-filter').on('click', function(e) {
        $('#column_clinic, #doctor_name, #contact, #email, #vendor').val('');
        $('input[name="gender"]').prop('checked', false); 
        selectedGender = null; 
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

    $(document).on('update_quick_action', function() {
        // resetActionButtons()
    })
</script>
@endpush
