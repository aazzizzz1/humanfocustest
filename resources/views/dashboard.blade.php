@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid">
  <div class="card">
    <div id="reaction-time-container" class="page-header min-height-300 border-radius-xl">
      <button id="start-test-btn" class="btn btn-primary">Start Test</button>
    </div>
  </div>
  <div class="card card-body blur shadow-blur mx-4 mt-n6 overflow-hidden">
    <div class="row gx-4">
      <div class="col-auto">
        <div class="avatar avatar-xl position-relative">
          <img src="../assets/img/bruce-mars.jpg" alt="profile_image" class="w-100 border-radius-lg shadow-sm">
        </div>
      </div>
      <div class="col-auto my-auto">
        <div class="row gx-4 justify-content-between">
          <div class="col-lg-10">
            <div class="h-100">
              <h5 class="mb-1">
                Hasil
              </h5>
              <p id="test-result" class="mb-0 font-weight-bold text-sm">
              </p>
              @foreach(Auth::user()->pdfs as $pdf)
                    <tr>
                      <td>{{ $pdf->file_name }}</td>
                      <td><a href="{{ route('download.pdf', $pdf->id) }}">Download</a></td>
                    </tr>
                  @endforeach
            </div>
          </div>
          <div class="col-lg-2">
            <div class="h-100">
              <button id="download-pdf-btn" class="btn btn-secondary">Download PDF</button>
              <table class="table">
                <thead>
                  <tr>
                    <th>No</th>
                    <th>Reaction Time (ms)</th>
                  </tr>
                </thead>
                <tbody id="reaction-table-body">
                </tbody>
              </table>
            </div>
          </div>
        </div>        
      </div>
    </div>
  </div>
</div>

@endsection

@push('dashboard')
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script>
  $(document).ready(function () {
      var testInterval;
      var testCount = 0;
      var reactionTimes = [];
      var testInProgress = false;
      var testResult;

      function startTest() {
          if (!testInProgress) {
              testInProgress = true;
              $("#start-test-btn").prop("disabled", true);
              runTest();
          }
      }

      function runTest() {
          var waitTime = Math.floor(Math.random() * 4) + 1;
          $("#reaction-time-container").css("background-color", "red");
          $("#test-result").html("Mohon Tunggu Sampai Berwarna Hijau...").show();
          setTimeout(function () {
              var startTime = new Date().getTime();
              $("#reaction-time-container").css("background-color", "green");
              $("#test-result").html("Tekan Sekarang!").show();

              var userClickTime;
              var clicked = false;

              $(document).on("click", function () {
                  if (!clicked) {
                      userClickTime = new Date().getTime();
                      var reactionTime = userClickTime - startTime - 300;

                      if (reactionTime <= 0) {
                          reactionTime = 0;
                      }

                      if (reactionTime <= 10000) {
                          reactionTimes.push(reactionTime);
                          testCount++;
                          $("#test-result").html("Reaksi: " + reactionTime + "ms").show();
                          $("#reaction-table-body").append("<tr><td>" + testCount + "</td><td>" + reactionTime + "</td></tr>");

                          if (testCount === 20) {
                              clearInterval(testInterval);
                              testInProgress = false;
                              $("#start-test-btn").prop("disabled", false);
                              $("#reaction-time-container").css("background-color", "");
                              $(document).off("click");

                              var totalReactionTime = reactionTimes.reduce((a, b) => a + b, 0);
                              var averageReactionTime = totalReactionTime / reactionTimes.length;

                              if (averageReactionTime < 240 && testCount === 20) {
                                  testResult = "Normal";
                              } else if (averageReactionTime < 410 && averageReactionTime > 240 && testCount === 20) {
                                  testResult = "Kelelahan Kerja Ringan (KKR)";
                              } else if (averageReactionTime < 580 && averageReactionTime > 410 && testCount === 20) {
                                  testResult = "Kelelahan Kerja Sedang (KKS)";
                              } else if (averageReactionTime >= 580 && testCount === 20) {
                                  testResult = "Kelelahan Kerja Berat (KKB)";
                              } else {
                                  testResult = "Anda perlu meningkatkan kecepatan reaksi.";
                              }

                              $("#test-result").html("<p>Waktu Reaksi: " + averageReactionTime.toFixed(2) + "ms</p><h5>Kriteria</h5><p>" + testResult + "</p>").show();
                          } else {
                              setTimeout(runTest, 2000);
                          }
                      } else {
                          $("#test-result").html("Terlalu lambat! Silakan coba lagi.").show();
                      }

                      clicked = true;
                  }
              });
          }, waitTime * 1000);
      }

      function generatePDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.text("Hasil Tes Waktu Reaksi Anda", 10, 10);
    reactionTimes.forEach((time, index) => {
        doc.text(`Tes ${index + 1}: ${time} ms`, 10, 20 + (index * 10));
    });

    const user = {!! auth()->user() !!};

    doc.text(`Nama: ${user.name}`, 10, 40 + (reactionTimes.length * 10));
    doc.text(`Email: ${user.email}`, 10, 50 + (reactionTimes.length * 10));
    doc.text(`Departemen: ${user.location}`, 10, 60 + (reactionTimes.length * 10));
    doc.text(`Nomor Telepon: ${user.phone}`, 10, 70 + (reactionTimes.length * 10));

    var totalReactionTime = reactionTimes.reduce((a, b) => a + b, 0);
    var averageReactionTime = totalReactionTime / reactionTimes.length;

    doc.text(`\nWaktu Reaksi Rata-rata: ${averageReactionTime.toFixed(2)} ms`, 10, 80 + (reactionTimes.length * 10));
    doc.text(`\nKriteria: ${testResult}`, 10, 90 + (reactionTimes.length * 10));

    const pdfOutput = doc.output('blob');
    const formData = new FormData();
    formData.append('pdf', pdfOutput, 'Hasil_Tes_Waktu_Reaksi.pdf');

    $.ajax({
        url: '{{ route("save.pdf") }}',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            alert('PDF berhasil disimpan dan ditambahkan ke database.');
        },
        error: function(response) {
            alert('Terjadi kesalahan saat menyimpan PDF.');
        }
    });

    doc.save("Hasil_Tes_Waktu_Reaksi.pdf");
}

      $("#download-pdf-btn").click(function () {
          generatePDF();
      });

      $("#start-test-btn").click(function () {
          startTest();
      });
  });
  </script>
@endpush
