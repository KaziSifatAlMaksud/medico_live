@if(isset($data->appointmenttransaction))
    @if($data->appointmenttransaction->payment_status != 1)
    <select name="branch_for" class="select2 change-select" data-token="{{csrf_token()}}"
        data-url="{{route('backend.appointments.updatePaymentStatus', ['id' => $data->id, 'action_type' => 'update-payment-status'])}}"
        style="width: 100%;" {{ $data->status !== 'checkout' ? 'disabled' : '' }}>
        @foreach ($payment_status as $key => $value )
        <option value="{{$value->value}}" {{optional($data->appointmenttransaction)->payment_status  == $value->value ? 'selected' : ''}}>
            {{$value['name']}}</option>
        @endforeach
    </select>
    @else
    @foreach ($payment_status as $key => $value )
   
        @if(isset($data->appointmenttransaction))
            @if($data->appointmenttransaction->payment_status == $value['value'])
                <span class="text-capitalize badge bg-info-subtle p-2">{{$value['name']}}</span>
            @endif
        @endif
    @endforeach
    @endif 
@else
<span class="text-capitalize badge bg-danger-subtle  p-3">Failed</span>
@endif 