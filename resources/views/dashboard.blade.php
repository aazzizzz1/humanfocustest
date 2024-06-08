@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid">
  <div class="card">
    <div class="card-header">
      <h4>Silakan Pilih Jenis Tes</h4>
      <div>
        <input type="radio" id="visual-test" name="test-type" value="visual" checked>
        <label for="visual-test">Tes Visual</label>
        <input type="radio" id="audio-test" name="test-type" value="audio">
        <label for="audio-test">Tes Audio</label>
      </div>
    </div>
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
              <p>Nama: {{ Auth::user()->name }}</p>
              <p>Usia: {{ Auth::user()->age }}</p>
              <p>Departemen: {{ Auth::user()->location }}</p>
              <h5 class="mb-1">
                Hasil
              </h5>
              <p id="test-result" class="mb-0 font-weight-bold text-sm"></p>
            </div>
          </div>
          <div class="col-lg-2">
            <div class="h-100">
              <button id="download-pdf-btn" class="btn btn-secondary">Save and Download PDF</button>
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
              var testType = $('input[name="test-type"]:checked').val();
              if (testType === 'visual') {
                  runVisualTest();
              } else {
                  runAudioTest();
              }
          }
      }

      function runVisualTest() {
          var waitTime = Math.floor(Math.random() * 5) + 1;
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
                      var reactionTime = userClickTime - startTime - 240;

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
                              setTimeout(runVisualTest, 2000);
                          }
                      } else {
                          $("#test-result").html("Terlalu lambat! Silakan coba lagi.").show();
                      }

                      clicked = true;
                  }
              });
          }, waitTime * 1000);
      }

      function runAudioTest() {
          var waitTime = Math.floor(Math.random() * 5) + 1;
          $("#reaction-time-container").css("background-color", "black");
          $("#test-result").html("Mohon Tunggu Sampai Mendengar Suara...").show();
          setTimeout(function () {
              var startTime = new Date().getTime();
              var audio = new Audio('https://www.soundjay.com/buttons/sounds/beep-08b.mp3');
              audio.play();
              $("#test-result").html("Tekan Sekarang!").show();

              var userClickTime;
              var clicked = false;

              $(document).on("click", function () {
                  if (!clicked) {
                      userClickTime = new Date().getTime();
                      var reactionTime = userClickTime - startTime - 240;

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
                              setTimeout(runAudioTest, 2000);
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

      const user = {!! json_encode(auth()->user()) !!}; // Gunakan json_encode untuk mendapatkan data pengguna sebagai objek JavaScript

      // Tambahkan informasi pengguna di bagian atas PDF
      doc.text(`Nama: ${user.name}`, 10, 20);
      doc.text(`Usia: ${user.age}`, 10, 30);
      doc.text(`Departemen: ${user.location}`, 10, 40);
      doc.text(`Nomor Telepon: ${user.phone}`, 10, 50);
      doc.text(`Pekerjaan: ${user.job}`, 10, 60);
      doc.text(`Tempat Kerja: ${user.work_location}`, 10, 70);
      doc.text(`Nama Penguji: ${user.examiner_name}`, 10, 80);

      // Dapatkan tanggal dan waktu saat ini
      const currentDate = new Date();
      const dateString = currentDate.toLocaleDateString();
      const timeString = currentDate.toLocaleTimeString();

      doc.text(`Tanggal: ${dateString}`, 10, 90);
      doc.text(`Waktu: ${timeString}`, 10, 100);

      // Tambahkan judul hasil tes
      doc.text("Hasil Tes Waktu Reaksi Anda", 10, 120);

      // Atur hasil tes menjadi dua kolom di bawah informasi pengguna
      const leftColumnX = 10;
      const rightColumnX = 110;
      const yOffset = 130; // Awal Y untuk hasil tes
      const rowHeight = 10;

      reactionTimes.forEach((time, index) => {
          if (index < 10) {
              doc.text(`Tes ${index + 1}: ${time} ms`, leftColumnX, yOffset + (index * rowHeight));
          } else {
              doc.text(`Tes ${index + 1}: ${time} ms`, rightColumnX, yOffset + ((index - 10) * rowHeight));
          }
      });

      var totalReactionTime = reactionTimes.reduce((a, b) => a + b, 0);
      var averageReactionTime = totalReactionTime / reactionTimes.length;

      // Tambahkan hasil rata-rata dan kriteria di bawah hasil tes
      const resultsYOffset = reactionTimes.length > 10 ? yOffset + ((reactionTimes.length - 10) * rowHeight) + 10 : yOffset + (reactionTimes.length * rowHeight);
      doc.text(`Waktu Reaksi Rata-rata: ${averageReactionTime.toFixed(2)} ms`, 10, resultsYOffset);
      doc.text(`Kriteria: ${testResult}`, 10, resultsYOffset + 10);

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
