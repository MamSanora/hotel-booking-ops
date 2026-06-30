<!DOCTYPE html>
<html lang="en">
   <head>
      <base href="/public">
      @include('home.css')

      <style>
        label {
            display: inline-block;
            width: 200px;
        }
        input {
            width: 100%;
        }
    </style>
    {{-- Bootstrap CDN removed — Tailwind + Alpine bundled via home.css --}}
   </head>
<body>
   <main class="main-layout">
      <header>
         @include('home.header')
      </header>

      <div class="our_room">
         <div class="container mx-auto w-full max-w-[1170px] px-[15px]">
            <div class="row flex flex-wrap -mx-[15px]">
               <div class="w-full px-[15px]">
                  <div class="titlepage">
                     <h2>Our Room</h2>
                     <p>Lorem Ipsum available, but the majority have suffered</p>
                  </div>
               </div>
            </div>
            <div class="row flex flex-wrap -mx-[15px]">

               {{-- col-md-8 → 66.6667% from md up --}}
               <div class="w-full md:w-2/3 px-[15px]">
                  <div id="serv_hover" class="room">
                     <div style="padding: 20px" class="room_img">
                        <figure><img style="height: 300px; width: 800px" src="/room/{{ $room->image }}" alt="#"/></figure>
                     </div>
                     <div class="bed_room">
                        <h2>{{ $room->room_title }}</h2>
                        <p style="padding: 12px">{{ $room->description }}</p>
                        <h4 style="padding: 12px">Free Wifi : {{ $room->wifi }}</h4>
                        <h4 style="padding: 12px">Room Type : {{ $room->room_type }}</h4>
                        <h3 style="padding: 12px">Price : ${{ $room->price }}</h3>
                     </div>
                  </div>
               </div>

               {{-- Book Room Form — col-md-4 → 33.3333% from md up --}}
               <div class="w-full md:w-1/3 px-[15px]">
                    <h1 style="font-size: 40px!important;">Book Room</h1>

                    <div>
                        @if(session()->has('message'))
                            {{-- Bootstrap `.alert .alert-success` → Tailwind; dismiss via Alpine --}}
                            <div x-data="{ show: true }" x-show="show"
                                 class="relative mb-4 rounded border border-[#c3e6cb] bg-[#d4edda] px-5 py-3 text-[#155724]">
                                <button type="button" @click="show = false"
                                        class="absolute right-3 top-2 text-2xl leading-none font-bold opacity-50 hover:opacity-100">X</button>

                                {{ session()->get('message') }}
                            </div>
                        @endif
                    </div>

                    @if($errors)
                        @foreach($errors->all() as $errors)
                            <li style="color: red">
                                {{ $errors }}
                            </li>
                        @endforeach
                    @endif

                    <form action="{{ url('add_booking', $room->id) }}" method="Post">
                        @csrf
                        <div style="padding-top: 20px;">
                            <label>Name</label>
                            <input type="text" name="name"
                            @if(Auth::id()) value="{{ Auth::user()->name }}" @endif>
                        </div>

                        <div style="padding-top: 20px;">
                            <label>Email</label>
                            <input type="email" name="email"
                            @if(Auth::id()) value="{{ Auth::user()->email }}" @endif>
                        </div>

                        <div style="padding-top: 20px;">
                            <label>Phone</label>
                            <input type="number" name="phone"
                            @if(Auth::id()) value="{{ Auth::user()->phone }}" @endif>
                        </div>

                        <div style="padding-top: 20px;">
                            <label>Start Date</label>
                            <input type="date" name="startDate" id="startDate" value="{{ request('checkin') }}">
                        </div>

                        <div style="padding-top: 20px;">
                            <label>End Date</label>
                            <input type="date" name="endDate" id="endDate" value="{{ request('checkout') }}">
                        </div>

                        <div style="padding-top: 20px;">
                            {{-- Bootstrap `.btn .btn-primary` → Tailwind (inline skyblue background kept) --}}
                            <input type="submit" style="background-color: skyblue;" class="inline-block px-3 py-1.5 text-base leading-normal text-center text-white border border-transparent rounded cursor-pointer" value="Book Room">
                        </div>
                    </form>
                </div>

            </div>
         </div>
      </div>

      @include('home.footer')
      <script type="text/javascript">
        $(function(){
            var dtToday = new Date();

            var month = dtToday.getMonth() + 1;
            var day = dtToday.getDate();
            var year = dtToday.getFullYear();

            if(month < 10)
                month = '0' + month.toString();
            if(day < 10)
                day = '0' + day.toString();

            var maxDate = year + '-' + month + '-' + day;
            $('#startDate').attr('min', maxDate);
            $('#endDate').attr('min', maxDate);
        });
      </script>
   </main>
{{-- Bootstrap JS bundle removed — Alpine.js (via @vite) handles interactivity --}}
</body>
</html>
