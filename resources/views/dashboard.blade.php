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
  {{-- Script Reaction Test --}}
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script>
  $(document).ready(function () {
      var testInterval;
      var testCount = 0;
      var reactionTimes = [];
      var testInProgress = false;
      var testResult; // Deklarasi variabel testResult di luar fungsi

      // Fungsi untuk memulai tes reaksi
      function startTest() {
          if (!testInProgress) {
              testInProgress = true;
              $("#start-test-btn").prop("disabled", true);
              runTest();
          }
      }
    
        // Fungsi untuk menjalankan tes reaksi
        function runTest() {
            var waitTime = Math.floor(Math.random() * 4) + 1; // Random interval waktu antara 1 hingga 4 detik
            $("#reaction-time-container").css("background-color", "red");
            $("#test-result").html("Mohon Tunggu Sampai Berwarna Hijau...").show();
            setTimeout(function () {
                var startTime = new Date().getTime();
                $("#reaction-time-container").css("background-color", "green");
                $("#test-result").html("Tekan Sekarang!").show();
    
                var userClickTime;
                var clicked = false; // Variabel untuk melacak apakah pengguna sudah mengklik
    
                $(document).on("click", function () {
                    if (!clicked) { // Pastikan pengguna belum mengklik sebelumnya
                        userClickTime = new Date().getTime();
                        var reactionTime = userClickTime - startTime - 300; // Mengurangi 300ms dari waktu reaksi
    
                        if (reactionTime <= 0) {
                            reactionTime = 0; // Pastikan waktu reaksi tidak negatif
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
    
                                // Hitung rata-rata reaksi
                                var totalReactionTime = reactionTimes.reduce((a, b) => a + b, 0);
                                var averageReactionTime = totalReactionTime / reactionTimes.length;
    
                                // Menentukan testResult
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
                              
                               // Menampilkan hasil tes
                                $("#test-result").html("<p>Waktu Reaksi: " + averageReactionTime.toFixed(2) + "ms</p><h5>Kriteria</h5><p>" + testResult + "</p>").show();
                            } else {
                                setTimeout(runTest, 2000); // Tes selanjutnya dimulai setelah 2 detik
                            }
                        } else {
                            $("#test-result").html("Terlalu lambat! Silakan coba lagi.").show();
                        }
    
                        clicked = true; // Setelah pengguna mengklik, atur ke true untuk mencegah pengklikan berulang
                    }
                });
            }, waitTime * 1000); // Konversi detik menjadi milidetik
        }
    
        // Fungsi untuk menambahkan data pengguna ke PDF
        function generatePDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
    
            // Menambahkan judul dan hasil ke PDF
            doc.text("Hasil Tes Waktu Reaksi Anda", 10, 10);
            reactionTimes.forEach((time, index) => {
                doc.text(`Tes ${index + 1}: ${time} ms`, 10, 20 + (index * 10));
            });
    
            // Mengambil data pengguna
            const user = {!! Auth::user() !!}; // Jika Anda menggunakan Laravel Blade, Anda dapat mengakses data pengguna langsung dari blade template
    
            // Menambahkan informasi pengguna ke PDF
            doc.text(`Nama: ${user.name}`, 10, 40 + (reactionTimes.length * 10));
            doc.text(`Email: ${user.email}`, 10, 50 + (reactionTimes.length * 10));
            doc.text(`Departemen: ${user.location}`, 10, 60 + (reactionTimes.length * 10));
            doc.text(`Nomor Telepon: ${user.phone}`, 10, 70 + (reactionTimes.length * 10));
    
            // Hitung rata-rata reaksi dan menambahkannya ke PDF (bagian ini sama dengan sebelumnya)
              var totalReactionTime = reactionTimes.reduce((a, b) => a + b, 0);
              var averageReactionTime = totalReactionTime / reactionTimes.length;

              // Menambahkan hasil rata-rata dan kriteria ke PDF
              doc.text(`\nWaktu Reaksi Rata-rata: ${averageReactionTime.toFixed(2)} ms`, 10, 80 + (reactionTimes.length * 10));
              doc.text(`\nKriteria: ${testResult}`, 10, 90 + (reactionTimes.length * 10));
    
            // Unduh PDF
            doc.save("Hasil_Tes_Waktu_Reaksi.pdf");
        }
    
        $("#download-pdf-btn").click(function () {
            generatePDF();
        });
    
        // Memulai tes ketika tombol "Start Test" ditekan
        $("#start-test-btn").click(function () {
            startTest();
        });
    });
    </script>
    
@endpush
