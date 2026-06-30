<!DOCTYPE html>
<html>
  <head>
    @include('admin.css')
  </head>
  <body>
    @include('admin.header')
    @include('admin.sidebar')

    <div class="page-content">
      <div class="page-header">
        {{-- container-fluid → Tailwind full-width container with Bootstrap's 15px gutters --}}
        <div class="container-fluid w-full px-[15px] mx-auto">

          <center>
            <h1 style="font-size: 40px; font-weight: bolder; color: white;">Gallery</h1>

            {{-- row → flex; col-md-4 → 33.3333% from md up --}}
            <div class="row flex flex-wrap -mx-[15px]">
                @foreach($gallery as $gallery)
                <div class="w-full md:w-1/3 px-[15px]">
                    <img style="height: 200px!important; width: 300px!important;" src="/gallery/{{ $gallery->image }}">

                    <a style="margin-top: 20px; margin-bottom: 20px" class="btn btn-danger" href="{{ url('delete_gallery', $gallery->id) }}">Delete Image</a>
                </div>
                @endforeach
            </div>

            <form action="{{ url('upload_gallery') }}" method="Post" enctype="multipart/form-data">
                @csrf
                <div style="padding: 30px;">
                    <label style="color: white; font-weight: bold;">Upload Image</label>
                    <input type="file" name="image" required>
                    <input class="btn btn-primary" type="submit" value="Add Image">
                </div>
            </form>
          </center>

        </div>
      </div>
    </div>

    @include('admin.footer')
  </body>
</html>
