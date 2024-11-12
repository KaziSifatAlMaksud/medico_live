@extends('backend.layouts.app', ['isNoUISlider' => true])

@section('title')
{{ $module_title }}
@endsection



@push('after-styles')
<link rel="stylesheet" href="{{ mix('modules/service/style.css') }}">
@endpush

@section('content')
<div class="table-content mb-5">
    <x-backend.section-header>
      <div>
        <x-backend.quick-action url="{{ route('backend.doctor.bulk_action_review') }}">
          <div class="">
            <select name="action_type" class="form-control select2 col-12" id="quick-action-type" style="width:100%">
              <option value="">{{ __('messages.no_action') }}</option>
              <option value="delete">{{ __('messages.delete') }}</option>
            </select>
          </div>
        </x-backend.quick-action>
      </div>
      <x-slot name="toolbar"> 
          <div class="input-group flex-nowrap">
              <span class="input-group-text" id="addon-wrapping"><i
                      class="fa-solid fa-magnifying-glass"></i></span>
              <input type="text" class="form-control dt-search" placeholder= {{ (__('appointment.search')) }} aria-label="Search"
                  aria-describedby="addon-wrapping">
          </div>
      </x-slot>
    </x-backend.section-header>
    <table id="datatable" class="table table-responsive">
    </table>
</div>
<x-backend.advance-filter>
  <x-slot name="title">
    <h4>Advanced Filter</h4>
  </x-slot>
</x-backend.advance-filter>
</div>
@endsection

@push('after-styles')
<!-- DataTables Core and Extensions -->
<link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push('after-scripts')
<!-- DataTables Core and Extensions -->
<script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>

<script type="text/javascript" defer>
  const range_flatpicker = document.querySelectorAll('.booking-date-range')
  Array.from(range_flatpicker, (elem) => {
    if (typeof flatpickr !== typeof undefined) {
      flatpickr(elem, {
        mode: "range",
        dateFormat: "d-m-Y",
      })
    }
  })
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
      data: 'user_id',
      name: 'user_id',
      title: "{{ __('sidebar.patient') }}",
      orderable: true,
      searchable: true,
    },
    {
      data: 'doctor_id',
      name: 'doctor_id',
      title: "{{ __('appointment.lbl_doctor') }}",
      orderable: true,
      searchable: true,
      // width: '10%'
    },
    {
      data: 'service_id',
      name: 'service_id',
      title: "{{ __('appointment.lbl_service') }}",
      orderable: true,
      searchable: true,
      // width: '10%'
    },
    {
      data: 'review_msg',
      name: 'review_msg',
      title: "{{ __('clinic.lbl_message') }}",
      width: '10%',
      className: 'description-column'

    },
    {
      data: 'rating',
      name: 'rating',
      title: "{{ __('clinic.lbl_rating') }}",
      width: '5%'
    },
    {
      data: 'updated_at',
      name: 'updated_at',
      title: "{{ __('messages.date_time') }}",
      orderable: true,
      visible: true,
    }

  ]

  const actionColumn = [
    @if(auth()->user()->hasRole(['admin','demo_admin']))
    {
    data: 'action',
    name: 'action',
    orderable: false,
    searchable: false,
    title: "{{ __('employee.lbl_action') }}",
    width: '5%'
    }
    @endif
  ]



  let finalColumns = [
    ...columns,
    ...actionColumn
  ]


  document.addEventListener('DOMContentLoaded', (event) => {
    initDatatable({
      url: '{{ route("backend.doctor.review_data", ["doctor_id" => $doctor_id ]) }}',
      finalColumns,
      orderColumn: [ 
        @if(auth()->user()->hasRole('doctor'))
          [4, "desc"]
        @else
          [5, "desc"]
        @endif
      ],
      advanceFilter: () => {
        return {
          booking_date: $('#booking_date').val(),

        }
      }
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
  })
</script>
@endpush
