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
        <div class="h-100">
          <h5 class="mb-1">
            Hasil
          </h5>
          <p id="test-result" class="mb-0 font-weight-bold text-sm">
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
@push('dashboard')
  {{-- Script Reaction Test --}}
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
      $(document).ready(function () {
          var testInterval;
          var testCount = 0;
          var reactionTimes = [];
          var testInProgress = false;

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
            var waitTime = Math.floor(Math.random() * 5) + 1; // Random interval waktu antara 1 hingga 5 detik
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
                        var reactionTime = userClickTime - startTime;

                        if (reactionTime <= 10000) {
                            reactionTimes.push(reactionTime);
                            testCount++;
                            $("#test-result").html("Reaksi: " + reactionTime + "ms").show();

                            if (testCount === 20) {
                                clearInterval(testInterval);
                                testInProgress = false;
                                $("#start-test-btn").prop("disabled", false);
                                $("#reaction-time-container").css("background-color", "");
                                $(document).off("click");

                                // Hitung rata-rata reaksi
                                var totalReactionTime = reactionTimes.reduce((a, b) => a + b, 0);
                                var averageReactionTime = totalReactionTime / reactionTimes.length;

                                // Menentukan hasil
                                var testResult;
                                if (averageReactionTime < 1000 && testCount === 20) {
                                    testResult = "Normal";
                                } else if (averageReactionTime < 4000 && testCount === 20) {
                                    testResult = "Kelelahan Kerja Ringan (KKR)";
                                } else if (averageReactionTime < 6000 && testCount === 20) {
                                    testResult = "Kelelahan Kerja Sedang (KKS)";
                                } else if (averageReactionTime < 9000 && testCount === 20) {
                                    testResult = "Kelelahan Kerja Berat (KKB)";
                                } else {
                                    testResult = "Anda perlu meningkatkan kecepatan reaksi.";
                                }

                                // Menampilkan hasil tes
                                $("#test-result").html("<p>Hasil Tes:</p><p>Rata-rata Reaksi: " + averageReactionTime.toFixed(2) + "ms</p><p>" + testResult + "</p>").show();
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

          // Memulai tes ketika tombol "Start Test" ditekan
          $("#start-test-btn").click(function () {
              startTest();
          });
      });
  </script>
@endpush

