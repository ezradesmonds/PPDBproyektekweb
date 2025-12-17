<?php
include 'koneksi.php';

// Ambil data info
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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPDB SMA Kristen Petra 2</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <script>
        if (history.scrollRestoration) { history.scrollRestoration = 'manual'; }
        window.scrollTo(0, 0);
    </script>

    <style>
        :root {
            --primary-color: #0f3460;
            --secondary-color: #e94560;
            --accent-color: #fca311;
            --light-bg: #f8f9fa;
            --text-dark: #1a1a2e;
            --card-shadow: 0 10px 40px -10px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            background-color: #fff;
            overflow-x: hidden;
        }

        h1, h2, h3, .brand-font { font-family: 'Playfair Display', serif; }
        h4, h5, .nav-link, .btn { font-family: 'Poppins', sans-serif; }

        /* Navbar Styling */
        .navbar {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.05);
            padding: 15px 0;
            transition: all 0.3s ease;
        }

        .navbar-brand { color: var(--primary-color) !important; font-size: 1.5rem; }
        .nav-link { color: #555 !important; font-weight: 500; margin: 0 10px; position: relative; transition: color 0.3s; }
        .nav-link:hover { color: var(--primary-color) !important; }
        .nav-link::after { content: ''; position: absolute; width: 0; height: 2px; bottom: 0; left: 0; background-color: var(--accent-color); transition: width 0.3s; }
        .nav-link:hover::after { width: 100%; }

        .btn-fixed-nav {
            width: 150px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            font-size: 0.9rem;
        }

        /* Hero Section */
        .hero {
            position: relative;
            background: linear-gradient(135deg, rgba(15, 52, 96, 0.9), rgba(22, 33, 62, 0.85)), url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 180px 0 250px 0;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
        }

        .hero h1 { font-size: 4.5rem; margin-bottom: 20px; letter-spacing: -1px; }
        @media (max-width: 768px) { .hero h1 { font-size: 2.5rem; } .hero { padding: 120px 0 200px 0; clip-path: polygon(0 0, 100% 0, 100% 95%, 0 100%); } }

        /* Buttons */
        .btn-custom-primary {
            background-color: var(--accent-color); color: var(--primary-color);
            font-weight: 600; padding: 12px 30px; border-radius: 50px; border: none;
            transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(252, 163, 17, 0.4);
        }
        .btn-custom-primary:hover { transform: translateY(-3px); background-color: #e5940e; color: #fff; box-shadow: 0 8px 25px rgba(252, 163, 17, 0.6); }

        .btn-outline-custom {
            border: 2px solid rgba(255,255,255,0.8); color: white; font-weight: 600;
            padding: 12px 30px; border-radius: 50px; transition: all 0.3s ease;
        }
        .btn-outline-custom:hover { background-color: white; color: var(--primary-color); }

        /* Countdown */
        .countdown-wrapper { margin-top: -120px; position: relative; z-index: 10; }
        .countdown-card {
            background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px);
            border-radius: 20px; padding: 40px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1); border: 1px solid rgba(255,255,255,0.5);
        }
        .countdown-item { font-size: 3rem; font-weight: 700; color: var(--primary-color); font-family: 'Poppins', sans-serif; line-height: 1; }
        .countdown-label { font-size: 0.9rem; text-transform: uppercase; letter-spacing: 2px; color: #666; margin-top: 5px; }

        /* Shared Card Style (Unified) */
        .unified-card {
            background: #fff; border: 1px solid #f0f0f0; border-radius: 15px; padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03); transition: all 0.3s ease;
            height: 100%; position: relative; overflow: hidden;
        }
        .unified-card:hover, .accordion-item:hover {
            transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.08); border-color: var(--accent-color);
        }
        .card-icon {
            width: 50px; height: 50px; background: rgba(15, 52, 96, 0.05); color: var(--primary-color);
            border-radius: 12px; display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; margin-bottom: 20px; transition: all 0.3s;
        }
        .unified-card:hover .card-icon { background: var(--primary-color); color: white; }

        /* Section Header */
        .section-header { text-align: center; margin-bottom: 60px; }
        .section-header h2 { font-size: 2.5rem; color: var(--primary-color); font-weight: 700; position: relative; display: inline-block; }
        .section-header h2::after { content: ''; display: block; width: 60px; height: 4px; background: var(--accent-color); margin: 15px auto 0; border-radius: 2px; }

        /* Beasiswa */
        .scholarship-card { background: linear-gradient(to right, #0f3460, #16213e); color: white; border-radius: 20px; overflow: hidden; position: relative; }
        .scholarship-card::before { content: ''; position: absolute; top: -50%; right: -10%; width: 300px; height: 300px; background: rgba(255,255,255,0.05); border-radius: 50%; }

        /* FAQ */
        .accordion-item { border: 1px solid #f0f0f0; border-radius: 15px !important; margin-bottom: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.03); transition: all 0.3s ease; background: #fff; }
        .accordion-button { background-color: transparent; color: var(--text-dark); font-weight: 600; padding: 20px 25px; box-shadow: none !important; }
        .accordion-button:not(.collapsed) { color: var(--primary-color); background-color: rgba(15, 52, 96, 0.02); }
        .accordion-body { padding: 0 25px 25px 25px; color: #666; }

        /* Footer & Alert */
        footer { background-color: var(--primary-color); color: rgba(255,255,255,0.7); padding: 60px 0 40px; text-align: center; }
        .alert-modern { border: none; border-left: 5px solid var(--accent-color); background: #fff; box-shadow: 0 5px 20px rgba(0,0,0,0.05); border-radius: 8px; padding: 20px; }
        .profile-img { border-radius: 20px; box-shadow: 20px 20px 0px rgba(15, 52, 96, 0.1); }

        html { scroll-behavior: smooth; }
        section { scroll-margin-top: 100px; }
    </style>
</head>
<body data-bs-spy="scroll" data-bs-target="#navbarNav">

    <nav class="navbar navbar-expand-lg fixed-top" id="navbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <i class="fas fa-graduation-cap fa-lg text-warning"></i>
                <span class="brand-font fw-bold">Petra 2</span>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#home">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#info">Info Pendaftaran</a></li>
                    <li class="nav-item"><a class="nav-link" href="#beasiswa">Beasiswa</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pengumuman">Pengumuman</a></li>
                    <li class="nav-item"><a class="nav-link" href="#profil">Profil</a></li>
                    <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
                    <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
                </ul>
                <div class="d-flex ms-lg-4 gap-2 mt-3 mt-lg-0">
                    <a href="login.php" class="btn btn-outline-dark rounded-pill fw-bold btn-fixed-nav" style="font-size: 0.9rem;">Masuk</a>
                    <a href="register.php" class="btn btn-custom-primary rounded-pill btn-fixed-nav" style="font-size: 0.9rem;">Daftar Akun</a>
                </div>
            </div>
        </div>
    </nav>

    <section id="home" class="hero d-flex align-items-center">
        <div class="container text-center">
            <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill fw-bold"><i class="fas fa-star me-1"></i> Akreditasi A Unggul</span>
            <h1 class="display-3 fw-bold mb-4">Membangun Generasi <br>Cerdas & Berkarakter</h1>
            <p class="lead mb-5 opacity-75 mx-auto" style="max-width: 700px;">Bergabunglah dengan SMA Kristen Petra 2 Surabaya. Tempat di mana bakat diasah, iman dikuatkan, dan masa depan diraih.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="register.php" class="btn btn-custom-primary btn-lg">Daftar Sekarang <i class="fas fa-arrow-right ms-2"></i></a>
                <a href="#profil" class="btn btn-outline-custom btn-lg">Jelajahi Sekolah</a>
            </div>
        </div>
    </section>

    <div class="container countdown-wrapper">
        <div class="countdown-card">
            <div class="row align-items-center">
                <div class="col-lg-4 text-center text-lg-start mb-4 mb-lg-0">
                    <h4 class="fw-bold mb-1" style="color: var(--primary-color);">Batas Pendaftaran</h4>
                    <p class="mb-0 text-muted">Gelombang 1 Tahun Ajaran 2026/2027</p>
                    <div class="mt-2 text-warning small fw-bold"><i class="far fa-clock me-1"></i> Jangan sampai terlewat!</div>
                </div>
                <div class="col-lg-8">
                    <div id="countdown" class="row text-center g-2 justify-content-center justify-content-lg-end"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="container" style="padding-top: 80px; padding-bottom: 80px;">
        
        <section id="info" class="mb-5 py-5">
            <div class="section-header">
                <h2>Informasi Pendaftaran</h2>
            </div>
            <div class="row g-4">
                <?php if (empty($infos['pendaftaran'])): ?>
                    <div class="col-12 text-center text-muted py-5 bg-light rounded-3">Belum ada informasi.</div>
                <?php else: ?>
                    <?php 
                    $icons = ['fas fa-file-alt', 'fas fa-info-circle', 'fas fa-list-check']; 
                    $i = 0;
                    foreach ($infos['pendaftaran'] as $info): 
                        $currentIcon = $icons[$i % count($icons)];
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="unified-card">
                            <div class="card-icon">
                                <i class="<?php echo $currentIcon; ?>"></i>
                            </div>
                            <h5 class="fw-bold mb-3"><?php echo htmlspecialchars($info['judul']); ?></h5>
                            <div class="text-muted" style="font-size: 0.95rem; line-height: 1.6;">
                                <?php echo nl2br(htmlspecialchars($info['konten'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php $i++; endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section id="beasiswa" class="mb-5 py-4">
            <div class="scholarship-card p-5">
                <div class="row align-items-center position-relative" style="z-index: 1;">
                    <div class="col-lg-12">
                        <h2 class="display-6 fw-bold mb-3">Beasiswa</h2>
                        <p class="lead mb-5 opacity-75">Berikut adalah daftar beasiswa yang tersedia di SMA Kristen Petra 2 untuk siswa berprestasi:</p>
                        
                        <?php if (!empty($infos['beasiswa'])): ?>
                            <div class="row g-4">
                                <?php foreach ($infos['beasiswa'] as $info): ?>
                                    <div class="col-md-6">
                                        <div class="bg-white bg-opacity-10 p-4 rounded-4 border border-light border-opacity-10 h-100">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-medal text-warning me-2 fs-4"></i>
                                                <h5 class="fw-bold text-white mb-0"><?php echo htmlspecialchars($info['judul']); ?></h5>
                                            </div>
                                            <div class="text-white opacity-75 small">
                                                <?php echo nl2br(htmlspecialchars($info['konten'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light bg-opacity-10 text-white border-0">Belum ada info beasiswa saat ini.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <section id="pengumuman" class="mb-5 py-5">
            <div class="section-header">
                <h2>Pengumuman Terbaru</h2>
            </div>
            
            <?php if (!empty($infos['pengumuman'])): $latest = $infos['pengumuman'][0]; ?>
            <div class="alert-modern d-flex align-items-start gap-3">
                <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning"><i class="fas fa-bullhorn fa-lg"></i></div>
                <div>
                    <h5 class="fw-bold mb-1">Info Terkini</h5>
                    <small class="text-muted d-block mb-2"><?php echo date('d F Y', strtotime($latest['created_at'])); ?></small>
                    <p class="mb-0 text-dark"><?php echo htmlspecialchars($latest['judul']); ?>: <?php echo htmlspecialchars($latest['konten']); ?></p>
                </div>
            </div>
            <?php else: ?>
                <p class="text-center text-muted">Tidak ada pengumuman baru.</p>
            <?php endif; ?>

        <section id="profil" class="py-5">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0 order-lg-2">
                    <div class="profile-img-wrapper">
                        <img src="fotosekolah.jpg" class="img-fluid profile-img" alt="Gedung Sekolah">
                    </div>
                </div>
                <div class="col-lg-6 order-lg-1">
                    <span class="text-uppercase text-warning fw-bold letter-spacing-2">Tentang Kami</span>
                    <h2 class="fw-bold display-6 mb-4 text-dark font-serif mt-2">Membentuk Pemimpin Masa Depan</h2>
                    
                    <?php if (!empty($infos['profil'])): $profil = $infos['profil'][0]; ?>
                        <h4 class="fw-bold mb-3"><?php echo htmlspecialchars($profil['judul']); ?></h4>
                        <div class="text-muted lead" style="font-size: 1.1rem; line-height: 1.8;">
                            <?php echo nl2br(htmlspecialchars($profil['konten'])); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted lead mb-4">SMA Kristen Petra 2 Surabaya berkomitmen memberikan pendidikan holistik yang berfokus pada akademik dan karakter.</p>
                        <ul class="list-unstyled">
                            <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-circle text-primary me-3"></i> Fasilitas Laboratorium Modern</li>
                            <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-circle text-primary me-3"></i> Kurikulum Internasional & Nasional</li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section id="faq" class="py-5 bg-light rounded-4 px-4 px-md-5 my-5">
            <div class="section-header mb-5">
                <h2>FAQ</h2>
                <p>Pertanyaan-pertanyaan yang sering diajukan</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <?php if (empty($infos['faq'])): ?>
                            <p class="text-center text-muted">Belum ada FAQ.</p>
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
                </div>
            </div>
        </section>

        <section id="kontak" class="py-4">
             <div class="card border-0 shadow-lg overflow-hidden rounded-4">
                 <div class="row g-0">
                     <div class="col-lg-4 bg-dark text-white p-5 d-flex flex-column justify-content-center">
                         <h3 class="fw-bold mb-4 font-serif">Kunjungi Kami</h3>
                         <div class="d-flex mb-4">
                             <div class="me-3 text-warning"><i class="fas fa-map-marker-alt fa-2x"></i></div>
                             <div><h6 class="fw-bold mb-1">Lokasi</h6><p class="mb-0 opacity-75">Jl. Manyar Tirtoasri 3-10<br>Surabaya, Jawa Timur</p></div>
                         </div>
                         <div class="d-flex mb-4">
                             <div class="me-3 text-warning"><i class="fas fa-envelope fa-2x"></i></div>
                             <div><h6 class="fw-bold mb-1">Email</h6><p class="mb-0 opacity-75">smakrpetra2@sch.id</p></div>
                         </div>
                         <div class="d-flex">
                             <div class="me-3 text-warning"><i class="fas fa-phone-alt fa-2x"></i></div>
                             <div><h6 class="fw-bold mb-1">Telepon</h6><p class="mb-0 opacity-75">(031) 594-5678</p></div>
                         </div>
                     </div>
                     <div class="col-lg-8">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3957.6110111730864!2d112.7659384!3d-7.2850221!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7fa3607e3ac55%3A0xccf5949b8ae0aa0!2sSMA%20Kristen%20Petra%202%20Surabaya!5e0!3m2!1sen!2sid!4v1765982037060!5m2!1sen!2sid" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>                     
                    </div>
                 </div>
             </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <h3 class="fw-bold mb-3 font-playfair text-white">SMA Kristen Petra 2</h3>
            <div class="border-top border-secondary border-opacity-25 pt-4 mt-4 text-center">
                <p class="mb-0 small opacity-50">© 2025 PPDB SMA Kristen Petra 2 Surabaya. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar Scroll Effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('shadow');
                navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.98)';
            } else {
                navbar.classList.remove('shadow');
                navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
            }
        });

        // Countdown Logic
        const countDownDate = new Date("Jan 31, 2026 23:59:59").getTime();
        const x = setInterval(function() {
            const now = new Date().getTime();
            const distance = countDownDate - now;
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            const pad = (n) => n < 10 ? '0' + n : n;

            document.getElementById("countdown").innerHTML = `
                <div class="col-3 col-md-2"><div class="countdown-item">${pad(days)}</div><div class="countdown-label">Hari</div></div>
                <div class="col-3 col-md-2"><div class="countdown-item">${pad(hours)}</div><div class="countdown-label">Jam</div></div>
                <div class="col-3 col-md-2"><div class="countdown-item">${pad(minutes)}</div><div class="countdown-label">Menit</div></div>
                <div class="col-3 col-md-2"><div class="countdown-item">${pad(seconds)}</div><div class="countdown-label">Detik</div></div>
            `;

            if (distance < 0) {
                clearInterval(x);
                document.getElementById("countdown").innerHTML = "<div class='col-12'><span class='badge bg-danger fs-5 px-4 py-3'>PENDAFTARAN DITUTUP</span></div>";
            }
        }, 1000);
    </script>
</body>
</html>