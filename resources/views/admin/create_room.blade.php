<!Doctype html>
<html lang="en">
  <head>
    @include('admin.css')

    <style type="text/css">
        label{
            display: inline-block;
            width: 200px;
        }
        .div_deg{
            padding-bottom: 30px;
        }

        .div_center{
            text-align: center;
            padding-top: 40px;
        }
    </style>
  </head>
  <body>

    <header class="header">
      @include('admin.header')
    </header>
      <!-- Sidebar Navigation-->
        @include('admin.sidebar')
      <!-- Sidebar Navigation end-->

        <div class="page-content">
                <div class="page-header">
                {{-- container-fluid → Tailwind full-width container with Bootstrap's 15px gutters --}}
                <div class="container-fluid w-full px-[15px] mx-auto">

                    <div class="div_center">
                        <h1 style="font-size: 30px; font-weight: bold;">Add Room</h1>

                     <form action="{{ url('add_room') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="div_deg">
                            <label for="room_title">Room Title</label>
                            <input type="text" name="title">
                        </div>

                        <div class="div_deg">
                            <label>Description</label>
                            <textarea name="description" rows="4"></textarea>
                        </div>

                        <div class="div_deg">
                            <label for="price">Price</label>
                            <input type="number" name="price">
                        </div>

                        <div class="div_deg">
                            <label for="type">Room Type</label>
                            <select name="type">
                                <option selected value="regular">Regular</option>
                                <option value="premium">Premium</option>
                                <option value="deluxe">Deluxe</option>
                            </select>
                        </div>

                        <div class="div_deg">
                            <label for="wifi">Wifi</label>
                            <select name="wifi">
                                <option selected value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </div>

                        <div class="div_deg">
                            <label>Upload Image</label>
                            <input type="file" name="image">
                        </div>

                        <div class="div_deg">
                            <input class="btn btn-primary" type="submit" value="Add Room">
                        </div>
                        </form>
                    </div>

                </div>
                </div>
        </div>
    {{-- @include('admin.body') --}}
    @include('admin.footer')
  </body>
