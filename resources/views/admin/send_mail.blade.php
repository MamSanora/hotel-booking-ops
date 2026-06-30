<!DOCTYPE html>
<html>
  <head>

    <base href="/public">
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


<div class="page-content">
    <div class="page-header">
        {{-- container-fluid → Tailwind full-width container with Bootstrap's 15px gutters --}}
        <div class="container-fluid w-full px-[15px] mx-auto">

            <center>
                <h1 style="font-size: 30px; font-weight: bold;">Mail send to {{ $data->name }}</h1>

                <form action="{{ url('mail', $data->id) }}" method="POST">
                    @csrf

                    <div class="div_deg">
                        <label>Greeting</label>
                        <input type="text" name="greeting">
                    </div>

                    <div class="div_deg">
                        <label>Mail Body</label>
                        <textarea name="body"></textarea>
                    </div>

                    <div class="div_deg">
                        <label>Action Text</label>
                        <input type="text" name="action_text">
                    </div>

                    <div class="div_deg">
                        <label>Action URL</label>
                        <input type="text" name="action_url">
                    </div>

                    <div class="div_deg">
                        <label>End Line</label>
                        <input type="text" name="endline">
                    </div>

                    <div class="div_deg">
                        <input class="btn btn-primary" type="submit" value="Send Mail">
                    </div>
                </form>
            </center>

        </div>
    </div>
</div>

</body>
</html>
