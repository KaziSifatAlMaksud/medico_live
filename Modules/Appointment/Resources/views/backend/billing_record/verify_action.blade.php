@if ($data->payment_status) 

<span class="badge booking-status bg-success-subtle p-2">{{__('appointment.paid')}} </span>
                  
@else

<span class="badge booking-status bg-danger-subtle py-2 px-3">{{__('messages.unpaid')}} </span>

@endif