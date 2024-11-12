@extends('backend.layouts.app')

@section('title')
{{ __($module_title) }}
@endsection
@section('content')
<b-row>
    <b-col sm="12">
        <div id="bill">

            @foreach($data as $info)

            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                                <p class="mb-0">{{ __('messages.invoice_id') }} :<span class="text-secondary"> #{{ $info['id'] ?? '--' }}</span></h3>
                                <p class="mb-0">
                                    {{ __('messages.payment_status') }}
                                    @if($info['appointmenttransaction']['payment_status'] == 1)
                                    <span class="text-capitalize badge bg-success-subtle p-2">{{ __('messages.paid') }}</span>
                                    @else
                                    <span class="text-capitalize badge bg-soft-danger p-2">{{ __('messages.unpaid') }}</span>
                                    @endif
                                </p>
                            </div>
                            @php
                            $setting = App\Models\Setting::where('name', 'date_formate')->first();
                            $dateformate = $setting ? $setting->val : 'Y-m-d';
                            $setting = App\Models\Setting::where('name', 'time_formate')->first();
                            $timeformate = $setting ? $setting->val : 'h:i A';
                            $createdDate = isset($info['created_at']) ? date($dateformate, strtotime($info['created_at'] ?? '--' )) : '--';
                            @endphp
                            <p class="mb-0 mt-1">
                                {{ __('messages.created_at') }} : <span class="font-weight-bold text-dark"> {{ $createdDate }}</span>
                            </p>

                        </div>
                    </div>
                </div>
            </div>

            <div class="row gy-3">
                <div class="col-md-6 col-lg-6">
                    <h5 class="mb-3">Clinic Info</h5>
                    <div class="card card-block card-stretch card-height mb-0">
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-3">
                                <div class="image-block">
                                    <img src="{{$info['cliniccenter']['file_url'] ?? '--'}}" class="img-fluid avatar avatar-50 rounded-circle" alt="image">
                                </div>
                                <div class="content-detail">
                                    <h5 class="mb-2">{{ $info['cliniccenter']['name'] ?? '--' }}</h5>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        <i class="ph ph-envelope"></i>
                                        <u class="text-secondary">{{ $info['cliniccenter']['email'] ?? '--' }}</u>
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <i class="ph ph-map-pin"></i>
                                        <span>{{ $info['cliniccenter']['address'] ?? '--' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6">
                    <h5 class="mb-3">{{ __('messages.patient_detail') }}</h5>
                    <div class="card card-block card-stretch card-height mb-0">
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-3">
                                <div class="image-block">
                                    <img src="{{$info['user']['profile_image'] ?? '--'}}" class="img-fluid avatar avatar-50 rounded-circle" alt="image">
                                </div>
                                <div class="content-detail">
                                    <h5 class="mb-2">{{ $info['user']['first_name'] .''.$info['user']['last_name'] ?? '--' }}</h5>
                                    <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                                        @if($info['user']['gender']!== null)
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ph ph-user text-dark"></i>
                                            <span class="">{{ $info['user']['gender'] ?? '--' }}</span>
                                        </div>
                                        @endif
                                        @if($info['user']['date_of_birth'] !== null)
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ph ph-cake text-dark"></i>
                                            <span class="">{{ date($dateformate, strtotime($info['user']['date_of_birth']))  ?? '--' }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row gy-3">
                <div class="col-md-12 col-lg-12">
                    <h5 class="mb-3 mt-3">{{ __('messages.service') }}</h5>
                    <div class="card card-block card-stretch card-height mb-0">
                        <div class="card-body">

                            @if(!isset($info['patient_encounter']))
                            <div class="content-detail">
                                <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                                    <span>{{ __('messages.item_name') }}</span>
                                    <span class="text-dark">{{ $info['clinicservice']['name'] ?? '--' }}</span>
                                </div>
                                <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                                    <span>{{ __('messages.price') }}</span>
                                    <span class="text-dark">{{ Currency::format($info['service_price']) ?? '--' }}</span>
                                </div>
                                <!-- <div class="d-flex flex-wrap align-items-center justify-content-between">
                                        <span>{{ __('messages.total') }}</span>
                                        <span class="text-dark">{{ Currency::format($info['service_amount']) ?? '--' }}</span>
                                    </div> -->
                            </div>
                            @endif

                            @if(isset($info['patient_encounter']) && !empty($info['patient_encounter']['billingrecord']) && !empty($info['patient_encounter']['billingrecord']['billing_item']))

                            @foreach ($info['patient_encounter']['billingrecord']['billing_item'] as $billingItem)
                            <div class="d-flex align-items-center bg-body p-4 rounded">
                                <div class="detail-box bg-white rounded">
                                    <img src="{{ $billingItem['clinicservice']['file_url'] ?? default_file_url() }}" alt="avatar" class="avatar avatar-80 rounded-pill">
                                </div>

                                <div class="ms-3 w-100 d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="d-flex align-items-center">
                                        <span>
                                            <b>{{ $billingItem['clinicservice']['name'] ?? 'N/A' }}</b>
                                            {{ $billingItem['clinicservice']['description'] ?? ' ' }}
                                        </span>
                                    </div>

                                    @php
                                    // Calculate the payable amount based on discount type
                                    if ($billingItem['discount_type'] === 'percentage') {
                                    $payable_Amount = $billingItem['service_amount'] - ($billingItem['service_amount'] * ($billingItem['discount_value'] / 100));
                                    } else {
                                    $payable_Amount = $billingItem['service_amount'] - $billingItem['discount_value'];
                                    }
                                    @endphp

                                    @if ($billingItem['discount_value'] > 0)
                                    <h5 class="mb-0 w-50 text-lg-end text-sm-start">
                                        {{ Currency::format($payable_Amount) }}
                                        @if ($billingItem['discount_type'] === 'percentage')
                                        (<span>{{ $billingItem['discount_value'] ?? '--' }}%</span>) off
                                        @else
                                        (<span>{{ Currency::format($billingItem['discount_value']) ?? '--' }}</span>) off
                                        @endif
                                    </h5>
                                    <del>{{ Currency::format($billingItem['service_amount']) }}</del>
                                    @else
                                    <h5 class="mb-0 w-50 text-lg-end text-sm-start">{{ Currency::format($billingItem['total_amount']) }}</h5>
                                    @endif
                                </div>
                            </div>

                            @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @php $service_total_amount = 0; $total_tax = 0; @endphp
            @foreach (optional(optional($info['patient_encounter'])['billingrecord'])['billing_item'] as $item)
            @php
            $service_total_amount += $item['total_amount'];
            @endphp
            @endforeach
            @if($info['appointmenttransaction']['tax_percentage'] !== null)
            @php
            $tax = $info['appointmenttransaction']['tax_percentage'];
            $taxData = json_decode($tax, true);

            $total_amount = $info['service_amount'] ?? 0;
            $transaction = optional(optional($info['patient_encounter'])['billingrecord']) ? optional(optional($info['patient_encounter'])['billingrecord']) : null;
            if($transaction['final_discount_type'] == 'percentage'){
                            $discount_amount = $service_total_amount * ($transaction['final_discount_value'] / 100);
                            }else{
                            $discount_amount = $transaction['final_discount_value'];
                            }
                            $sub_total = $service_total_amount - $discount_amount;
            @endphp
            <div class="row gy-3 mt-4">
                <div class="col-sm-12">
                    <h5 class="mb-3">{{ __('report.lbl_taxes') }}</h5>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                                <span>{{ __('messages.total') }}</span>
                                <span class="text-dark">{{ Currency::format($service_total_amount) }}</span>
                            </div>
                            @if($transaction['final_discount'] == 1)
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                                <span>{{ __('messages.discount') }}(
                                    @if($transaction['final_discount_type'] === 'percentage')
                                    <span class="text-dark">{{ $transaction['final_discount_value'] ?? '--' }}%</span>
                                    @else
                                    <span class="text-dark">{{ Currency::format($transaction['final_discount_value']) ?? '--' }}</span>
                                    @endif
                                    )
                                </span>
                                <span class="text-dark">{{ Currency::format($discount_amount) ?? '--' }}</span>
                            </div>
                            
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                                <span>{{ __('messages.sub_total') }}</span>
                                <span class="text-dark">{{ Currency::format($sub_total) }}</span>
                            </div>
                            @endif

                            @foreach($taxData as $taxPercentage)
                            @php
                            $taxTitle = $taxPercentage['title'];
                            @endphp
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                                <span>
                                    @if($taxPercentage['type'] == 'fixed')
                                    {{ $taxTitle }} ({{ Currency::format($taxPercentage['value']) ?? '--' }})
                                    @else
                                    {{ $taxTitle }} ({{ $taxPercentage['value'] ?? '--' }}%)
                                    @endif
                                </span>
                                <span class="text-dark">
                                    @if($taxPercentage['type'] == 'fixed')
                                    <?php
                                    $total_tax += $taxPercentage['value'];
                                    ?>
                                    {{ Currency::format($taxPercentage['value']) ?? '--' }}
                                    @else
                                    <?php
                                    $tax_amount = $sub_total * $taxPercentage['value'] / 100;
                                    if ($sub_total > 0) {
                                        $tax_amount = $sub_total * $taxPercentage['value'] / 100;
                                    }
                                    $total_tax += $tax_amount;
                                    ?>
                                    {{ Currency::format($tax_amount) ?? '--' }}
                                    @endif
                                </span>
                            </div>
                            @endforeach

                            @php
                            $amount_total = 0;
                            $discount_amount = 0;

                            $transaction = optional(optional($info['patient_encounter'])['billingrecord']) ? optional(optional($info['patient_encounter'])['billingrecord']) : null;
                            if($transaction['final_discount_type'] == 'percentage'){
                            $discount_amount = $service_total_amount * ($transaction['final_discount_value'] / 100);
                            }else{
                            $discount_amount = $transaction['final_discount_value'];
                            }

                            $amount_due = $service_total_amount + $total_tax - $discount_amount;
                            @endphp                            <hr class="border-top border-gray">
                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                                <span class="text-dark">{{ __('messages.grand_total') }}</span>
                                <span class="text-secondary">{{ Currency::format($amount_due) ?? '--' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="d-flex justify-content-end align-items-center ">
                <a class="btn btn-primary" href="{{ route('backend.appointments.download_invoice', ['id' => $info['id']]) }}">
                    <i class="fa-solid fa-download"></i>
                    {{__('appointment.lbl_download')}}
                </a>
            </div>







            <hr class="my-3" />


            <!-- <div class="d-flex justify-content-end align-items-center">
                <a href="{{ route('backend.appointments.download_invoice', ['id' => $info['id']]) }}" class="btn text-info p-0 fs-4" data-bs-toggle="tooltip" title="{{ __('clinic.invoice_detail') }}"><i class="ph ph-file-pdf"></i></a>
            </div> -->
            @endforeach
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
@endpush