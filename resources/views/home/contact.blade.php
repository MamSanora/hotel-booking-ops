<div class="contact">
         <div class="container mx-auto w-full max-w-[1170px] px-[15px]">
            <div class="row flex flex-wrap -mx-[15px]">
               <div class="w-full px-[15px]">
                  <div class="titlepage">
                     <h2>Contact Us</h2>
                  </div>

                  @if (session()->has('message'))
                      {{-- Bootstrap `.alert .alert-success` → Tailwind; dismiss handled by Alpine x-show --}}
                      <div x-data="{ show: true }" x-show="show"
                           class="relative mb-4 rounded border border-[#c3e6cb] bg-[#d4edda] px-5 py-3 text-[#155724]">
                        <button type="button" @click="show = false"
                                class="absolute right-3 top-2 text-2xl leading-none font-bold opacity-50 hover:opacity-100">X</button>
                          {{ session()->get('message') }}
                      </div>
                  @endif

               </div>
            </div>
            <div class="row flex flex-wrap -mx-[15px]">
               {{-- col-md-6 → 50% from md up --}}
               <div class="w-full md:w-1/2 px-[15px]">
                  <form id="request" class="main_form" action="{{ url('contact') }}" method="Post">

                    @csrf
                     <div class="row flex flex-wrap -mx-[15px]">
                        <div class="w-full px-[15px]">
                           <input class="contactus" placeholder="Name" type="type" name="name" required>
                        </div>
                        <div class="w-full px-[15px]">
                           <input class="contactus" placeholder="Email" type="email" name="email" required>
                        </div>
                        <div class="w-full px-[15px]">
                           <input class="contactus" placeholder="Phone Number" type="number" name="phone" required>
                        </div>
                        <div class="w-full px-[15px]">
                           <textarea class="textarea" placeholder="Message" type="type" name="message"></textarea>
                        </div>
                        <div class="w-full px-[15px]">
                           <button type="submit" class="send_btn">Send</button>
                        </div>
                     </div>
                  </form>
               </div>
               <div class="w-full md:w-1/2 px-[15px]">
                  <div class="map_main">
                     <div class="map-responsive">
                        <iframe src="https://www.google.com/maps/embed/v1/place?key=AIzaSyA0s1a7phLN0iaD6-UE7m4qP-z21pH0eSc&amp;q=Eiffel+Tower+Paris+France" width="600" height="400" frameborder="0" style="border:0; width: 100%;" allowfullscreen=""></iframe>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
