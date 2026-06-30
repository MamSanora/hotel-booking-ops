<div  class="our_room">
         <div class="container mx-auto w-full max-w-[1170px] px-[15px]">
            <div class="row flex flex-wrap -mx-[15px]">
               <div class="w-full px-[15px]">
                  <div class="titlepage">
                     <h2>Our Room</h2>
                     <p>Lorem Ipsum available, but the majority have suffered </p>
                  </div>
               </div>
            </div>

            <div class="row flex flex-wrap -mx-[15px]">
            @foreach ($room as $rooms)
               {{-- col-md-4 col-sm-6 → 50% from sm, 33.3333% from md --}}
               <div class="w-full sm:w-1/2 md:w-1/3 px-[15px]">
                  <div id="serv_hover"  class="room">
                     <div class="room_img">
                        <figure>
                            <img style="height: 200px; width: 350px;" src="room/{{ $rooms->image }}" alt="#"/>
                        </figure>
                     </div>
                     <div class="bed_room">
                        <h3>{{ $rooms->room_title }}</h3>
                        <p style="padding: 10px">{!! Str::limit($rooms->description, 100) !!}</p>

                        {{-- Bootstrap `.btn .btn-primary` → Tailwind reproduction --}}
                        <a class="inline-block px-3 py-1.5 text-base leading-normal text-center text-white border border-transparent rounded bg-[#007bff] hover:bg-[#0069d9] transition-colors" href="{{ url('room_details', $rooms->id) }}">Room Details</a>
                        </div>
                  </div>
               </div>
            @endforeach


            </div>
         </div>
      </div>
