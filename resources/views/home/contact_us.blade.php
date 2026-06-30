<!DOCTYPE html>
<html lang="en">
   <head>

    {{-- Bootstrap CDN removed — now using Tailwind + Alpine.js (bundled in home.css). --}}
    @include ('home.css')
   </head>
   <!-- body -->
   <body class="main-layout">
      <!-- loader  -->
      <div class="loader_bg">
         <div class="loader"><img src="images/loading.gif" alt="#"/></div>
      </div>
      <!-- end loader -->
      <!-- header -->
      <header>
        @include('home.header')
      </header>
      <!-- end header inner -->
      <!-- end header -->
      <!-- banner -->
      @include('home.slider')
       <!-- end banner -->
      <!-- end banner -->
      <!-- about -->

      <!--  contact -->
      @include('home.contact')
      <!-- end contact -->
      <!--  footer -->
      @include('home.footer')

   </body>
</html>
