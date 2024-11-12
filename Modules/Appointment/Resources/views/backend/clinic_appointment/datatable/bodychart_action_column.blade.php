<div class="text-end d-flex gap-3 align-items-center">
    {{-- <a href="{{ route('backend.appointments.view') }}" class="btn btn-icon text-danger p-0 fs-4" data-bs-placement="top" class='fs-4 text-info border-0 bg-transparent text-nowrap' data-bs-toggle="tooltip" title="View"> <i class="ph ph-eye"></i> </a> --}}
    <a href="{{route("backend.bodychart.editbodychartview", $data->id)}}" class="btn text-success p-0 fs-4" data-crud-id="{{ $data->id }}" title="{{ __('messages.edit') }} " data-bs-toggle="tooltip"> <i class="ph ph-pencil-simple-line"></i> </a>

    <a href="{{route("backend.bodychart.bodychartdestroy", $data->id)}}" id="delete-{{$module_name}}-{{$data->id}}" class="btn text-danger p-0 fs-4" data-type="ajax" data-method="DELETE" data-token="{{csrf_token()}}" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-confirm="{{ __('messages.are_you_sure?') }}"> <i class="ph ph-trash"></i></a>
</div>
