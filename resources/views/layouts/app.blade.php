@php
    $setting = App\Models\Settings::first();
@endphp
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="A fully featured admin theme which can be used to build CRM, CMS, etc.">
        <meta name="author" content="Coderthemes">

        <link rel="shortcut icon" href="{{asset('public/assets/images/favicon.png')}}">
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>{{!empty($setting) ? $setting->site_name : 'e-Cotton'}}</title>

        <!--Morris Chart CSS -->
		<link rel="stylesheet" href="{{asset('public/assets/plugins/morris/morris.css')}}">

        <link href="{{asset('public/assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('public/assets/css/core.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('public/assets/css/components.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('public/assets/css/icons.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('public/assets/css/pages.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('public/assets/css/responsive.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('public/assets/css/toastr.css')}}" rel="stylesheet" type="text/css" />

        <script src="{{asset('public/assets/js/modernizr.min.js')}}"></script>
    </head>

    <body class="fixed-left">

        @php
            $user = App\Models\User::where('id',Auth::user()->id)->first();
        @endphp

        <!-- Begin page -->
        <div id="wrapper">

            <!-- Top Bar Start -->
            <div class="topbar">

                <!-- LOGO -->
                <div class="topbar-left">
                    <div class="text-center">
                        
                        <a href="javascript:void(0)" class="logo"><span class="icon-c-logo" style="opacity:999;">EC</span><span><img src="{{asset('public/assets/images/favicon.png')}}" style="height:40px;"></span></a>
                    </div>
                </div>

                <!-- Button mobile view to collapse sidebar menu -->
                <div class="navbar navbar-default" role="navigation">
                    <div class="container">
                        <div class="">
                            <div class="pull-left">
                                <button class="button-menu-mobile open-left">
                                    <i class="ion-navicon"></i>
                                </button>
                                <span class="clearfix"></span>
                            </div>

                            <ul class="nav navbar-nav navbar-right pull-right">
                                {{-- <li class="dropdown hidden-xs">
                                    <a href="#" data-target="#" class="dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="true">
                                        <i class="icon-bell"></i> <span class="badge badge-xs badge-danger">3</span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-lg">
                                        <li class="notifi-title"><span class="label label-default pull-right">New 3</span>Notification</li>
                                        <li class="list-group nicescroll notification-list">
                                           <!-- list item-->
                                           <a href="javascript:void(0);" class="list-group-item">
                                              <div class="media">
                                                 <div class="pull-left p-r-10">
                                                    <em class="fa fa-diamond fa-2x text-primary"></em>
                                                 </div>
                                                 <div class="media-body">
                                                    <h5 class="media-heading">A new order has been placed A new order has been placed</h5>
                                                    <p class="m-0">
                                                        <small>There are new settings available</small>
                                                    </p>
                                                 </div>
                                              </div>
                                           </a>

                                           <!-- list item-->
                                           <a href="javascript:void(0);" class="list-group-item">
                                              <div class="media">
                                                 <div class="pull-left p-r-10">
                                                    <em class="fa fa-cog fa-2x text-custom"></em>
                                                 </div>
                                                 <div class="media-body">
                                                    <h5 class="media-heading">New settings</h5>
                                                    <p class="m-0">
                                                        <small>There are new settings available</small>
                                                    </p>
                                                 </div>
                                              </div>
                                           </a>

                                           <!-- list item-->
                                           <a href="javascript:void(0);" class="list-group-item">
                                              <div class="media">
                                                 <div class="pull-left p-r-10">
                                                    <em class="fa fa-bell-o fa-2x text-danger"></em>
                                                 </div>
                                                 <div class="media-body">
                                                    <h5 class="media-heading">Updates</h5>
                                                    <p class="m-0">
                                                        <small>There are <span class="text-primary font-600">2</span> new updates available</small>
                                                    </p>
                                                 </div>
                                              </div>
                                           </a>

                                           <!-- list item-->
                                           <a href="javascript:void(0);" class="list-group-item">
                                              <div class="media">
                                                 <div class="pull-left p-r-10">
                                                    <em class="fa fa-user-plus fa-2x text-info"></em>
                                                 </div>
                                                 <div class="media-body">
                                                    <h5 class="media-heading">New user registered</h5>
                                                    <p class="m-0">
                                                        <small>You have 10 unread messages</small>
                                                    </p>
                                                 </div>
                                              </div>
                                           </a>

                                           <!-- list item-->
                                           <a href="javascript:void(0);" class="list-group-item">
                                              <div class="media">
                                                 <div class="pull-left p-r-10">
                                                    <em class="fa fa-diamond fa-2x text-primary"></em>
                                                 </div>
                                                 <div class="media-body">
                                                    <h5 class="media-heading">A new order has been placed A new order has been placed</h5>
                                                    <p class="m-0">
                                                        <small>There are new settings available</small>
                                                    </p>
                                                 </div>
                                              </div>
                                           </a>

                                           <!-- list item-->
                                            <a href="javascript:void(0);" class="list-group-item">
                                                <div class="media">
                                                    <div class="pull-left p-r-10">
                                                     <em class="fa fa-cog fa-2x text-custom"></em>
                                                    </div>
                                                    <div class="media-body">
                                                      <h5 class="media-heading">New settings</h5>
                                                      <p class="m-0">
                                                        <small>There are new settings available</small>
                                                    </p>
                                                    </div>
                                              </div>
                                           </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="list-group-item text-right">
                                                <small class="font-600">See all notifications</small>
                                            </a>
                                        </li>
                                    </ul>
                                </li> --}}
                                <li class="hidden-xs">
                                    <a href="#" id="btn-fullscreen" class="waves-effect waves-light"><i class="icon-size-fullscreen"></i></a>
                                </li>

                                <li class="dropdown">
                                    <a href="" class="dropdown-toggle profile" data-toggle="dropdown" aria-expanded="true">
                                        @if(!empty($user->image))
                                            @if(file_exists(storage_path('app/public/profile/' . $user->image)))
                                                <img src="{{url('storage/app/public/profile/'.$user->image)}}" alt="user-img" class="img-circle profile_img">
                                            @else
                                                <i class="ti-user m-r-5"></i>
                                            @endif
                                        @else
                                            <i class="ti-user m-r-5"></i>
                                        @endif
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a href="{{url('/profile')}}"><i class="ti-user m-r-5"></i> Profile</a></li>
                                        <li><a href="{{url('/settings')}}"><i class="ti-settings m-r-5"></i> Settings</a></li>
                                        {{-- <li><a href="javascript:void(0)"><i class="ti-lock m-r-5"></i> Lock screen</a></li> --}}
                                        <li><a href="{{url('logout')}}"><i class="ti-power-off m-r-5"></i> Logout</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                        <!--/.nav-collapse -->
                    </div>
                </div>
            </div>
            <!-- Top Bar End -->


            <!-- ========== Left Sidebar Start ========== -->

           @include('layouts.sidebar')
            <!-- Left Sidebar End -->



            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="content-page">
                <!-- Start content -->
				@yield('content')
				<!-- content -->

                <footer class="footer text-right">
                    © {{date('Y')}}. All rights reserved.
                </footer>

            </div>


            <!-- ============================================================== -->
            <!-- End Right content here -->
            <!-- ============================================================== -->


            <!-- Right Sidebar -->
            <div class="side-bar right-bar nicescroll">
                <h4 class="text-center">Chat</h4>
                <div class="contact-list nicescroll">
                    <ul class="list-group contacts-list">
                        <li class="list-group-item">
                            <a href="#">
                                <div class="avatar">
                                    <img src="assets/images/users/avatar-1.jpg" alt="">
                                </div>
                                <span class="name">Chadengle</span>
                                <i class="fa fa-circle online"></i>
                            </a>
                            <span class="clearfix"></span>
                        </li>
                        <li class="list-group-item">
                            <a href="#">
                                <div class="avatar">
                                    <img src="assets/images/users/avatar-2.jpg" alt="">
                                </div>
                                <span class="name">Tomaslau</span>
                                <i class="fa fa-circle online"></i>
                            </a>
                            <span class="clearfix"></span>
                        </li>
                        <li class="list-group-item">
                            <a href="#">
                                <div class="avatar">
                                    <img src="assets/images/users/avatar-3.jpg" alt="">
                                </div>
                                <span class="name">Stillnotdavid</span>
                                <i class="fa fa-circle online"></i>
                            </a>
                            <span class="clearfix"></span>
                        </li>
                        <li class="list-group-item">
                            <a href="#">
                                <div class="avatar">
                                    <img src="assets/images/users/avatar-4.jpg" alt="">
                                </div>
                                <span class="name">Kurafire</span>
                                <i class="fa fa-circle online"></i>
                            </a>
                            <span class="clearfix"></span>
                        </li>
                        <li class="list-group-item">
                            <a href="#">
                                <div class="avatar">
                                    <img src="assets/images/users/avatar-5.jpg" alt="">
                                </div>
                                <span class="name">Shahedk</span>
                                <i class="fa fa-circle away"></i>
                            </a>
                            <span class="clearfix"></span>
                        </li>
                        <li class="list-group-item">
                            <a href="#">
                                <div class="avatar">
                                    <img src="assets/images/users/avatar-6.jpg" alt="">
                                </div>
                                <span class="name">Adhamdannaway</span>
                                <i class="fa fa-circle away"></i>
                            </a>
                            <span class="clearfix"></span>
                        </li>
                        <li class="list-group-item">
                            <a href="#">
                                <div class="avatar">
                                    <img src="assets/images/users/avatar-7.jpg" alt="">
                                </div>
                                <span class="name">Ok</span>
                                <i class="fa fa-circle away"></i>
                            </a>
                            <span class="clearfix"></span>
                        </li>
                        <li class="list-group-item">
                            <a href="#">
                                <div class="avatar">
                                    <img src="assets/images/users/avatar-8.jpg" alt="">
                                </div>
                                <span class="name">Arashasghari</span>
                                <i class="fa fa-circle offline"></i>
                            </a>
                            <span class="clearfix"></span>
                        </li>
                        <li class="list-group-item">
                            <a href="#">
                                <div class="avatar">
                                    <img src="assets/images/users/avatar-9.jpg" alt="">
                                </div>
                                <span class="name">Joshaustin</span>
                                <i class="fa fa-circle offline"></i>
                            </a>
                            <span class="clearfix"></span>
                        </li>
                        <li class="list-group-item">
                            <a href="#">
                                <div class="avatar">
                                    <img src="assets/images/users/avatar-10.jpg" alt="">
                                </div>
                                <span class="name">Sortino</span>
                                <i class="fa fa-circle offline"></i>
                            </a>
                            <span class="clearfix"></span>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- /Right-bar -->

        </div>
        <!-- END wrapper -->



        <script>
            var resizefunc = [];
        </script>

        <!-- jQuery  -->
        <script src="{{asset('public/assets/js/jquery.min.js')}}"></script>
        <script src="{{asset('public/assets/js/bootstrap.min.js')}}"></script>
        <script src="{{asset('public/assets/js/detect.js')}}"></script>
        <script src="{{asset('public/assets/js/fastclick.js')}}"></script>
        <script src="{{asset('public/assets/js/jquery.slimscroll.js')}}"></script>
        <script src="{{asset('public/assets/js/jquery.blockUI.js')}}"></script>
        <script src="{{asset('public/assets/js/waves.js')}}"></script>
        <script src="{{asset('public/assets/js/wow.min.js')}}"></script>
        <script src="{{asset('public/assets/js/jquery.nicescroll.js')}}"></script>
        <script src="{{asset('public/assets/js/jquery.scrollTo.min.js')}}"></script>
        <!-- <script src="{{asset('public/assets/plugins/peity/jquery.peity.min.js')}}"></script>
        <script src="{{asset('public/assets/plugins/waypoints/lib/jquery.waypoints.js')}}"></script>
        <script src="{{asset('public/assets/plugins/counterup/jquery.counterup.min.js')}}"></script>
        <script src="{{asset('public/assets/plugins/morris/morris.min.js')}}"></script>
        <script src="{{asset('public/assets/plugins/raphael/raphael-min.js')}}"></script>
        <script src="{{asset('public/assets/plugins/jquery-knob/jquery.knob.js')}}"></script>
        <script src="{{asset('public/assets/pages/jquery.dashboard.js')}}"></script> -->
        <script src="{{asset('public/assets/js/jquery.core.js')}}"></script>
        <script src="{{asset('public/assets/js/jquery.app.js')}}"></script>
        <script src="{{asset('public/assets/js/toastr.js')}}"></script>

        <script type="text/javascript">

        </script>

			@yield('script')


    </body>
</html>
