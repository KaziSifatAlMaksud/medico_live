<div class="d-flex gap-3 align-items-center">
    <!-- <a href="{{ route('backend.customers.patient_detail') }}" class="btn p-0 btn-icon text-danger"
    data-bs-placement="top" class='text-primary border-0 bg-transparent text-nowrap' data-bs-toggle="tooltip" title="View"><i class="ph ph-eye"></i></a> -->

    <button type='button' data-assign-module="{{$data->id}}" data-assign-target='#customerDetails-offcanvas' data-assign-event='customer-details' class='btn text-primary p-0 fs-5' data-bs-toggle='tooltip' title='View'><i class="ph ph-eye"></i>
@hasPermission('customer_password')
<button type='button' data-assign-module="{{ $data->id }}" data-assign-target='#Employee_change_password' data-assign-event='employee_assign' class='btn text-info p-0 rounded text-nowrap fs-5' data-bs-toggle="tooltip" title="{{ __('messages.change_password') }}"><i class="ph ph-key"></i></button>
@endhasPermission

@hasPermission('edit_customer')
        <button type="button" class="btn text-success p-0 fs-5" data-crud-id="{{$data->id}}" title="{{ __('messages.edit') }} " data-bs-toggle="tooltip"> <i class="ph ph-pencil-simple-line"></i></button>
    @endhasPermission
    @hasPermission('delete_customer')
        <a href="{{route("backend.$module_name.destroy", $data->id)}}" id="delete-{{$module_name}}-{{$data->id}}" class="btn text-danger p-0 fs-5" data-type="ajax" data-method="DELETE" data-token="{{csrf_token()}}" data-bs-toggle="tooltip" title="{{__('messages.delete')}}" data-confirm="{{ __('messages.are_you_sure?') }}"> <i class="ph ph-trash"></i></a>
    @endhasPermission
</div>



