<div  class="gallery">
    <div class="container mx-auto w-full max-w-[1170px] px-[15px]">
        <div class="row flex flex-wrap -mx-[15px]">
            <div class="w-full px-[15px]">
                  <div class="titlepage">
                     <h2>gallery</h2>
                  </div>
            </div>
        </div>
        <div class="row flex flex-wrap -mx-[15px]">

                @foreach($gallery as $gallery)
                {{-- col-md-3 col-sm-6 → 50% from sm, 25% from md --}}
                <div class="w-full sm:w-1/2 md:w-1/4 px-[15px]">
                    <div class="gallery_img">
                        <figure>
                            <img src="/gallery/{{ $gallery->image }}" alt="#"/>
                        </figure>
                    </div>
                </div>
                @endforeach

        </div>
    </div>
</div>
