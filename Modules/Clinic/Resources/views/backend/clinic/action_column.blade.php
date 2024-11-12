<div class="d-flex gap-3 align-items-center">
 <button type='button' data-assign-module="{{$data->id}}" data-assign-target='#clinic-session-form' data-assign-event='clinic_session' class='btn text-primary p-0 fs-5' data-bs-toggle='tooltip' title='Session'><i class='ph ph-clock-user align-middle'></i></button>
 @hasPermission('clinics_center_gallery')
 <button type='button' data-gallery-module="{{$data->id}}" data-gallery-target='#clinic-gallery-form' data-gallery-event='branch_gallery' class='btn text-info p-0 fs-5' data-bs-toggle="tooltip" title="{{ __('clinic.gallery_for_clinic') }}"><i class="ph ph-image align-middle"></i></button>
@endhasPermission
<button type='button' data-assign-module="{{$data->id}}" data-assign-target='#clinicDetails-offcanvas' data-assign-event='clinic-details' class='btn text-secondary p-0 fs-5' data-bs-toggle='tooltip' title='View'><i class="ph ph-eye align-middle"></i>
</button>

 @hasPermission('edit_clinics_center')
 <button type="button" class="btn text-success p-0 fs-5" data-crud-id="{{ $data->id }}" title="{{ __('messages.edit') }} " data-bs-toggle="tooltip"> <i class="ph ph-pencil-simple-line align-middle"></i></button>
 @endhasPermission
@hasPermission('delete_clinics_center')
    <a href="{{ route('backend.clinics.destroy', $data->id) }}" id="delete-{{ $module_name }}-{{ $data->id }}"
        class="btn text-danger p-0 fs-5" data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}"
        data-bs-toggle="tooltip" title="{{ __('messages.delete') }}" data-confirm="{{ __('messages.are_you_sure?') }}">
        <i class="ph ph-trash align-middle"></i></a>
@endhasPermission
</div>
