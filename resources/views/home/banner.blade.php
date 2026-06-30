{{-- Same Bootstrap-carousel → Alpine.js conversion as home/slider.blade.php --}}
<section class="banner_main" x-data="{ active: 0, slides: 3, init() { setInterval(() => { this.active = (this.active + 1) % this.slides }, 5000) } }">
         <div id="myCarousel" class="carousel slide banner relative">
            <ol class="carousel-indicators absolute z-10 flex">
               <li @click="active = 0" :class="{ 'active': active === 0 }" class="cursor-pointer"></li>
               <li @click="active = 1" :class="{ 'active': active === 1 }" class="cursor-pointer"></li>
               <li @click="active = 2" :class="{ 'active': active === 2 }" class="cursor-pointer"></li>
            </ol>
            <div class="carousel-inner">
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
               <div class="row flex flex-wrap -mx-[15px]">
                  <div class="w-full md:w-5/12 px-[15px]">
                     <div class="book_room">
                        <h1>Book a Room Online</h1>
                        <form class="book_now">
                           <div class="row flex flex-wrap -mx-[15px]">
                              <div class="w-full px-[15px]">
                                 <span>Arrival</span>
                                 <img class="date_cua" src="images/date.png">
                                 <input class="online_book" placeholder="dd/mm/yyyy" type="date" name="dd/mm/yyyy">
                              </div>
                              <div class="w-full px-[15px]">
                                 <span>Departure</span>
                                 <img class="date_cua" src="images/date.png">
                                 <input class="online_book" placeholder="dd/mm/yyyy" type="date" name="dd/mm/yyyy">
                              </div>
                              <div class="w-full px-[15px]">
                                 <button class="book_btn">Book Now</button>
                              </div>
                           </div>
                        </form>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
