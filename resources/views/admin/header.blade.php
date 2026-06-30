<nav class="navbar navbar-expand-lg">
        {{-- Search panel: still shown/hidden by the theme's front.js (jQuery fadeIn/fadeOut) --}}
        <div class="search-panel">
          <div class="search-inner flex items-center justify-center">
            <div class="close-btn">Close <i class="fa fa-close"></i></div>
            <form id="searchForm" action="#">
              <div class="form-group">
                <input type="search" name="search" placeholder="What are you searching for...">
                <button type="submit" class="submit">Search</button>
              </div>
            </form>
          </div>
        </div>
        {{-- container-fluid + d-flex utilities → Tailwind (full width, 15px gutters, flex row) --}}
        <div class="container-fluid w-full px-[15px] mx-auto flex items-center justify-between">
          <div class="navbar-header">
            <!-- Navbar Header-->
            <a href="{{ url('home') }}" class="navbar-brand">
              <div class="brand-text brand-big visible uppercase"><strong class="text-primary">Dark</strong><strong>Admin</strong></div>
              <div class="brand-text brand-sm"><strong class="text-primary">D</strong><strong>A</strong></div></a>
            <!-- Sidebar Toggle Btn (handled by theme front.js) -->
            <button class="sidebar-toggle"><i class="fa fa-long-arrow-left"></i></button>
          </div>
          {{-- right-menu: `flex items-center gap-2` reproduces Bootstrap's .list-inline spacing (0.5rem) --}}
          <div class="right-menu no-margin-bottom flex items-center gap-2">
            <div class="list-inline-item"><a href="#" class="search-open nav-link"><i class="icon-magnifying-glass-browser"></i></a></div>
            {{-- Messages dropdown → Alpine (was data-toggle="dropdown") --}}
            <div class="dropdown relative" x-data="{ open: false }" @click.outside="open = false">
              <a id="navbarDropdownMenuLink1" href="#" @click.prevent="open = !open" :aria-expanded="open" aria-haspopup="true" class="nav-link messages-toggle"><i class="icon-email"></i><span class="badge dashbg-1">5</span></a>
              <div aria-labelledby="navbarDropdownMenuLink1" class="dropdown-menu messages absolute z-[1000]" :class="{ 'active': open }" x-show="open" x-transition.opacity x-cloak><a href="#" class="dropdown-item message flex items-center">
                  <div class="profile"><img src="admin/img/avatar-3.jpg" alt="..." class="max-w-full h-auto">
                    <div class="status online"></div>
                  </div>
                  <div class="content">   <strong class="block">Nadia Halsey</strong><span class="block">lorem ipsum dolor sit amit</span><small class="date block">9:30am</small></div></a><a href="#" class="dropdown-item message flex items-center">
                  <div class="profile"><img src="admin/img/avatar-2.jpg" alt="..." class="max-w-full h-auto">
                    <div class="status away"></div>
                  </div>
                  <div class="content">   <strong class="block">Peter Ramsy</strong><span class="block">lorem ipsum dolor sit amit</span><small class="date block">7:40am</small></div></a><a href="#" class="dropdown-item message flex items-center">
                  <div class="profile"><img src="admin/img/avatar-1.jpg" alt="..." class="max-w-full h-auto">
                    <div class="status busy"></div>
                  </div>
                  <div class="content">   <strong class="block">Sam Kaheil</strong><span class="block">lorem ipsum dolor sit amit</span><small class="date block">6:55am</small></div></a><a href="#" class="dropdown-item message flex items-center">
                  <div class="profile"><img src="admin/img/avatar-5.jpg" alt="..." class="max-w-full h-auto">
                    <div class="status offline"></div>
                  </div>
                  <div class="content">   <strong class="block">Sara Wood</strong><span class="block">lorem ipsum dolor sit amit</span><small class="date block">10:30pm</small></div></a><a href="#" class="dropdown-item text-center message"> <strong>See All Messages <i class="fa fa-angle-right"></i></strong></a></div>
            </div>
            <!-- Tasks-->
            <div class="dropdown relative" x-data="{ open: false }" @click.outside="open = false">
              <a id="navbarDropdownMenuLink2" href="#" @click.prevent="open = !open" :aria-expanded="open" aria-haspopup="true" class="nav-link tasks-toggle"><i class="icon-new-file"></i><span class="badge dashbg-3">9</span></a>
              <div aria-labelledby="navbarDropdownMenuLink2" class="dropdown-menu tasks-list absolute z-[1000]" :class="{ 'active': open }" x-show="open" x-transition.opacity x-cloak><a href="#" class="dropdown-item">
                  <div class="text flex justify-between"><strong>Task 1</strong><span>40% complete</span></div>
                  <div class="progress">
                    <div role="progressbar" style="width: 40%" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" class="progress-bar dashbg-1"></div>
                  </div></a><a href="#" class="dropdown-item">
                  <div class="text flex justify-between"><strong>Task 2</strong><span>20% complete</span></div>
                  <div class="progress">
                    <div role="progressbar" style="width: 20%" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" class="progress-bar dashbg-3"></div>
                  </div></a><a href="#" class="dropdown-item">
                  <div class="text flex justify-between"><strong>Task 3</strong><span>70% complete</span></div>
                  <div class="progress">
                    <div role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" class="progress-bar dashbg-2"></div>
                  </div></a><a href="#" class="dropdown-item">
                  <div class="text flex justify-between"><strong>Task 4</strong><span>30% complete</span></div>
                  <div class="progress">
                    <div role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100" class="progress-bar dashbg-4"></div>
                  </div></a><a href="#" class="dropdown-item">
                  <div class="text flex justify-between"><strong>Task 5</strong><span>65% complete</span></div>
                  <div class="progress">
                    <div role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100" class="progress-bar dashbg-1"></div>
                  </div></a><a href="#" class="dropdown-item text-center"> <strong>See All Tasks <i class="fa fa-angle-right"></i></strong></a>
              </div>
            </div>
            <!-- Tasks end-->
            <!-- Megamenu-->
            <div class="dropdown menu-large relative" x-data="{ open: false }" @click.outside="open = false"><a href="#" @click.prevent="open = !open" class="nav-link">Mega <i class="fa fa-ellipsis-v"></i></a>
              <div class="dropdown-menu megamenu absolute z-[1000]" :class="{ 'active': open }" x-show="open" x-transition.opacity x-cloak>
                {{-- row → flex; col-lg-3 col-md-6 → 50% (md) / 25% (lg) --}}
                <div class="row flex flex-wrap -mx-[15px]">
                  <div class="w-full md:w-1/2 lg:w-1/4 px-[15px]"><strong class="uppercase">Elements Heading</strong>
                    <ul class="list-none mb-4">
                      <li><a href="#">Lorem ipsum dolor</a></li>
                      <li><a href="#">Sed ut perspiciatis</a></li>
                      <li><a href="#">Voluptatum deleniti</a></li>
                      <li><a href="#">At vero eos</a></li>
                      <li><a href="#">Consectetur adipiscing</a></li>
                      <li><a href="#">Duis aute irure</a></li>
                      <li><a href="#">Necessitatibus saepe</a></li>
                      <li><a href="#">Maiores alias</a></li>
                    </ul>
                  </div>
                  <div class="w-full md:w-1/2 lg:w-1/4 px-[15px]"><strong class="uppercase">Elements Heading</strong>
                    <ul class="list-none mb-4">
                      <li><a href="#">Lorem ipsum dolor</a></li>
                      <li><a href="#">Sed ut perspiciatis</a></li>
                      <li><a href="#">Voluptatum deleniti</a></li>
                      <li><a href="#">At vero eos</a></li>
                      <li><a href="#">Consectetur adipiscing</a></li>
                      <li><a href="#">Duis aute irure</a></li>
                      <li><a href="#">Necessitatibus saepe</a></li>
                      <li><a href="#">Maiores alias</a></li>
                    </ul>
                  </div>
                  <div class="w-full md:w-1/2 lg:w-1/4 px-[15px]"><strong class="uppercase">Elements Heading</strong>
                    <ul class="list-none mb-4">
                      <li><a href="#">Lorem ipsum dolor</a></li>
                      <li><a href="#">Sed ut perspiciatis</a></li>
                      <li><a href="#">Voluptatum deleniti</a></li>
                      <li><a href="#">At vero eos</a></li>
                      <li><a href="#">Consectetur adipiscing</a></li>
                      <li><a href="#">Duis aute irure</a></li>
                      <li><a href="#">Necessitatibus saepe</a></li>
                      <li><a href="#">Maiores alias</a></li>
                    </ul>
                  </div>
                  <div class="w-full md:w-1/2 lg:w-1/4 px-[15px]"><strong class="uppercase">Elements Heading</strong>
                    <ul class="list-none mb-4">
                      <li><a href="#">Lorem ipsum dolor</a></li>
                      <li><a href="#">Sed ut perspiciatis</a></li>
                      <li><a href="#">Voluptatum deleniti</a></li>
                      <li><a href="#">At vero eos</a></li>
                      <li><a href="#">Consectetur adipiscing</a></li>
                      <li><a href="#">Duis aute irure</a></li>
                      <li><a href="#">Necessitatibus saepe</a></li>
                      <li><a href="#">Maiores alias</a></li>
                    </ul>
                  </div>
                </div>
                {{-- col-lg-2 col-md-4 → 33.3333% (md) / 16.6667% (lg); bg-danger/bg-info → Bootstrap hex --}}
                <div class="row megamenu-buttons text-center flex flex-wrap -mx-[15px]">
                  <div class="w-full md:w-1/3 lg:w-1/6 px-[15px]"><a href="#" class="block megamenu-button-link dashbg-1"><i class="fa fa-clock-o"></i><strong>Demo 1</strong></a></div>
                  <div class="w-full md:w-1/3 lg:w-1/6 px-[15px]"><a href="#" class="block megamenu-button-link dashbg-2"><i class="fa fa-clock-o"></i><strong>Demo 2</strong></a></div>
                  <div class="w-full md:w-1/3 lg:w-1/6 px-[15px]"><a href="#" class="block megamenu-button-link dashbg-3"><i class="fa fa-clock-o"></i><strong>Demo 3</strong></a></div>
                  <div class="w-full md:w-1/3 lg:w-1/6 px-[15px]"><a href="#" class="block megamenu-button-link dashbg-4"><i class="fa fa-clock-o"></i><strong>Demo 4</strong></a></div>
                  <div class="w-full md:w-1/3 lg:w-1/6 px-[15px]"><a href="#" class="block megamenu-button-link bg-[#dc3545]"><i class="fa fa-clock-o"></i><strong>Demo 5</strong></a></div>
                  <div class="w-full md:w-1/3 lg:w-1/6 px-[15px]"><a href="#" class="block megamenu-button-link bg-[#17a2b8]"><i class="fa fa-clock-o"></i><strong>Demo 6</strong></a></div>
                </div>
              </div>
            </div>
            <!-- Megamenu end     -->
            <!-- Languages dropdown    -->
            <div class="dropdown relative" x-data="{ open: false }" @click.outside="open = false"><a id="languages" rel="nofollow" href="#" @click.prevent="open = !open" :aria-expanded="open" aria-haspopup="true" class="nav-link language dropdown-toggle"><img src="img/flags/16/GB.png" alt="English"><span class="hidden sm:inline-block">English</span></a>
              <div aria-labelledby="languages" class="dropdown-menu absolute z-[1000]" :class="{ 'active': open }" x-show="open" x-transition.opacity x-cloak>
                <a rel="nofollow" href="#" class="dropdown-item"> <img src="img/flags/16/DE.png" alt="English" class="mr-2"><span>German</span></a>
                <a rel="nofollow" href="#" class="dropdown-item"> <img src="img/flags/16/FR.png" alt="English" class="mr-2"><span>French  </span></a>
              </div>
            </div>
            <!-- Log out               -->
            <x-admin-user-menu />

          </div>
        </div>
      </nav>
