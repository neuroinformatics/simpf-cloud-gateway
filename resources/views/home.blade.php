<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Simulation Platform Cloud Gateway Service</title>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">

        <!-- Styles -->
        <link rel="stylesheet" href="css/modal.css">
        <link rel="stylesheet" href="css/loading.css">
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 60px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <div class="title m-b-md">
                    Simulation Platform <br /> Cloud Gateway Service
                </div>

                <div class="links">
                    <a href="http://sim.neuroinf.jp/">Simulation Platform</a>
                    <a href="https://www.neuroinf.jp/">INCF Japan Node</a>
                    <a href="https://cbs.riken.jp/">RIKEN Center for Brain Science</a>
                </div>
            </div>
        </div>
<div id="modal-panel" class="modal">
  <div class="modal-content">
    <h1 id="modal-title-panel">
      <span id="modal-title-error"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i></span>
      <span id="modal-title"></span>
    </h1>
    <p id="modal-message-panel">
      <span id="modal-message"></span>
    </p>
    <div id="modal-loading" class="sk-fading-circle">
      <div class="sk-circle1 sk-circle"></div>
      <div class="sk-circle2 sk-circle"></div>
      <div class="sk-circle3 sk-circle"></div>
      <div class="sk-circle4 sk-circle"></div>
      <div class="sk-circle5 sk-circle"></div>
      <div class="sk-circle6 sk-circle"></div>
      <div class="sk-circle7 sk-circle"></div>
      <div class="sk-circle8 sk-circle"></div>
      <div class="sk-circle9 sk-circle"></div>
      <div class="sk-circle10 sk-circle"></div>
      <div class="sk-circle11 sk-circle"></div>
      <div class="sk-circle12 sk-circle"></div>
    </div>
    <div id="modal-download">
      <button id="modal-download-button"><i class="fas fa-cloud-download-alt" aria-hidden="true"></i>  Download</button>
    </div>
  </div>
</div>
<script>
(function($) {
$(function() {

function simpf_error(message) {
  $('#modal-title').text('Conneciton Failure');
  $('#modal-loading').hide();
  $('#modal-title-error').show();
  $('#modal-message').text(message);
}

function simpf_desktop_connect(sid, token, dataSource) {
  setTimeout(function() {
    window.location.href = 'https://simpf.med.kanazawa-u.ac.jp/desktop/#/client?token=' + token;
  }, 1000);
}

function simpf_desktop_login(sid) {
  let endpoint = 'https://simpf.med.kanazawa-u.ac.jp/desktop/api/tokens';
  $.ajax({
    type: 'POST',
    url: endpoint,
    data: {'username': sid, 'password': ''},
  }).done(function (response) {
    simpf_desktop_connect(sid, response.authToken, response.dataSource);
  }).fail(function (XMLHttpRequest, textStatus, errorThrown) {
    simpf_error('failed to login remote desktop service');
  });
}

function simpf_dispatch(url, type, dsize) {
  $('#modal-title').text('Connecting..');
  $('#modal-message').text('Dispatching virtual machine..');
  $('#modal-panel').show();
  let endpoint = '{{ url('/api/dispatch') }}';
  $.ajax({
    type: 'GET',
    url: endpoint,
    data: {'url': url, 'type': type, 'dsize': dsize}
  }).done(function (response) {
    if ('ERROR' == response.status) {
      simpf_error(response.result);
    } else {
      $('#modal-message').text('Redirecting..');
      simpf_desktop_login(response.result.sid);
    }
  }).fail(function (XMLHttpRequest, textStatus, errorThrown) {
    simpf_error('failed to dispatch virtual machine service');
  });
}

function simpf_download_dialog(sid) {
  $('#modal-title').text('Connection closed');
  $('#modal-message').text('To download the result files, click the "Download" button below.');
  $('#modal-download').show();
  $('#modal-loading').hide();
  $('#modal-download-button').on('click', function() {
    window.location.href = '{{ url('/download') }}' + '/' + sid + '/result.zip';
  });
  $('#modal-panel').show();
}

let url = @json($url);
let type = @json($type);
let dsize = @json($dsize);
let sid = @json($download);
if ('' !== url) {
  simpf_dispatch(url, type, dsize);
}
if ('' !== sid) {
  simpf_download_dialog(sid);
}

});
})(jQuery);
</script>
    </body>
</html>
