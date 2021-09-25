@extends('layouts.app')

@section('content')
<div class="content">
    <div class="container">

        <div class="row">
            <div class="col-sm-12">
                <h4 class="page-title">Dashboard</h4>
                <p class="text-muted page-title-alt">Welcome !</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-lg-6">
                <div class="widget-bg-color-icon card-box fadeInDown animated">
                    <div class="bg-icon bg-icon-info pull-left">
                        <i class="md md-attach-money text-info"></i>
                    </div>
                    <div class="text-right">
                        <h3 class="text-dark"><b class="counter">{{!empty($seller) ? count($seller) : 0}}</b></h3>
                        <p class="text-muted">Seller Count</p>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>

            <div class="col-md-6 col-lg-6">
                <div class="widget-bg-color-icon card-box">
                    <div class="bg-icon bg-icon-pink pull-left">
                        <i class="md md-add-shopping-cart text-pink"></i>
                    </div>
                    <div class="text-right">
                        <h3 class="text-dark"><b class="counter">{{!empty($buyer) ? count($buyer) : 0}}</b></h3>
                        <p class="text-muted">Buyer Count</p>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>

            {{-- <div class="col-md-6 col-lg-3">
                <div class="widget-bg-color-icon card-box">
                    <div class="bg-icon bg-icon-purple pull-left">
                        <i class="md md-equalizer text-purple"></i>
                    </div>
                    <div class="text-right">
                        <h3 class="text-dark"><b class="counter">0.16</b>%</h3>
                        <p class="text-muted">Conversion</p>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="widget-bg-color-icon card-box">
                    <div class="bg-icon bg-icon-success pull-left">
                        <i class="md md-remove-red-eye text-success"></i>
                    </div>
                    <div class="text-right">
                        <h3 class="text-dark"><b class="counter">64,570</b></h3>
                        <p class="text-muted">Today's Visits</p>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div> --}}
        </div>

    </div>
</div>
@endsection
