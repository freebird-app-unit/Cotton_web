<div class="left side-menu">
    <div class="sidebar-inner slimscrollleft">
        <!--- Divider -->
        <div id="sidebar-menu">
            <ul>

                <li class="text-muted menu-title">Navigation</li>

                <li class="has_sub">
                    <a href="{{url('/dashboard')}}" class="waves-effect {{request()->segment(1) == 'dashboard' ? 'active' : ''}}"><i class="ti-home"></i> <span> Dashboard </span></a>
                </li>

                <li class="has_sub">
                    <a href="{{url('/product')}}" class="waves-effect {{request()->segment(1) == 'product' ? 'active' : ''}}"><i class="ti-pinterest"></i> <span> Product </span></a>
                </li>
                <li class="has_sub">
                    <a href="{{url('/country')}}" class="waves-effect {{request()->segment(1) == 'country' ? 'active' : ''}}"><i class="ti-joomla"></i> <span> Country </span></a>
                </li>
                <li class="has_sub">
                    <a href="{{url('/state')}}" class="waves-effect {{request()->segment(1) == 'state' ? 'active' : ''}}"><i class="ti-dropbox-alt"></i> <span> State </span></a>
                </li>
                <li class="has_sub">
                    <a href="{{url('/city')}}" class="waves-effect {{request()->segment(1) == 'city' ? 'active' : ''}}"><i class="ti-map"></i> <span> City </span></a>
                </li>
                <li class="has_sub">
                    <a href="{{url('/lab')}}" class="waves-effect {{request()->segment(1) == 'lab' ? 'active' : ''}}"><i class="ti-agenda"></i> <span> Lab </span></a>
                </li>
                <li class="has_sub">
                    <a href="{{url('/payment_condition')}}" class="waves-effect {{request()->segment(1) == 'payment_condition' ? 'active' : ''}}"><i class="ti-money"></i> <span> Payment Condition </span></a>
                </li>
                <li class="has_sub">
                    <a href="{{url('/registration_type')}}" class="waves-effect {{request()->segment(1) == 'registration_type' ? 'active' : ''}}"><i class="fa fa-registered"></i> <span> Registration Type </span></a>
                </li>

                <li class="has_sub">
                    <a href="{{url('/bussiness_type')}}" class="waves-effect {{request()->segment(1) == 'bussiness_type' ? 'active' : ''}}"><i class="ti-briefcase"></i> <span> Business Types </span></a>
                </li>

                <li class="has_sub">
                    <a href="{{url('/buyer_type')}}" class="waves-effect {{request()->segment(1) == 'buyer_type' ? 'active' : ''}}"><i class="fa fa-buysellads"></i> <span> Buyer Type </span></a>
                </li>

                <li class="has_sub">
                    <a href="{{url('/seller_type')}}" class="waves-effect {{request()->segment(1) == 'seller_type' ? 'active' : ''}}"><i class="fa fa-registered"></i> <span> Seller Type </span></a>
                </li>

                <li class="has_sub">
                    <a href="{{url('/seller')}}" class="waves-effect {{request()->segment(1) == 'seller' ? 'active' : ''}}"><i class="ti-user"></i> <span> Sellers </span></a>
                </li>
                <li class="has_sub">
                    <a href="{{url('/buyer')}}" class="waves-effect {{request()->segment(1) == 'buyer' ? 'active' : ''}}"><i class="ti-user"></i> <span> Buyers </span></a>
                </li>
				<li class="has_sub">
                    <a href="{{url('/broker')}}" class="waves-effect {{request()->segment(1) == 'broker' ? 'active' : ''}}"><i class="ti-user"></i> <span> Brokers </span></a>
                </li>
                <li class="has_sub">
                    <a href="{{url('/transmit_condition')}}" class="waves-effect {{request()->segment(1) == 'transmit_condition' ? 'active' : ''}}"><i class="fa fa-trello"></i> <span> Transmit Condition </span></a>
                </li>
                <li class="has_sub">
                    <a href="{{url('/settings')}}" class="waves-effect {{request()->segment(1) == 'settings' ? 'active' : ''}}"><i class="fa fa-gear"></i> <span> Settings </span></a>
                </li>
				<li class="has_sub">
                    <a href="{{url('/news')}}" class="waves-effect {{request()->segment(1) == 'news' ? 'active' : ''}}"><i class="fa fa-newspaper-o"></i> <span> News </span></a>
                </li>

            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
