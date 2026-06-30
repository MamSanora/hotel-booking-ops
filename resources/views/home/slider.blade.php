{{--
   Bootstrap carousel → Alpine.js carousel.
   `active` holds the visible slide index; init() auto-advances every 5s (Bootstrap's data-ride).
   The bespoke theme (style.css) still styles `.banner .carousel-indicators li` and the
   `.booking_online` overlay, so those class names are preserved.
--}}
<section class="banner_main" x-data="{ active: 0, slides: 3, init() { setInterval(() => { this.active = (this.active + 1) % this.slides }, 5000) } }">
         <div id="myCarousel" class="carousel slide banner relative">
            {{-- Indicators: `absolute flex` replaces Bootstrap's positioning; theme handles size/colour --}}
            <ol class="carousel-indicators absolute z-10 flex">
               <li @click="active = 0" :class="{ 'active': active === 0 }" class="cursor-pointer"></li>
               <li @click="active = 1" :class="{ 'active': active === 1 }" class="cursor-pointer"></li>
               <li @click="active = 2" :class="{ 'active': active === 2 }" class="cursor-pointer"></li>
            </ol>
            <div class="carousel-inner">
               {{-- Each slide is shown/hidden via Alpine x-show instead of Bootstrap's .active class --}}
               <div class="carousel-item" x-show="active === 0">
                  <img class="first-slide block w-full" src="images/banner1.jpg" alt="First slide">
                  <div class="container mx-auto w-full max-w-[1170px] px-[15px]">
                  </div>
               </div>
               <div class="carousel-item" x-show="active === 1" x-cloak>
                  <img class="second-slide block w-full" src="images/banner2.jpg" alt="Second slide">
               </div>
               <div class="carousel-item" x-show="active === 2" x-cloak>
                  <img class="third-slide block w-full" src="images/banner3.jpg" alt="Third slide">
               </div>
            </div>
            {{-- Prev/next controls kept for markup parity; style.css hides them (display:none) --}}
            <a class="carousel-control-prev" href="#myCarousel" role="button" @click.prevent="active = (active - 1 + slides) % slides">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#myCarousel" role="button" @click.prevent="active = (active + 1) % slides">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
            </a>
         </div>
         <div class="booking_online">
            <div class="container mx-auto w-full max-w-[1170px] px-[15px]">
               {{-- row + justify-content-center → flex flex-wrap justify-center --}}
               <div class="row flex flex-wrap justify-center -mx-[15px]">
                  <div class="w-full md:w-5/12 px-[15px]">
                     <div class="book_room">
                        <h1>Book a Room Online</h1>
                        <form class="book_now" action="{{  url('search_room') }}" method="GET">
                           <div class="row flex flex-wrap -mx-[15px]">
                              <div class="w-full px-[15px]">
                                 <span>Check-In</span>
                                 <img class="date_cua" src="images/date.png">
                                 <input class="online_book" type="date" name="checkin" required>
                              </div>
                              <div class="w-full px-[15px]">
                                 <span>Check-Out</span>
                                 <img class="date_cua" src="images/date.png">
                                 <input class="online_book" type="date" name="checkout" required>
                              </div>
                              <div class="w-full px-[15px]">
                                 <input type="submit" class="book_btn" value="Book Now">
                              </div>
                           </div>
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
                        </form>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
