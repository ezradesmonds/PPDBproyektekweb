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

while ($row = $result->fetch_assoc()) {
    $infos[$row['tipe']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPDB Sekolah Impian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .navbar {
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }
        .hero {
            background-color: rgba(0, 92, 149, 0.7);
            backgrond-size: cover;
            color: white;
            padding: 150px 0;
            text-align: center;
        }
        .hero h1 {
            font-size: 4rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .section-title {
            text-align: center;
            margin-bottom: 50px;
            font-weight: 600;
            color: #343a40;
        }
        .countdown-item {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
        }
        .countdown-label {
            font-size: 1rem;
            text-transform: uppercase;
        }
        .info-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .info-card:hover {
            transform: translateY(-10px);
        }
        .faq-accordion .accordion-button {
            font-weight: 600;
        }
        footer {
            background-color: #343a40;
            color: white;
        }
        footer a {
            color: #ffc107;
            text-decoration: none;
        }
        footer a:hover {
            color: white;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-school"></i> PPDB Sekolah Impian
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#info">Info Pendaftaran</a></li>
                    <li class="nav-item"><a class="nav-link" href="#beasiswa">Beasiswa</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pengumuman">Pengumuman</a></li>
                    <li class="nav-item"><a class="nav-link" href="#profil">Profil</a></li>
                    <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
                    <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
                </ul>
                <a href="login.php" class="btn btn-warning ms-lg-3">Login</a>
                <a href="register.php" class="btn btn-outline-warning ms-lg-2">Daftar</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <h1 class="display-3">PPDB TAHUN AJARAN 2026/2027</h1>
            <p class="lead">Wujudkan mimpi dan raih prestasi bersama Sekolah Impian.</p>
            <a href="register.php" class="btn btn-warning btn-lg mt-3">Daftar Sekarang</a>
        </div>
    </section>

    <!-- Countdown -->
    <section class="py-5">
        <div class="container text-center">
            <h2 class="section-title">Pendaftaran Ditutup Dalam</h2>
            <div id="countdown" class="row justify-content-center">
                <!-- Countdown will be injected by JS -->
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container py-5">
        <!-- Info Pendaftaran -->
        <section id="info" class="mb-5">
            <h2 class="section-title">Informasi Pendaftaran</h2>
            <div class="row g-4">
                <?php if (empty($infos['pendaftaran'])): ?>
                    <p class="text-center">Belum ada informasi pendaftaran.</p>
                <?php else: ?>
                    <?php foreach ($infos['pendaftaran'] as $info): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card info-card h-100">
                            <div class="card-body">
                                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($info['judul']); ?></h5>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($info['konten'])); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Beasiswa -->
        <section id="beasiswa" class="mb-5">
            <h2 class="section-title">Info Beasiswa</h2>
            <div class="row g-4">
                <?php if (empty($infos['beasiswa'])): ?>
                    <p class="text-center">Belum ada informasi beasiswa.</p>
                <?php else: ?>
                    <?php foreach ($infos['beasiswa'] as $info): ?>
                    <div class="col-md-12">
                         <div class="card info-card mb-3">
                             <div class="card-body">
                                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($info['judul']); ?></h5>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($info['konten'])); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Pengumuman -->
        <section id="pengumuman" class="mb-5">
            <h2 class="section-title">Pengumuman PPDB</h2>
            <?php if (!empty($infos['pengumuman'])): 
                $latest_pengumuman = $infos['pengumuman'][0];
            ?>
            <div class="alert alert-warning" role="alert">
                <strong><?php echo date('d F Y', strtotime($latest_pengumuman['created_at'])); ?></strong> - <?php echo htmlspecialchars($latest_pengumuman['judul']); ?>: <?php echo htmlspecialchars($latest_pengumuman['konten']); ?>
            </div>
            <?php else: ?>
            <div class="alert alert-secondary" role="alert">
                Belum ada pengumuman terbaru.
            </div>
            <?php endif; ?>
        </section>

        <!-- Profil Sekolah -->
        <section id="profil" class="mb-5">
            <h2 class="section-title">Profil Singkat Sekolah</h2>
            <div class="row align-items-center">
                <div class="col-md-6">
                    <img src="fotosekolah.jpg" class="img-fluid rounded shadow">
                </div>
                <div class="col-md-6">
                    <?php if (!empty($infos['profil'])): 
                        $profil = $infos['profil'][0];
                    ?>
                        <h3 class="fw-bold"><?php echo htmlspecialchars($profil['judul']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($profil['konten'])); ?></p>
                    <?php else: ?>
                        <h3 class="fw-bold">Visi & Misi</h3>
                        <p><strong>Visi:</strong> Menjadi lembaga pendidikan terdepan yang menghasilkan generasi cerdas, kreatif, dan berakhlak mulia.</p>
                        <p><strong>Misi:</strong> Menyelenggarakan pendidikan berkualitas, mengembangkan bakat siswa, dan menanamkan nilai-nilai luhur.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section id="faq" class="mb-5">
            <h2 class="section-title">FAQ PPDB</h2>
            <div class="accordion faq-accordion" id="faqAccordion">
                <?php if (empty($infos['faq'])): ?>
                    <p class="text-center">Belum ada FAQ.</p>
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
        <section id="kontak" class="mb-5">
            <h2 class="section-title">Kontak & Lokasi</h2>
            <div class="row">
                <div class="col-md-6">
                    <h4 class="fw-bold">Hubungi Kami</h4>
                    <p><i class="fas fa-phone"></i> (031) 123-4567</p>
                    <p><i class="fas fa-envelope"></i> smakrpetra2@sch.id</p>
                    <p><i class="fas fa-map-marker-alt"></i> Jl. Manyar Tirtoasri 3-10, Surabaya, Jawa Timur, Indonesia</p>
                </div>
                <div class="col-md-6">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3957.6109643732907!2d112.7685133!3d-7.2850274!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7fa3607e3ac55%3A0xccf5949b8ae0aa0!2sSMA%20Kristen%20Petra%202%20Surabaya!5e0!3m2!1sen!2sid!4v1765948438348!5m2!1sen!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" width="100%" height="300" style="border:0; border-radius: 15px;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="py-4 mt-auto">
        <div class="container text-center">
            <p>&copy; 2025 PPDB SMA Kristen Petra 2 Surabaya.</p>
        </div>
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set the date we're counting down to
        const countDownDate = new Date("Jan 31, 2026 23:59:59").getTime();

        // Update the count down every 1 second
        const x = setInterval(function() {

            const now = new Date().getTime();
            const distance = countDownDate - now;

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById("countdown").innerHTML = `
                <div class="col-3"><div class="countdown-item">${days}</div><div class="countdown-label">Hari</div></div>
                <div class="col-3"><div class="countdown-item">${hours}</div><div class="countdown-label">Jam</div></div>
                <div class="col-3"><div class="countdown-item">${minutes}</div><div class="countdown-label">Menit</div></div>
                <div class="col-3"><div class="countdown-item">${seconds}</div><div class="countdown-label">Detik</div></div>
            `;

            if (distance < 0) {
                clearInterval(x);
                document.getElementById("countdown").innerHTML = "<div class='col-12 fs-3 text-danger'>PENDAFTARAN DITUTUP</div>";
            }
        }, 1000);
    </script>
</body>
</html>
