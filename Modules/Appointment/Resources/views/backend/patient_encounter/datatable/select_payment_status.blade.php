@if(isset($data->appointmenttransaction))
    @if($data->appointmenttransaction->payment_status != 1)
    <select name="branch_for" class="select2 change-select" data-token="{{csrf_token()}}"
        data-url="{{route('backend.appointment.updatePaymentStatus', ['id' => $data->id, 'action_type' => 'update-payment-status'])}}"
        style="width: 100%;">
        @foreach ($payment_status as $key => $value )
        <option value="{{$key}}" {{$data->appointmenttransaction->payment_status  == $key ? 'selected' : ''}}>
            {{$value['title']}}</option>
        @endforeach
    </select>
    @else
    @foreach ($payment_status as $key => $value )
        @if(isset($data->appointmenttransaction))
            @if($data->appointmenttransaction->payment_status==$key)
                <span class="text-capitalize badge bg-soft-info p-3">{{$value['title']}}</span>
            @endif
        @endif
    @endforeach
    @endif 
@else
<span class="text-capitalize badge bg-soft-danger p-3">Failed</span>
@endif 