{{-- d-flex align-items-stretch → flex items-stretch. This wrapper holds the sidebar +
     page-content side by side and is closed in admin/footer.blade.php. --}}
<div class="flex items-stretch">
      <!-- Sidebar Navigation-->
      <nav id="sidebar">
        <!-- Sidebar Header-->
        <div class="sidebar-header flex items-center">
          {{-- img-fluid rounded-circle → max-w-full h-auto rounded-full --}}
          <div class="avatar"><img src="admin/img/avatar-6.jpg" alt="..." class="max-w-full h-auto rounded-full"></div>
          <div class="title">
            <h1 class="h5">Mark Stephen</h1>
            <p>Web Designer</p>
          </div>
        </div>
        <!-- Sidebar Navidation Menus-->
        <span class="heading">Main</span>
        <ul class="list-none">
                <li class="active"><a href={{ url('/home') }}> <i class="icon-home"></i>Home </a></li>

                {{-- Bootstrap collapse → Alpine. `x-collapse` animates the submenu height. --}}
                <li x-data="{ open: false }">
                  <a href="#exampledropdownDropdown" @click.prevent="open = !open" :aria-expanded="open"> <i class="icon-windows"></i>Hotel Rooms </a>
                  <ul id="exampledropdownDropdown" class="list-none" x-show="open" x-collapse x-cloak>
                    <li><a href="{{ url('create_room') }}">Add Rooms</a></li>
                    <li><a href="{{ url('view_room') }}">View Rooms</a></li>
                  </ul>

                </li>

                <li>
                    <a href="{{ url('bookings') }}"> <i class="icon-home"></i>Bookings </a>
                </li>
                <li>
                    <a href="{{ url('view_gallery') }}"> <i class="icon-picture"></i>Gallery</a>
                </li>
                <li>
                    <a href="{{ url('all_messages') }}"> <i class="icon-mail"></i> Messages </a>
                </li>

        </ul>

      </nav>
