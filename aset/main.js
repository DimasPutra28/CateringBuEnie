// menu
const navbarNav = document.querySelector(".navbar-isi");

document.querySelector("#menu").onclick = () => {
  navbarNav.classList.toggle("active");
};

const menu = document.querySelector("#menu");

document.addEventListener("click", function (e) {
  if (!menu.contains(e.target) && !navbarNav.contains(e.target)) {
    navbarNav.classList.remove("active");
  }
});

document.addEventListener("DOMContentLoaded", function () {
  setTimeout(function () {
    // Semua elemen telah dimuat, sembunyikan loader
    document.querySelector(".loader-container").style.display = "none";
  }, 200); // 2000 milidetik (2 detik)
});

// your-script.js

document.addEventListener("DOMContentLoaded", function () {
  // Pilih elemen yang ingin diberi animasi
  var animatedElement = document.getElementById("yourElementId");

  // Tambahkan event listener untuk mendeteksi peristiwa scroll
  window.addEventListener("scroll", function () {
    // Ambil posisi scroll saat ini
    var scrollPosition = window.scrollY;

    // Tambahkan atau hapus kelas animasi berdasarkan kondisi scroll
    if (scrollPosition > 200) {
      animatedElement.classList.add("animate__animated", "animate__fadeInDown");
    } else {
      animatedElement.classList.remove(
        "animate__animated",
        "animate__fadeInDown"
      );
    }
  });
});
