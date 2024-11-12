@extends('backend.layouts.app')

@section('title')
{{ __($module_title) }}
@endsection
@section('content')


<style type="text/css" media="print">
      @page :footer {
        display: none !important;
      }

      @page :header {
        display: none !important;
      }
      @page { size: landscape; }
      /* @page { margin: 0; } */

      .pr-hide {
        display: none;
        }

        	
    .order_table tr td div{
           white-space: normal;
        }

     
      * {
        -webkit-print-color-adjust: none !important;   /* Chrome, Safari 6 – 15.3, Edge */
        color-adjust: none !important;                 /* Firefox 48 – 96 */
        print-color-adjust: none !important;           /* Firefox 97+, Safari 15.4+ */
      }
    </style>

<b-row>
    <b-col sm="12">
        <div id="bill">
           

            <div class="row pr-hide mb-4">
               
                
                <div class="d-flex justify-content-end align-items-center ">
                            <a class="btn btn-primary" onclick="invoicePrint(this)">
                                <i class="fa-solid fa-download"></i>
                                {{__('messages.print')}}
                            </a>
                        </div>
            </div>
            @php
                use Carbon\Carbon;
            @endphp

            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                                <p class="mb-0">{{ __('messages.invoice_id') }}:<span class="text-secondary"> #{{ $billing['encounter_id'] ?? '--' }}</span></h3>
                                <p class="mb-0">
                                    {{ __('messages.payment_status') }}
                                     @if($billing['payment_status'] == 1)
                                     <span class="badge booking-status bg-success-subtle p-2">{{ __('messages.paid') }}</span>
                                     @else
                                     <span class="badge booking-status bg-danger-subtle p-2">{{ __('messages.unpaid') }}</span>
                                     @endif
                                </p>
                            </div>
                            <p class="mt-1 mb-0">{{ __('messages.date') }}: <span class="font-weight-bold text-dark"> 
                                {{ isset($billing['created_at']) 
                                    ? Carbon::parse($billing['created_at'])->timezone($timezone)->format($dateformate) . ' At ' . Carbon::parse($billing['created_at'])->timezone($timezone)->format($timeformate) 
                                    : '-- At --' 
                                }}</span>
                            </p>
                            
                        </div>
                    </div>                    
                </div>
            </div>

            <div class="row gy-3">
                <div class="col-md-12 col-lg-12">
                    <h5 class="mb-3">Clinic Info</h5>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-3">
                                <div class="image-block">
                                    <img src="{{$billing['clinic']['file_url'] ?? '--'}}" class="img-fluid avatar avatar-50 rounded-circle" alt="image">
                                </div>
                                <div class="content-detail">
                                    <h5 class="mb-2">{{ $billing['clinic']['name'] ?? '--' }}</h5>
                                    <div class="d-flex flex-wrap gap-4">
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <i class="ph ph-envelope text-dark"></i> 
                                            <u class="text-secondary">{{ $billing['clinic']['email'] ?? '--' }}</u>
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <i class="ph ph-map-pin text-dark"></i> 
                                            <span>{{ $billing['clinic']['address'] ?? '--' }}</span>
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <i class="ph ph-phone-call text-dark"></i>
                                            <span>{{ $billing['clinic']['contact_number'] ?? '--' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <h5 class="mb-3">{{ __('messages.doctor_details') }}</h5>
                    <div class="card card-block card-stretch card-height mb-0">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center h-100 gap-3">
                                <div class="image-block">
                                    <img src="{{$billing['doctor']['profile_image'] ?? '--'}}" class="img-fluid avatar avatar-50 rounded-circle" alt="image">
                                </div>
                                <div class="content-detail">
                                    <h5 class="mb-2">Dr. {{ $billing['doctor']['full_name'] ?? '--' }}</h5>
                                    <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <i class="ph ph-envelope text-dark"></i> 
                                            <u class="text-secondary">{{ $billing['doctor']['email'] ?? '--' }}</u>
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <i class="ph ph-phone-call text-dark"></i>
                                            <span>{{ $billing['doctor']['mobile'] ?? '--' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <h5 class="mb-3">{{ __('messages.patient_detail') }}</h5>
                    <div class="card card-block card-stretch card-height mb-0">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center h-100 gap-3">
                                <div class="image-block">
                                    <img src="{{$billing['user']['profile_image'] ?? '--'}}" class="img-fluid avatar avatar-50 rounded-circle" alt="image">
                                </div>
                                <div class="content-detail">
                                    <h5 class="mb-2">{{ $billing['user']['first_name'] .''.$billing['user']['last_name'] ?? '--' }}</h5>
                                    <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                                    @if($billing['user']['gender']!== null)
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ph ph-user text-dark"></i>
                                            <span class="">{{ $billing['user']['gender'] ?? '--' }}</span>
                                        </div>
                                        @endif
                                        @if($billing['user']['date_of_birth']!== null)
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ph ph-cake text-dark"></i>
                                            <span class="">{{date($dateformate, strtotime($billing['user']['date_of_birth']))  ?? '--' }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <h5 class="mb-3">{{ __('messages.service') }}</h5>
                    <div class="card card-block card-stretch card-height mb-0">
                        <div class="card-body">
                            <div class="content-detail">
                                <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                                    <span>{{ __('messages.service_name') }}</span>
                                    <span class="text-dark">{{ $billing['clinicservice']['name'] ?? '--' }}</span>
                                </div>
                                <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                                    <span>{{ __('messages.price') }}</span>
                                    <span class="text-dark">{{ Currency::format($billing['service_amount']) ?? '--' }}</span>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-3" />
            @if(!empty($billing['billingItem']) && $billing['billingItem']->isNotEmpty())
    <div class="row">
        <div class="col-md-12">
        <h5 class="mb-3">{{ __('messages.service') }}</h5>
        </div>
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-bordered border-top order_table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('messages.sr_no') }}</th>
                            <th>{{ __('messages.service_name') }}</th>
                            <th>{{ __('messages.price') }}</th>
                            <th>{{ __('messages.discount') }}</th>
                            <th>{{ __('messages.total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $index = 1 @endphp
                        @foreach($billing['billingItem'] as $item)
                            <tr>
                                <td>{{ $index }}</td>
                                <td>{{ $item->item_name ?? '--' }}</td>
                                <td>{{  Currency::format($item->service_amount) . ' * ' . $item->quantity  ?? '--' }}</td>
                                @if($item->discount_type === 'percentage')
                                    <td>{{ $item->discount_value ?? '--' }}%</td>
                                @else
                                    <td>{{ Currency::format($item->discount_value) ?? '--' }}</td>
                                @endif
                                <td>{{ Currency::format($item->total_amount) ?? '--' }}</td>
                            </tr>
                            @php $index++ @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@else
    <div class="row">
        <div class="col-md-12">
            <h4 class="text-primary mb-0">{{ __('messages.no_record_found') }}</h4>
        </div>
    </div>
@endif

            @if($billing['tax_data']!== null)
            @php
            $tax = $billing['tax_data'];
            $taxData = json_decode($tax, true);
            $total_amount = 0;
            // $total_amount = $billing['service_amount'] ?? 0;
            @endphp
          

    @foreach($billing['billingItem'] as $item)
        @php
            $total_amount += $item->total_amount; // Sum up service amounts
        @endphp
    @endforeach
            <div class="row gy-3 mt-4">
                <div class="col-sm-12">
                    <h5 class="mb-3">{{ __('report.lbl_taxes') }}</h5>
                    <div class="card">
                        <div class="card-body">
                        
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                                <span>{{ __('messages.total') }}</span>
                                <div>{{ Currency::format($total_amount) }}</div>
                            </div>
                            @php
                            $amount_total = 0;
                            // $discount_total = $billing['discount_value'] ?? 0;
                            // $discount_amount = $billing['discount_amount'] ?? 0;
                            $discount_total = $billing['final_discount_value'] ?? 0;
                            if($billing['final_discount_type'] == 'percentage'){
                                $discount_amount =  $total_amount * ($billing['final_discount_value'] / 100);
                            }else{
                                $discount_amount =  $billing['final_discount_value'];
                            }
                            // $discount_amount = $billing['discount_amount'] ?? 0;
                            $amount_due = $total_amount - $discount_amount ?? 0;
                            $subtotal = $total_amount - $discount_amount ?? 0;
                            @endphp
                            @foreach($taxData as $taxPercentage)
                                @php
                                //$percentagetax = ($billing['service_amount'] - $billing['discount_amount'])  * $taxPercentage['value'] / 100;
                                $percentagetax = $subtotal  * $taxPercentage['value'] / 100;
                                $amount_total += $taxPercentage['type'] == 'fixed' ? $taxPercentage['value'] : $percentagetax;
                                $amount_due = $subtotal + $amount_total;
                            
                                @endphp
                                @endforeach
                            <!-- <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                                <span>{{ __('messages.discount') }}
                                (@if($billing['discount_type'] === 'percentage')
                                    <span>{{ $discount_total ?? '--' }}%</span>
                                    @else
                                    <span>{{ Currency::format($discount_total) ?? '--' }}</span>
                                    @endif)
                                    </span>
                               <span class="text-dark">{{ Currency::format($discount_amount) ?? '--' }}</span>
                            </div>
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                                <span>{{ __('messages.grand_total') }}</span>
                                
                                    <div>{{ Currency::format($amount_due) ?? '--' }}</div>
                                   
                                
                            </div> -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                                <span>{{ __('messages.discount') }}
                                (@if($billing['final_discount_type'] === 'percentage')
                                    <span>{{ $discount_total ?? '--' }}%</span>
                                    @else
                                    <span>{{ Currency::format($discount_total) ?? '--' }}</span>
                                    @endif)
                                    </span>
                               <span class="text-dark">- {{ Currency::format($discount_amount) ?? '--' }}</span>
                            </div>
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                                <span>{{ __('booking.sub_total') }}</span>
                                <div>{{ Currency::format($subtotal) }}</div>
                            </div>
                            @foreach($taxData as $taxPercentage)
                                @php
                                $taxTitle = $taxPercentage['title'];
                                $percentagetax = $subtotal  * $taxPercentage['value'] / 100;
                                
                                $taxAmount = $taxPercentage['type'] == 'fixed' ? Currency::format($taxPercentage['value']) : $percentagetax;
                                @endphp
                                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                                    <span> 
                                        @if($taxPercentage['type'] == 'fixed')
                                            {{ $taxTitle }} ({{ $taxAmount ?? '--' }})
                                            @else
                                            {{ $taxTitle }} ({{ $taxPercentage['value'] ?? '--' }}%)
                                            @endif
                                    </span>
                                    <div>
                                    @if($taxPercentage['type'] == 'fixed')
                                            {{ $taxAmount ?? '--' }}
                                            @else
                                            {{ Currency::format($taxAmount) ?? '--' }}
                                            @endif
                                    </div>
                                </div>
                            @endforeach
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                                <span>{{ __('messages.grand_total') }}</span>
                                
                                    <div>{{ Currency::format($billing['final_total_amount']) ?? '--' }}</div>
                                   
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif


              

           
           
           
        </div>

    </b-col>
</b-row>
@endsection

@push('after-styles')
<style>
    .detail-box {
        padding: 0.625rem 0.813rem;
    }
</style>
<link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push('after-scripts')
<script src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
<script src="{{ mix('modules/appointment/script.js') }}"></script>
<script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>
<script>
      function invoicePrint() {
            window.print()
        }

        function updateStatusAjax(__this, url) {
            console.log(url);
            console.log($billing);
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: {
                    id: {{ $billing['id'] }},
                    status: __this.val(),
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.status) {
                        window.successSnackbar(res.message)
                        setTimeout(() => {
                            location.reload()
                        }, 100);
                    }
                }
            });
        }
</script>
@endpush