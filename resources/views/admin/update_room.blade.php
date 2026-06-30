<!DOCTYPE html>
<html>
  <head>
    <base href="/public"> @include('admin.css')

    <style>
        label {
            display: inline-block;
            width: 200px;
        }
        .div_deg {
            padding-top: 30px;
        }
        .div_center {
            text-align: center;
            padding-top: 40px;
        }
        h1 {
            font-size: 30px;
            font-weight: bold;
        }
    </style>
  </head>
  <body>
    @include('admin.header')
    @include('admin.sidebar')

    <div class="page-content">
      <div class="page-header">
        {{-- container-fluid → Tailwind full-width container with Bootstrap's 15px gutters --}}
        <div class="container-fluid w-full px-[15px] mx-auto">

          <div class="div_center">
            <h1 style="font-size: 30px; font-weight: bold;">Update Room</h1>

            <form action="{{ url('edit_room', $data->id) }}" method="Post" enctype="multipart/form-data">

                @csrf

                <div class="div_deg">
                    <label>Room Title</label>
                    <input type="text" name="title" value="{{ $data->room_title }}">
                </div>

                <div class="div_deg">
                    <label>Description</label>
                    <textarea name="description">{{ $data->description }}</textarea>
                </div>

                <div class="div_deg">
                    <label>Price</label>
                    <input type="number" name="price" value="{{ $data->price }}">
                </div>

                <div class="div_deg">
                    <label>Room Type</label>
                    <select name="type">
                        <option selected value="{{ $data->room_type }}">{{ $data->room_type }}</option>
                        <option value="regular">Regular</option>
                        <option value="premium">Premium</option>
                        <option value="deluxe">Deluxe</option>
                    </select>
                </div>

                <div class="div_deg">
                    <label>Free Wifi</label>
                    <select name="wifi">
                        <option selected value="{{ $data->wifi }}">{{ $data->wifi }}</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>

                <div class="div_deg">
                    <label>Current Image</label>
                    <img style="margin: auto" width="100" src="/room/{{ $data->image }}">
                </div>

                <div class="div_deg">
                    <label>Upload Image</label>
                    <input type="file" name="image">
                </div>

                <div class="div_deg">
                    <input type="submit" value="Update Room" class="btn btn-primary">
                </div>
            </form>
          </div>

        </div>
      </div>
    </div>
    @include('admin.footer')
  </body>
</html>
