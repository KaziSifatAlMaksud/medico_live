@extends('backend.layouts.app', ['isNoUISlider' => true])

@section('title')
    {{ __($module_title) }}
@endsection


@push('after-styles')
<link rel="stylesheet" href="{{ mix('modules/service/style.css') }}">
@endpush

@section('content')
<div class="table-content mb-5">
    <x-backend.section-header>
        <div class="d-flex flex-wrap gap-3">
            @if (auth()->user()->can('edit_system_service') ||
            auth()->user()->can('delete_system_service'))
            <x-backend.quick-action url="{{ route('backend.system-service.bulk_action') }}">
                <div class="">
                    <select name="action_type" class="form-control select2 col-12" id="quick-action-type" style="width:100%">
                        <option value="">{{ __('messages.no_action') }}</option>
                        @can('edit_system_service')
                        <option value="change-status">{{ __('messages.status') }}</option>
                        <option value="change-featured">{{ __('messages.featured') }}</option>
                        @endcan
                        @can('delete_system_service')
                        <option value="delete">{{ __('messages.delete') }}</option>
                        @endcan
                    </select>
                </div>
                <div class="select-status d-none quick-action-field" id="change-status-action">
                    <select name="status" class="form-control select2" id="status" style="width:100%">
                        <option value="" selected>{{ __('messages.select_status') }}</option>
                        <option value="1">{{ __('messages.active') }}</option>
                        <option value="0">{{ __('messages.inactive') }}</option>
                    </select>
                </div>
                <div class="select-featured d-none quick-action-field" id="change-featured-action">
                    <select name="featured" class="form-control select2" id="featured" style="width:100%">
                        <option value="" selected>{{ __('messages.select_featured') }}</option>
                        <option value="1">{{ __('messages.yes') }}</option>
                        <option value="0">{{ __('messages.no') }}</option>
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
                    <select name="column_status" id="column_status" class="select2 form-control" data-filter="select" style="width: 100%">
                        <option value="">{{ __('messages.all') }}</option>
                        <option value="0" {{ $filter['status'] == '0' ? 'selected' : '' }}>
                            {{ __('messages.inactive') }}
                        </option>
                        <option value="1" {{ $filter['status'] == '1' ? 'selected' : '' }}>
                            {{ __('messages.active') }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="input-group flex-nowrap">
                <span class="input-group-text" id="addon-wrapping"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..." aria-label="Search" aria-describedby="addon-wrapping">
            </div>
            <button class="btn btn-secondary d-flex align-items-center gap-1 btn-group" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample"><i class="ph ph-funnel"></i>{{ __('messages.advance_filter') }}</button>
            @hasPermission('add_system_service')
            <x-buttons.offcanvas target='#form-offcanvas' title="{{ __('messages.create') }} {{ __('service.singular_title') }}">
                {{ __('messages.new') }} </x-buttons.offcanvas>
            @endhasPermission

        </x-slot>
    </x-backend.section-header>
    <table id="datatable" class="table table-responsive">
    </table>
</div>
<div data-render="app">
    <system-service-offcanvas custom-data="{{ isset($data) ? json_encode($data) : '{}' }}" create-title="{{ __('messages.create') }} {{ __('service.singular_title') }}" default-image="{{default_file_url()}}" edit-title="{{ __('messages.edit') }} {{ __('service.singular_title') }}" :customefield="{{ json_encode($customefield) }}">
    </system-service-offcanvas>

</div>
<x-backend.advance-filter>
    <x-slot name="title">
        <h4>{{ __('service.lbl_advanced_filter') }}</h4>
    </x-slot>

    <div class="form-group datatable-filter">
        <label class="form-label" for="column_category">{{ __('service.lbl_category') }}</label>
        <select name="column_category" id="column_category" class="form-control select2" data-filter="select">
            <option value="">{{ __('service.all') }} {{ __('service.lbl_category') }}</option>
            @foreach ($categories as $category)
            <option value="{{ $category->id }}" data-parent="{{ $category->parent_id }}">{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group datatable-filter d-none" id="subcategory-group">
        <label class="form-label" for="column_subcategory">{{ __('service.lbl_subcategory') }}</label>
        <select name="column_subcategory" id="column_subcategory" class="form-control select2" data-filter="select">
            <option value="">{{ __('service.all') }} {{ __('service.lbl_subcategory') }}</option>
        </select>
    </div>



    <button type="reset" class="btn btn-danger" id="reset-filter">{{ __('messages.reset') }}</button>
    <div class="form-group custom-range">
        <div class="filter-slider slider-secondary"></div>
    </div>
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
            data: 'name',
            name: 'name',
            title: `{{ __('service.lbl_name') }}`
        },


        {
            data: 'category_id',
            name: 'category_id',
            title: `{{ __('service.lbl_category_id') }}`
        },
        {
            data: 'subcategory_id',
            name: 'subcategory_id',
            title: `{{ __('category.parent_category') }}`
        },
        {
            data: 'featured',
            name: 'featured',
            orderable: false,
            searchable: false,
            title: `{{ __('messages.featured') }}`,
            width: '5%'
        },

        {
            data: 'status',
            name: 'status',
            orderable: false,
            searchable: true,
            title: `{{ __('service.lbl_status') }}`,
            width: '5%'
        },

        {
            data: 'updated_at',
            name: 'updated_at',
            title: `{{ __('service.lbl_update_at') }}`,
            orderable: true,
            visible: false,
        },

    ]


    const actionColumn = [{
        data: 'action',
        name: 'action',
        orderable: false,
        searchable: false,
        title: `{{ __('service.lbl_action') }}`,
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
            url: '{{ route("backend.system-service.index_data") }}',
            finalColumns,
            orderColumn: [
                [6, "desc"]
            ],
            advanceFilter: () => {
                return {
                    category_id: $('#column_category').val(),
                    sub_category_id: $('#column_subcategory').val(),
                }
            }
        });

        $('#reset-filter').on('click', function(e) {
            $('#column_category').val('');
            $('#column_subcategory').val('');

            window.renderedDataTable.ajax.reload(null, false);
        });

        // filterSubcategories($('#column_category').val());
    });

    $('#column_category').on('change', function() {
        var selectedCategoryId = $(this).val();
        var subcategoryGroup = $('#subcategory-group');
        var subcategorySelect = $('#column_subcategory');

        subcategorySelect.html('<option value="">All Sub Categories</option>');

        if (selectedCategoryId !== "") {
            var subcategories = {!!$subcategories->toJson()!!};
            var hasSubcategories = subcategories.some(function(subcategory) {
                return subcategory.parent_id == selectedCategoryId;
            });

            if (hasSubcategories) {
                subcategoryGroup.removeClass('d-none');
                subcategories.forEach(function(subcategory) {
                    if (subcategory.parent_id == selectedCategoryId) {
                        $('<option></option>').attr('value', subcategory.id).text(subcategory.name).appendTo(subcategorySelect);
                    }
                });
            } else {
                subcategoryGroup.addClass('d-none');
            }
        } else {
            subcategoryGroup.addClass('d-none');
        }
    });


    function resetQuickAction() {
        const actionValue = $('#quick-action-type').val();
        if (actionValue != '') {
            $('#quick-action-apply').removeAttr('disabled');

            if (actionValue == 'change-status') {
                $('.quick-action-field').addClass('d-none');
                $('#change-status-action').removeClass('d-none');

            } else if (actionValue == 'change-featured') {
                $('.quick-action-field').addClass('d-none');
                $('#change-featured-action').removeClass('d-none');

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

    document.addEventListener('DOMContentLoaded', function() {
        var type = '<?php echo isset($type) ? $type : ''; ?>';
        var data = '<?php echo isset($data) ? json_encode($data) : ''; ?>';
        console.log(type === 'system_service')
        if (type === 'system_service') {
            var myOffcanvas = document.getElementById('form-offcanvas')
            var bsOffcanvas = new bootstrap.Offcanvas(myOffcanvas)
            bsOffcanvas.show()
        }
    });
</script>
@endpush