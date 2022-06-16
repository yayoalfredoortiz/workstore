<!-- SIDEBAR START -->
 <aside class="{{ ((!user()->dark_theme) ? 'sidebar-'.$appTheme->sidebar_theme : '') }}">
     <!-- MOBILE CLOSE SIDEBAR PANEL START -->
     <div class="mobile-close-sidebar-panel w-100 h-100" onclick="closeMobileMenu()" id="mobile_close_panel"></div>
     <!-- MOBILE CLOSE SIDEBAR PANEL END -->

     <!-- MAIN SIDEBAR START -->
     <div class="main-sidebar" id="mobile_menu_collapse">
         <!-- SIDEBAR BRAND START -->
         <div class="sidebar-brand-box dropdown cursor-pointer {{ user()->dark_theme ? 'bg-dark' : '' }}">
             <div class="dropdown-toggle sidebar-brand d-flex align-items-center justify-content-between  w-100"
                 type="link" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                 @if (global_setting()->sidebar_logo_style == 'square')
                     <!-- SIDEBAR BRAND NAME START -->
                    <div class="sidebar-brand-name">
                        <h1 class="mb-0 f-16 f-w-500 text-white-shade mt-0"  data-placement="bottom" data-toggle="tooltip" data-original-title="{{ ucwords($companyName) }}">{{ ucwords($companyName) }}
                            <i class="icon-arrow-down icons pl-2"></i>
                        </h1>
                        <div class="mb-0 position-relative pro-name">
                            <span class="bg-light-green rounded-circle"></span>
                            <p class="f-13 text-lightest mb-0" data-placement="bottom" data-toggle="tooltip" data-original-title="{{ ucwords(user()->name) }}">{{ ucwords(user()->name) }}</p>
                        </div>
                    </div>
                    <!-- SIDEBAR BRAND NAME END -->
                    <!-- SIDEBAR BRAND LOGO START -->
                    <div class="sidebar-brand-logo">
                        <img src="{{ $global->logo_url }}">
                    </div>
                    <!-- SIDEBAR BRAND LOGO END -->
                 @else
                    <!-- SIDEBAR BRAND NAME START -->
                    <div class="sidebar-brand-name">
                        <h1 class="mb-0 f-16 f-w-500 text-white-shade mt-0"  data-placement="bottom" data-toggle="tooltip" data-original-title="{{ ucwords($companyName) }}">
                        <img src="{{ $global->logo_url }}">
                        </h1>
                    </div>
                    <!-- SIDEBAR BRAND NAME END -->
                    <!-- SIDEBAR BRAND LOGO START -->
                    <div class="sidebar-brand-logo text-white-shade f-12">
                    <i class="icon-arrow-down icons pl-2"></i>
                    </div>
                    <!-- SIDEBAR BRAND LOGO END -->
                @endif
             </div>
             <!-- DROPDOWN - INFORMATION -->
             <div class="dropdown-menu dropdown-menu-right sidebar-brand-dropdown ml-3"
                 aria-labelledby="dropdownMenuLink" tabindex="0">
                 <div class="d-flex justify-content-between align-items-center profile-box">
                     <div class="profileInfo d-flex align-items-center mr-1 flex-wrap">
                         <div class="profileImg mr-2">
                             <img class="h-100" src="{{ $user->image_url }}" alt="{{ ucwords(user()->name) }}">
                         </div>
                         <div class="ProfileData">
                             <h3 class="f-15 f-w-500 text-dark" data-placement="bottom" data-toggle="tooltip" data-original-title="{{ ucwords(user()->name) }}">{{ ucwords(user()->name) }}</h3>
                             <p class="mb-0 f-12 text-dark-grey">{{ user()->designation->name ?? '' }}</p>
                         </div>
                     </div>
                     <a href="{{ route('profile-settings.index') }}">
                         <i class="side-icon bi bi-pencil-square"></i>
                     </a>
                 </div>

                 @if (!in_array('client', user_roles()) && ($sidebarUserPermissions['add_employees'] == 4 || $sidebarUserPermissions['add_employees'] == 1))
                    <a class="dropdown-item d-flex justify-content-between align-items-center f-15 text-dark invite-member"
                        href="javascript:;">
                        <span>@lang('app.inviteMember') {{ ucwords($companyName) }}</span>
                        <i class="side-icon bi bi-person-plus"></i>
                    </a>
                 @endif

                 <a class="dropdown-item d-flex justify-content-between align-items-center f-15 text-dark" href="javascript:;">
                     <label for="dark-theme-toggle">@lang('app.darkTheme')</label>
                     <div class="custom-control custom-switch">
                         <input type="checkbox" class="custom-control-input" id="dark-theme-toggle" @if (user()->dark_theme) checked @endif>
                         <label class="custom-control-label f-14" for="dark-theme-toggle"></label>
                     </div>
                 </a>
                 <a class="dropdown-item d-flex justify-content-between align-items-center f-15 text-dark"
                     href="{{ route('logout') }}" onclick="event.preventDefault();
                document.getElementById('logout-form').submit();">
                     @lang('app.logout')
                     <i class="side-icon bi bi-power"></i>
                 </a>
             </div>
         </div>
         <!-- SIDEBAR BRAND END -->

         <!-- SIDEBAR MENU START -->
         <div class="sidebar-menu {{ user()->dark_theme ? 'bg-dark' : '' }}" id="sideMenuScroll">
             <ul>
                <!-- NAV ITEM - DASHBOARD COLLAPSE MENU-->
                @if (in_array('admin', user_roles()) || in_array('dashboards', user_modules()))
                    <x-menu-item icon="house" :text="__('app.menu.dashboard')">
                        <x-slot name="iconPath">
                            <path fill-rule="evenodd" d="M2 13.5V7h1v6.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V7h1v6.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5zm11-11V6l-2-2V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5z"/>
                            <path fill-rule="evenodd" d="M7.293 1.5a1 1 0 0 1 1.414 0l6.647 6.646a.5.5 0 0 1-.708.708L8 2.207 1.354 8.854a.5.5 0 1 1-.708-.708L7.293 1.5z"/>
                        </x-slot>
                        <div class="accordionItemContent pb-2">
                            <x-sub-menu-item :link="route('dashboard')" :text="__('app.admin') . ' ' . __('app.menu.dashboard')" />
                            <x-sub-menu-item :link="route('dashboard.member')" :text="__('app.private') . ' ' . __('app.menu.dashboard')" />
                        </div>
                    </x-menu-item>
                @else
                    <x-menu-item icon="house" :text="__('app.menu.dashboard')" :link="route('dashboard')">
                        <x-slot name="iconPath">
                            <path fill-rule="evenodd" d="M2 13.5V7h1v6.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V7h1v6.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5zm11-11V6l-2-2V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5z"/>
                            <path fill-rule="evenodd" d="M7.293 1.5a1 1 0 0 1 1.414 0l6.647 6.646a.5.5 0 0 1-.708.708L8 2.207 1.354 8.854a.5.5 0 1 1-.708-.708L7.293 1.5z"/>
                        </x-slot>
                    </x-menu-item>
                @endif

                <!-- NAV ITEM - CUSTOMERS COLLAPASE MENU -->
                @if (
                    !in_array('client', user_roles())
                    && in_array('leads', user_modules())
                    && $sidebarUserPermissions['view_lead'] != 5
                    && $sidebarUserPermissions['view_lead'] != 'none'
                )
                    <x-menu-item icon="person" :text="__('app.menu.lead')" :link="route('leads.index')">
                         <x-slot name="iconPath">
                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                        </x-slot>
                    </x-menu-item>
                @endif

                @if (
                    !in_array('client', user_roles())
                    && in_array('clients', user_modules())
                    && $sidebarUserPermissions['view_clients'] != 5
                    && $sidebarUserPermissions['view_clients'] != 'none'
                )
                    <x-menu-item icon="building" :text="__('app.menu.clients')" :link="route('clients.index')">
                         <x-slot name="iconPath">
                            <path fill-rule="evenodd" d="M14.763.075A.5.5 0 0 1 15 .5v15a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5V14h-1v1.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V10a.5.5 0 0 1 .342-.474L6 7.64V4.5a.5.5 0 0 1 .276-.447l8-4a.5.5 0 0 1 .487.022zM6 8.694 1 10.36V15h5V8.694zM7 15h2v-1.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5V15h2V1.309l-7 3.5V15z"/>
                            <path d="M2 11h1v1H2v-1zm2 0h1v1H4v-1zm-2 2h1v1H2v-1zm2 0h1v1H4v-1zm4-4h1v1H8V9zm2 0h1v1h-1V9zm-2 2h1v1H8v-1zm2 0h1v1h-1v-1zm2-2h1v1h-1V9zm0 2h1v1h-1v-1zM8 7h1v1H8V7zm2 0h1v1h-1V7zm2 0h1v1h-1V7zM8 5h1v1H8V5zm2 0h1v1h-1V5zm2 0h1v1h-1V5zm0-2h1v1h-1V3z"/>
                        </x-slot>
                    </x-menu-item>
                @endif

                <!-- NAV ITEM - HR COLLAPASE MENU -->
                @if (
                    !in_array('client', user_roles())
                    && (in_array('employees', user_modules()) || in_array('leaves', user_modules()) || in_array('attendance', user_modules()) || in_array('holidays', user_modules()))
                    && ($sidebarUserPermissions['view_employees'] != 5 || $sidebarUserPermissions['view_leave'] != 5 || $sidebarUserPermissions['view_attendance'] != 5 || $sidebarUserPermissions['view_holiday'] != 5)
                    && ($sidebarUserPermissions['view_employees'] != 'none' || $sidebarUserPermissions['view_leave'] != 'none' || $sidebarUserPermissions['view_attendance'] != 'none' || $sidebarUserPermissions['view_holiday'] != 'none')
                )
                    <x-menu-item icon="people" :text="__('app.menu.hr')">
                        <x-slot name="iconPath">
                            <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                        </x-slot>
                        <div class="accordionItemContent pb-2">
                        @if (in_array('employees', user_modules()) && $sidebarUserPermissions['view_employees'] != 5 && $sidebarUserPermissions['view_employees'] != 'none')
                            <x-sub-menu-item :link="route('employees.index')" :text="__('app.menu.employees')" />
                        @endif
                        @if (in_array('leaves', user_modules()) && $sidebarUserPermissions['view_leave'] != 5 && $sidebarUserPermissions['view_leave'] != 'none' )
                            <x-sub-menu-item :link="route('leaves.index')" :text="__('app.menu.leaves')" />
                        @endif
                        @if (in_array('attendance', user_modules()) && $sidebarUserPermissions['view_attendance'] != 5 && $sidebarUserPermissions['view_attendance'] != 'none')
                            <x-sub-menu-item :link="route('attendances.index')" :text="__('app.menu.attendance')" />
                        @endif
                        @if (in_array('holidays', user_modules()) && $sidebarUserPermissions['view_holiday'] != 5 && $sidebarUserPermissions['view_holiday'] != 'none')
                            <x-sub-menu-item :link="route('holidays.index')" :text="__('app.menu.holiday')" />
                        @endif
                        </div>
                    </x-menu-item>
                @endif

                <!-- NAV ITEM - WORK COLLAPASE MENU -->
                @if (
                (in_array('contracts', user_modules()) || in_array('projects', user_modules()) || in_array('tasks', user_modules()) || in_array('timelogs', user_modules()))
                 && ($sidebarUserPermissions['view_contract'] != 5 || $sidebarUserPermissions['view_projects'] != 5 || $sidebarUserPermissions['view_tasks'] != 5 || $sidebarUserPermissions['view_timelogs'] != 5)
                 && ($sidebarUserPermissions['view_contract'] != 'none' || $sidebarUserPermissions['view_projects'] != 'none' || $sidebarUserPermissions['view_tasks'] != 'none' || $sidebarUserPermissions['view_timelogs'] != 'none')
                 )
                <x-menu-item icon="briefcase" :text="__('app.menu.work')">
                    <x-slot name="iconPath">
                        <path d="M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v8A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-8A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1h-3zm0 1h3a.5.5 0 0 1 .5.5V3H6v-.5a.5.5 0 0 1 .5-.5zm1.886 6.914L15 7.151V12.5a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5V7.15l6.614 1.764a1.5 1.5 0 0 0 .772 0zM1.5 4h13a.5.5 0 0 1 .5.5v1.616L8.129 7.948a.5.5 0 0 1-.258 0L1 6.116V4.5a.5.5 0 0 1 .5-.5z"/>
                    </x-slot>
                    <div class="accordionItemContent pb-2">
                        @if (in_array('contracts', user_modules()) && $sidebarUserPermissions['view_contract'] != 5 && $sidebarUserPermissions['view_contract'] != 'none')
                            <x-sub-menu-item :link="route('contracts.index')" :text="__('app.menu.contracts')" />
                        @endif
                        @if (in_array('projects', user_modules()) && $sidebarUserPermissions['view_projects'] != 5 && $sidebarUserPermissions['view_projects'] != 'none')
                            <x-sub-menu-item :link="route('projects.index')" :text="__('app.menu.projects')" />
                        @endif
                        @if (!in_array('client', user_roles()))
                            @if (in_array('tasks', user_modules()) && $sidebarUserPermissions['view_tasks'] != 5 && $sidebarUserPermissions['view_tasks'] != 'none')
                                <x-sub-menu-item :link="route('tasks.index')" :text="__('app.menu.tasks')" />
                            @endif
                            @if (in_array('timelogs', user_modules()) && $sidebarUserPermissions['view_timelogs'] != 5 && $sidebarUserPermissions['view_timelogs'] != 'none')
                                <x-sub-menu-item :link="route('timelogs.index')" :text="__('app.menu.timeLogs')" />
                            @endif
                        @endif
                    </div>
                </x-menu-item>
                @endif

                <!-- NAV ITEM - FINANCE COLLAPASE MENU -->
                @if (
                (in_array('estimates', user_modules()) || in_array('invoices', user_modules()) || in_array('payments', user_modules()) || in_array('expenses', user_modules()))
                && ($sidebarUserPermissions['view_estimates'] != 5 || $sidebarUserPermissions['view_invoices'] != 5 || $sidebarUserPermissions['view_payments'] != 5 || $sidebarUserPermissions['view_expenses'] != 5 || $sidebarUserPermissions['view_lead_proposals'] != 5)
                && ($sidebarUserPermissions['view_estimates'] != 'none' || $sidebarUserPermissions['view_invoices'] != 'none' || $sidebarUserPermissions['view_payments'] != 'none' || $sidebarUserPermissions['view_expenses'] != 'none' || $sidebarUserPermissions['view_lead_proposals'] != 'none')
                )
                <x-menu-item icon="cash-coin" :active="($currentRouteName === 'payments.index')"
                    :text="__('app.menu.finance')">
                    <x-slot name="iconPath">
                        <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"/>
                    </x-slot>
                    <div class="accordionItemContent pb-2">
                        @if (in_array('leads', user_modules()) && $sidebarUserPermissions['view_lead_proposals'] != 5 && $sidebarUserPermissions['view_lead_proposals'] != 'none')
                            <x-sub-menu-item :link="route('proposals.index')" :text="__('app.menu.proposal')" />
                        @endif
                        @if (in_array('estimates', user_modules()) && $sidebarUserPermissions['view_estimates'] != 5 && $sidebarUserPermissions['view_estimates'] != 'none')
                            <x-sub-menu-item :link="route('estimates.index')" :text="__('app.menu.estimates')" />
                        @endif
                        @if (in_array('invoices', user_modules()) && $sidebarUserPermissions['view_invoices'] != 5 && $sidebarUserPermissions['view_invoices'] != 'none')
                            <x-sub-menu-item :link="route('invoices.index')" :text="__('app.menu.invoices')" />
                        @endif
                        @if (in_array('payments', user_modules()) && $sidebarUserPermissions['view_payments'] != 5 && $sidebarUserPermissions['view_payments'] != 'none')
                            <x-sub-menu-item :link="route('payments.index')" :text="__('app.menu.payments')" />
                        @endif
                        @if (in_array('invoices', user_modules()) && $sidebarUserPermissions['view_invoices'] != 5 && $sidebarUserPermissions['view_invoices'] != 'none')
                            <x-sub-menu-item :link="route('creditnotes.index')" :text="__('app.menu.credit-note')" />
                        @endif
                        @if (!in_array('client', user_roles()))
                            @if (in_array('expenses', user_modules()) && $sidebarUserPermissions['view_expenses'] != 5 && $sidebarUserPermissions['view_expenses'] != 'none')
                                <x-sub-menu-item :link="route('expenses.index')" :text="__('app.menu.expenses')" />
                            @endif
                        @endif
                    </div>
                </x-menu-item>
                @endif

                <!-- NAV ITEM - PRODUCTS -->
                @if (in_array('products', user_modules()) && $sidebarUserPermissions['view_product'] != 5 && $sidebarUserPermissions['view_product'] != 'none')
                    <x-menu-item icon="basket" :text="__('app.menu.products')" :link="route('products.index')">
                    <x-slot name="iconPath">
                        <path d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1v4.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 13.5V9a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h1.217L5.07 1.243a.5.5 0 0 1 .686-.172zM2 9v4.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V9H2zM1 7v1h14V7H1zm3 3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 4 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 6 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 8 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5z"/>
                    </x-slot>
                    </x-menu-item>
                @endif

                <!-- NAV ITEM - PRODUCTS -->
                @if (in_array('orders', user_modules()) && $sidebarUserPermissions['view_order'] != 5 && $sidebarUserPermissions['view_order'] != 'none')
                    <x-menu-item icon="cart3" :text="__('app.menu.orders')" :link="route('orders.index')">
                        <x-slot name="iconPath">
                            <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                        </x-slot>
                    </x-menu-item>
                @endif

                <!-- NAV ITEM - TICKETS -->
                @if (in_array('tickets', user_modules()) && $sidebarUserPermissions['view_tickets'] != 5 && $sidebarUserPermissions['view_tickets'] != 'none')
                    <x-menu-item icon="headset" :text="__('app.menu.tickets')" :link="route('tickets.index')" >
                        <x-slot name="iconPath">
                            <path d="M8 1a5 5 0 0 0-5 5v1h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a6 6 0 1 1 12 0v6a2.5 2.5 0 0 1-2.5 2.5H9.366a1 1 0 0 1-.866.5h-1a1 1 0 1 1 0-2h1a1 1 0 0 1 .866.5H11.5A1.5 1.5 0 0 0 13 12h-1a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1h1V6a5 5 0 0 0-5-5z"/>
                        </x-slot>
                    </x-menu-item>
                @endif

                <!-- NAV ITEM - EVENTS -->
                @if (in_array('events', user_modules()) && $sidebarUserPermissions['view_events'] != 5 && $sidebarUserPermissions['view_events'] != 'none')
                    <x-menu-item icon="calendar-event" :text="__('app.menu.Events')" :link="route('events.index')" >
                    <x-slot name="iconPath">
                            <path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z"/>
                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                        </x-slot>
                    </x-menu-item>
                @endif

                <!-- NAV ITEM - MESSAGES -->
                @if (in_array('messages', user_modules()))
                    @if (
                        (message_setting()->allow_client_admin == 'yes' || message_setting()->allow_client_employee == 'yes')
                        && in_array('client', user_roles())
                    )
                        <x-menu-item icon="chat-left-text" :text="__('app.menu.messages')"
                            :link="route('messages.index')">
                            <x-slot name="iconPath">
                                <path d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H4.414A2 2 0 0 0 3 11.586l-2 2V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12.793a.5.5 0 0 0 .854.353l2.853-2.853A1 1 0 0 1 4.414 12H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                                <path d="M3 3.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zM3 6a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 3 6zm0 2.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                            </x-slot>
                        </x-menu-item>
                    @elseif (in_array('employee', user_roles()))
                        <x-menu-item icon="chat-left-text" :text="__('app.menu.messages')"
                            :link="route('messages.index')" >
                            <x-slot name="iconPath">
                                <path d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H4.414A2 2 0 0 0 3 11.586l-2 2V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12.793a.5.5 0 0 0 .854.353l2.853-2.853A1 1 0 0 1 4.414 12H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                                <path d="M3 3.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zM3 6a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 3 6zm0 2.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                            </x-slot>
                        </x-menu-item>
                    @endif
                @endif

                <!-- NAV ITEM - GDPR -->
                @if (in_array('client', user_roles()) && $gdpr->enable_gdpr)
                    <x-menu-item icon="lock" :text="__('app.menu.gdpr')" :link="route('gdpr.index')">
                        <x-slot name="iconPath">
                            <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zM5 8h6a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z"/>
                        </x-slot>
                    </x-menu-item>
                @endif

                <!-- NAV ITEM - NOTICES -->
                @if (in_array('notices', user_modules()) && $sidebarUserPermissions['view_notice'] != 5 && $sidebarUserPermissions['view_notice'] != 'none')
                    <x-menu-item icon="clipboard" :text="__('app.menu.noticeBoard')" :link="route('notices.index')" >
                        <x-slot name="iconPath">
                            <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                            <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                        </x-slot>
                    </x-menu-item>
                @endif

                <!-- Knowledge base -->
                @if ( in_array('client', user_roles()) || in_array('employee', user_roles()))
                    <x-menu-item icon="note" :text="__('app.menu.knowledgebase')" :link="route('knowledgebase.index')" >
                        <x-slot name="iconPath">
                            <g xmlns="http://www.w3.org/2000/svg" id="surface1">
                                <path d="M 11.824219 0.21875 C 11.722656 0.0820312 11.5625 0 11.390625 0 L 2.277344 0 C 1.394531 0 0.675781 0.714844 0.675781 1.601562 L 0.675781 14.398438 C 0.675781 15.285156 1.394531 16 2.277344 16 L 13.71875 16 C 14.601562 16 15.320312 15.285156 15.320312 14.398438 L 15.320312 5.199219 C 15.320312 5.085938 15.285156 4.976562 15.21875 4.886719 Z M 11.121094 1.066406 L 14.253906 5.375 L 14.253906 14.398438 C 14.253906 14.695312 14.015625 14.933594 13.71875 14.933594 L 2.277344 14.933594 C 1.984375 14.933594 1.742188 14.695312 1.742188 14.398438 L 1.742188 1.601562 C 1.742188 1.304688 1.984375 1.066406 2.277344 1.066406 Z M 11.121094 1.066406 "/>
                                <path d="M 3.246094 4.460938 L 8 4.460938 C 8.292969 4.460938 8.53125 4.222656 8.53125 3.925781 C 8.53125 3.632812 8.292969 3.394531 8 3.394531 L 3.246094 3.394531 C 2.953125 3.394531 2.714844 3.632812 2.714844 3.925781 C 2.714844 4.222656 2.953125 4.460938 3.246094 4.460938 Z M 3.246094 4.460938 "/>
                                <path d="M 14.785156 5.429688 L 11.925781 5.429688 L 11.925781 0.535156 C 11.925781 0.238281 11.6875 0 11.390625 0 C 11.097656 0 10.859375 0.238281 10.859375 0.535156 L 10.859375 5.964844 C 10.859375 6.257812 11.097656 6.496094 11.390625 6.496094 L 14.785156 6.496094 C 15.082031 6.496094 15.320312 6.257812 15.320312 5.964844 C 15.320312 5.667969 15.082031 5.429688 14.785156 5.429688 Z M 14.785156 5.429688 "/>
                                <path d="M 3.246094 7.855469 L 8 7.855469 C 8.292969 7.855469 8.53125 7.617188 8.53125 7.320312 C 8.53125 7.027344 8.292969 6.789062 8 6.789062 L 3.246094 6.789062 C 2.953125 6.789062 2.714844 7.027344 2.714844 7.320312 C 2.714844 7.617188 2.953125 7.855469 3.246094 7.855469 Z M 3.246094 7.855469 "/>
                                <path d="M 3.246094 10.910156 L 10.035156 10.910156 C 10.328125 10.910156 10.566406 10.671875 10.566406 10.375 C 10.566406 10.082031 10.328125 9.84375 10.035156 9.84375 L 3.246094 9.84375 C 2.953125 9.84375 2.714844 10.082031 2.714844 10.375 C 2.714844 10.671875 2.953125 10.910156 3.246094 10.910156 Z M 3.246094 10.910156 "/>
                            </g>

                        </x-slot>
                    </x-menu-item>
                @endif
                <!-- Knowledge base -->

                <!-- NAV ITEM - NOTES -->
                @if (in_array('client', user_roles()))
                    <x-menu-item icon="journal-text" :text="__('app.menu.notes')" :link="route('client-notes.index')">
                        <x-slot name="iconPath">
                            <path d="M5 10.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5zm0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                            <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2z"/>
                            <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1z"/>
                        </x-slot>
                    </x-menu-item>
                @endif

                 <!-- NAV ITEM - CUSTOM MODULES  -->
                 @foreach ($worksuitePlugins as $item)
                     @if (View::exists(strtolower($item) . '::sections.sidebar'))
                         @include(strtolower($item).'::sections.sidebar')
                     @endif
                 @endforeach

                <!-- NAV ITEM - NOTICES -->
                @if (in_array('reports', user_modules()) &&
                        (
                            $sidebarUserPermissions['view_task_report'] != 5
                            || $sidebarUserPermissions['view_time_log_report'] != 5
                            || $sidebarUserPermissions['view_finance_report'] != 5
                            || $sidebarUserPermissions['view_income_expense_report'] != 5
                            || $sidebarUserPermissions['view_leave_report'] != 5
                            || $sidebarUserPermissions['view_attendance_report'] != 5
                        ) &&
                        (
                            $sidebarUserPermissions['view_task_report'] != 'none'
                            || $sidebarUserPermissions['view_time_log_report'] != 'none'
                            || $sidebarUserPermissions['view_finance_report'] != 'none'
                            || $sidebarUserPermissions['view_income_expense_report'] != 'none'
                            || $sidebarUserPermissions['view_leave_report'] != 'none'
                            || $sidebarUserPermissions['view_attendance_report'] != 'none'
                        )
                    )
                    <x-menu-item icon="graph-up" :text="__('app.menu.reports')">
                        <x-slot name="iconPath">
                            <path d="M7.5 1.018a7 7 0 0 0-4.79 11.566L7.5 7.793V1.018zm1 0V7.5h6.482A7.001 7.001 0 0 0 8.5 1.018zM14.982 8.5H8.207l-4.79 4.79A7 7 0 0 0 14.982 8.5zM0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8z"/>
                        </x-slot>

                         <div class="accordionItemContent pb-2">
                            @if ($sidebarUserPermissions['view_task_report'] != 5 && $sidebarUserPermissions['view_task_report'] != 'none' && in_array('tasks', user_modules()))
                                <x-sub-menu-item :link="route('task-report.index')" :text="__('app.menu.taskReport')" />
                            @endif

                            @if ($sidebarUserPermissions['view_time_log_report'] != 5 && $sidebarUserPermissions['view_time_log_report'] != 'none' && in_array('timelogs', user_modules()))
                                <x-sub-menu-item :link="route('time-log-report.index')" :text="__('app.menu.timeLogReport')" />
                            @endif

                            @if ($sidebarUserPermissions['view_finance_report'] != 5 && $sidebarUserPermissions['view_finance_report'] != 'none' && in_array('payments', user_modules()))
                                <x-sub-menu-item :link="route('finance-report.index')" :text="__('app.menu.financeReport')" />
                            @endif

                            @if ($sidebarUserPermissions['view_income_expense_report'] != 5 && $sidebarUserPermissions['view_income_expense_report'] != 'none' && in_array('expenses', user_modules()))
                                <x-sub-menu-item :link="route('income-expense-report.index')" :text="__('app.menu.incomeVsExpenseReport')" />
                            @endif

                            @if ($sidebarUserPermissions['view_leave_report'] != 5 && $sidebarUserPermissions['view_leave_report'] != 'none' && in_array('leaves', user_modules()))
                                <x-sub-menu-item :link="route('leave-report.index')" :text="__('app.menu.leaveReport')" />
                            @endif

                            @if ($sidebarUserPermissions['view_attendance_report'] != 5 && $sidebarUserPermissions['view_attendance_report'] != 'none' && in_array('attendance', user_modules()))
                                <x-sub-menu-item :link="route('attendance-report.index')" :text="__('app.menu.attendanceReport')" />
                            @endif
                         </div>
                     </x-menu-item>
                @endif

                 <!-- NAV ITEM - REPORTS COLLAPASE MENU -->
                    <!-- NAV ITEM - SETTINGS -->
                    <x-menu-item icon="gear" :text="__('app.menu.settings')"
                        :link="($sidebarUserPermissions['manage_company_setting'] == 4 ? route('company-settings.index') : route('profile-settings.index'))">
                    <x-slot name="iconPath">
                        <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
                        <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/>
                    </x-slot>
                    </x-menu-item>


             </ul>
         </div>
         <!-- SIDEBAR MENU END -->
     </div>
     <!-- MAIN SIDEBAR END -->
     <!-- Sidebar Toggler -->
     <div
         class="text-center d-flex justify-content-between align-items-center position-fixed sidebarTogglerBox {{ user()->dark_theme ? 'bg-dark' : '' }}">
         <button class="border-0 d-lg-block d-none text-lightest font-weight-bold" id="sidebarToggle"></button>

         <p class="mb-0 text-dark-grey">ver {{ File::get('version.txt') }}</p>
     </div>
     <!-- Sidebar Toggler -->
 </aside>
 <!-- SIDEBAR END -->

<script>
    $(document).ready(function () {

        $('.invite-member').click(function () {
            const url = "{{ route('employees.invite_member') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#dark-theme-toggle').change(function () {
            const darkTheme = ($(this).is(':checked')) ? '1' : '0'

            $.easyAjax({
                type: 'POST',
                url: "{{ route('profile.dark_theme') }}",
                blockUI: true,
                data: {
                    '_token': '{{ csrf_token() }}',
                    'darkTheme': darkTheme
                },
                success: function (response) {
                    if (response.status === 'success') {
                        window.location.reload();
                    }
                }
            });

        });

    });
</script>
