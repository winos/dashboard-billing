@extends('admin.layouts.app')

@section('panel')
    <div class="row mt-50 mb-none-30">
        <div class="col-xl-12 mb-30">
            <div class="card">
                <div class="card-body">
                    <form >
                        <div class="row">
                            <div class="col-xl-3 col-lg-6 col-md-3 col-sm-6">
                                <div class="form-group">
                                    <x-select-user-type title="Select User Type" />
                                </div>
                            </div> 
                            <div class="col-xl-3 col-lg-6 col-md-3 col-sm-6">
                                <div class="form-group"> 
                                    <select name="currency_code" class="form-control select2 mr-2" >
                                        <option value="">@lang('Select Currency')</option>
                                        @foreach ($currencies as $curr)
                                            <option value="{{ $curr->currency_code }}" @selected(request()->currency_code == $curr->currency_code)>
                                                {{$curr->currency_code}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 col-md-3 col-sm-6">
                                <x-search-date-field :icon="false" />
                            </div>
                            <div class="col-xl-3 col-lg-6 col-md-3 col-sm-6 mt-3 mt-md-0 mt-sm-0">
                                <button class="btn btn--primary input-group-text h-45 w-100" type="submit">
                                    <i class="fas fa-search"></i> @lang('Search')
                                </button>
                            </div>
                        </div>
                    </form>
                    <div id="apex-line" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-lib')
    <script src="{{asset('assets/global/js/apexcharts.min.js')}}"></script>
@endpush

@push('script')
<script>
    (function($){
        "use strict";
        $('select[name=user_type]').val('{{ @request()->user_type }}');
        $('select[name=currency_code]').val('{{ @request()->currency_code }}');
    })(jQuery)
    // apex-line chart
    var options = {
    chart: {
        height: 320,
        type: "area",
        toolbar: {
            show: false
        },
        dropShadow: {
            enabled: true,
            enabledSeries: [0],
            top: -2,
            left: 0,
            blur: 10,
            opacity: 0.08
        },
        animations: {
        enabled: true,
        easing: 'linear',
        dynamicAnimation: {
            speed: 1000
        }
        },
    },
    dataLabels: {
        enabled: false
    },
    series: [
        {
        name: "Total Amount",
        data: @json( $report['trx_amount'])
        }
    ],
    fill: {
        type: "gradient",
        gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.7,
        opacityTo: 0.9,
        stops: [0, 90, 100]
        }
    },
    tooltip: {
        y: {
            formatter: function (val) {
                return "{{__(gs('cur_sym'))}}" + val + " "
            }
        }
    },
    xaxis: {
        categories: @json( @$report['trx_dates'])
    },
    grid: {
        padding: {
        left: 5,
        right: 5
        },
        xaxis: {
        lines: {
            show: true
        }
        },   
        yaxis: {
        lines: {
            show: true
        }
        }, 
    },
    };
    var chart = new ApexCharts(document.querySelector("#apex-line"), options);
    chart.render()
</script>
@endpush 
   

