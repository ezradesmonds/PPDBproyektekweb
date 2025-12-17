<?php
include 'koneksi.php';

// Fetch all info data
$sql = "SELECT * FROM info ORDER BY created_at DESC";
$result = $koneksi->query($sql);

// Group info by type
$infos = [
    'pendaftaran' => [],
    'beasiswa' => [],
    'pengumuman' => [],
    'profil' => [],
    'faq' => [],
];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        if (isset($infos[$row['tipe']])) {
            $infos[$row['tipe']][] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPDB Sekolah Impian - Selamat Datang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-bs-spy="scroll" data-bs-target="#navbarNav">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top" id="navbar">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-school text-primary"></i> PPDB Sekolah Impian
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#info">Info</a></li>
                    <li class="nav-item"><a class="nav-link" href="#profil">Profil</a></li>
                    <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
                    <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
                </ul>
                <a href="login.php" class="btn btn-outline-primary ms-lg-3">Login</a>
                <a href="register.php" class="btn btn-primary ms-lg-2">Daftar</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <h1 class="display-3">PPDB TAHUN AJARAN 2026/2027</h1>
            <p class="lead">Wujudkan mimpi dan raih prestasi bersama Sekolah Impian, tempat para juara dilahirkan.</p>
            <a href="register.php" class="btn btn-primary btn-lg mt-3">Daftar Sekarang <i class="fas fa-arrow-right ms-2"></i></a>
        </div>
    </section>

    <!-- Countdown -->
    <section id="countdown-section" class="py-5">
        <div class="container text-center">
             <div class="section-title">
                <h2>Pendaftaran Ditutup Dalam</h2>
            </div>
            <div id="countdown" class="row justify-content-center">
                <!-- Countdown will be injected by JS -->
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container py-5">
        <!-- Info Pendaftaran & Beasiswa -->
        <section id="info" class="mb-5">
            <div class="section-title">
                <h2>Informasi PPDB</h2>
            </div>
            
            <!-- Pengumuman -->
            <?php if (!empty($infos['pengumuman'])): 
                $latest_pengumuman = $infos['pengumuman'][0];
            ?>
            <div class="announcement-bar text-center p-3 mb-5" role="alert">
                <h5 class="mb-1"><i class="fas fa-bullhorn me-2"></i> Pengumuman Terbaru</h5>
                <p class="mb-0"><strong><?php echo htmlspecialchars($latest_pengumuman['judul']); ?>:</strong> <?php echo htmlspecialchars($latest_pengumuman['konten']); ?> (<?php echo date('d F Y', strtotime($latest_pengumuman['created_at'])); ?>)</p>
            </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-7">
                    <h4 class="mb-3">Info Pendaftaran</h4>
                     <?php if (empty($infos['pendaftaran'])): ?>
                        <p class="text-center">Belum ada informasi pendaftaran.</p>
                    <?php else: ?>
                        <?php foreach ($infos['pendaftaran'] as $info): ?>
                        <div class="info-card mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($info['judul']); ?></h5>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($info['konten'])); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="col-lg-5">
                     <h4 class="mb-3">Info Beasiswa</h4>
                     <?php if (empty($infos['beasiswa'])): ?>
                        <p class="text-center">Belum ada informasi beasiswa.</p>
                    <?php else: ?>
                        <?php foreach ($infos['beasiswa'] as $info): ?>
                         <div class="info-card mb-3">
                             <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($info['judul']); ?></h5>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($info['konten'])); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Profil Sekolah -->
        <section id="profil" class="mb-5 py-5 bg-light rounded-3">
             <div class="container">
                 <div class="section-title">
                    <h2>Profil Sekolah</h2>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-6 mb-4 mb-md-0">
                        <img src="fotosekolah.jpg" class="img-fluid rounded shadow">
                    </div>
                    <div class="col-md-6">
                        <?php if (!empty($infos['profil'])): 
                            $profil = $infos['profil'][0];
                        ?>
                            <h3><?php echo htmlspecialchars($profil['judul']); ?></h3>
                            <p class="lead"><?php echo nl2br(htmlspecialchars($profil['konten'])); ?></p>
                        <?php else: ?>
                            <h3>Visi & Misi Sekolah Impian</h3>
                            <p class="lead">Menjadi lembaga pendidikan terdepan yang menghasilkan generasi cerdas, kreatif, dan berakhlak mulia.</p>
                            <ul>
                                <li>Menyelenggarakan pendidikan berkualitas yang terintegrasi dengan teknologi.</li>
                                <li>Mengembangkan potensi dan bakat siswa secara optimal.</li>
                                <li>Menanamkan nilai-nilai luhur, etika, dan integritas.</li>
                                <li>Menciptakan lingkungan belajar yang aman, nyaman, dan inspiratif.</li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
             </div>
        </section>

        <!-- FAQ -->
        <section id="faq" class="mb-5">
            <div class="section-title">
                <h2>Tanya Jawab (FAQ)</h2>
            </div>
            <div class="accordion faq-accordion" id="faqAccordion">
                <?php if (empty($infos['faq'])): ?>
                    <p class="text-center">Belum ada FAQ yang tersedia.</p>
                <?php else: ?>
                    <?php foreach ($infos['faq'] as $index => $faq): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>">
                                <?php echo htmlspecialchars($faq['judul']); ?>
                            </button>
                        </h2>
                        <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index == 0 ? 'show' : ''; ?>" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?php echo nl2br(htmlspecialchars($faq['konten'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Kontak & Lokasi -->
        <section id="kontak" class="mb-5 py-5 bg-light rounded-3">
            <div class="container">
                <div class="section-title">
                    <h2>Kontak & Lokasi</h2>
                </div>
                <div class="row g-4">
                    <div class="col-lg-6 contact-info">
                        <h4 class="mb-4">Hubungi Kami</h4>
                        <p><i class="fas fa-phone fa-fw"></i> (031) 123-4567</p>
                        <p><i class="fas fa-envelope fa-fw"></i> ppdb@sekolahimpian.sch.id</p>
                        <p><i class="fas fa-map-marker-alt fa-fw"></i> Jl. Manyar Tirtoasri 3-10, Surabaya, Jawa Timur, Indonesia</p>
                    </div>
                    <div class="col-lg-6">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3957.6109643732907!2d112.7685133!3d-7.2850274!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7fa3607e3ac55%3A0xccf5949b8ae0aa0!2sSMA%20Kristen%20Petra%202%20Surabaya!5e0!3m2!1sen!2sid!4v1765948438348!5m2!1sen!2sid" width="100%" height="300" style="border:0; border-radius: 15px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="mt-auto">
        <div class="container text-center">
             <div class="footer-brand mb-3">
                <i class="fas fa-school"></i> PPDB Sekolah Impian
            </div>
            <div class="social-icons mb-3">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
            <p>&copy; <?php echo date("Y"); ?> PPDB Sekolah Impian. All Rights Reserved.</p>
        </div>
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });

        // Countdown timer
        const countDownDate = new Date("Jan 31, 2026 23:59:59").getTime();
        const x = setInterval(function() {
            const now = new Date().getTime();
            const distance = countDownDate - now;

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            const countdownElement = document.getElementById("countdown");
            if(countdownElement){
                countdownElement.innerHTML = `
                    <div class="col-3"><div class="countdown-item">${days}</div><div class="countdown-label">Hari</div></div>
                    <div class="col-3"><div class="countdown-item">${hours}</div><div class="countdown-label">Jam</div></div>
                    <div class="col-3"><div class="countdown-item">${minutes}</div><div class="countdown-label">Menit</div></div>
                    <div class="col-3"><div class="countdown-item">${seconds}</div><div class="countdown-label">Detik</div></div>
                `;
            }

            if (distance < 0) {
                clearInterval(x);
                if(countdownElement) {
                    countdownElement.innerHTML = "<div class='col-12 fs-3 text-danger fw-bold'>PENDAFTARAN SUDAH DITUTUP</div>";
                }
            }
        }, 1000);
    </script>
</body>
</html>
