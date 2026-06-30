<!DOCTYPE html>
<html>
  <head>

    @include('admin.css')
  </head>
  <body>
    <header class="header">
      @include('admin.header')
    </header>
      <!-- Sidebar Navigation-->
        @include('admin.sidebar')
      <!-- Sidebar Navigation end-->
    @include('admin.body')
    @include('admin.footer')
  </body>
</html>
