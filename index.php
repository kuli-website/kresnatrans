<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa Bus</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Bus Jogjakarta</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#armada">Armada</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#packages">Paket</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Kontak</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-md-6" data-aos="fade-right">
                    <h1 class="display-4 fw-bold">Sewa
                    </h1>
                    <p class="lead">Layanan transportasi bus premium untuk perjalanan Anda yang aman dan nyaman di Yogyakarta dan sekitarnya.</p>
                    <a href="#contact" class="btn btn-primary btn-lg">Hubungi Kami</a>
                </div>
                <div class="col-md-6" data-aos="fade-left">
                    <div class="hero-image">
                        <!-- Hero image placeholder -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6" data-aos="fade-right">
                    <h2 class="section-title">Tentang Kami</h2>
                    <p>Selamat datang di Bus Jogja, penyedia layanan sewa bus terpercaya di Yogyakarta. Kami menyediakan berbagai pilihan armada bus modern dengan kondisi prima untuk memenuhi kebutuhan transportasi Anda.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check-circle text-primary me-2"></i>Armada bus modern dan terawat</li>
                        <li><i class="fas fa-check-circle text-primary me-2"></i>Driver profesional dan berpengalaman</li>
                        <li><i class="fas fa-check-circle text-primary me-2"></i>Layanan 24/7</li>
                        <li><i class="fas fa-check-circle text-primary me-2"></i>Harga bersaing</li>
                    </ul>
                </div>
                <div class="col-md-6" data-aos="fade-left">
                    <!-- About image placeholder -->
                </div>
            </div>
        </div>
    </section>

    <!-- Armada Section -->
    <section id="armada" class="armada-section py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center mb-5">Armada Kami</h2>
            <div class="row">
                <?php
                // Placeholder for database query to get bus fleet
                $armada = [
                    ['name' => 'Big Bus', 'capacity' => '59 Seats'],
                    ['name' => 'Medium Bus', 'capacity' => '35 Seats'],
                    ['name' => 'Mini Bus', 'capacity' => '25 Seats']
                ];

                foreach ($armada as $bus) {
                    echo '<div class="col-md-4 mb-4" data-aos="fade-up">
                        <div class="card h-100">
                            <div class="card-img-top bus-image">
                                <!-- Bus image placeholder -->
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">' . $bus['name'] . '</h5>
                                <p class="card-text">Kapasitas: ' . $bus['capacity'] . '</p>
                                <a href="#contact" class="btn btn-outline-primary">Info Lebih Lanjut</a>
                            </div>
                        </div>
                    </div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Packages Section -->
    <section id="packages" class="packages-section py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5">Paket Sewa Bus</h2>
            <div class="row">
                <div class="col-md-4 mb-4" data-aos="fade-up">
                    <div class="card pricing-card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Paket City Tour</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Mulai dari</h6>
                            <p class="price">Rp 2.000.000</p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check me-2"></i>Durasi 12 jam</li>
                                <li><i class="fas fa-check me-2"></i>Driver profesional</li>
                                <li><i class="fas fa-check me-2"></i>BBM</li>
                                <li><i class="fas fa-check me-2"></i>Air mineral</li>
                            </ul>
                            <a href="#contact" class="btn btn-primary w-100">Pesan Sekarang</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card pricing-card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Paket Wisata</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Mulai dari</h6>
                            <p class="price">Rp 3.500.000</p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check me-2"></i>Durasi 24 jam</li>
                                <li><i class="fas fa-check me-2"></i>Driver profesional</li>
                                <li><i class="fas fa-check me-2"></i>BBM</li>
                                <li><i class="fas fa-check me-2"></i>Air mineral</li>
                                <li><i class="fas fa-check me-2"></i>Snack</li>
                            </ul>
                            <a href="#contact" class="btn btn-primary w-100">Pesan Sekarang</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card pricing-card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Paket Drop Off</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Mulai dari</h6>
                            <p class="price">Rp 1.500.000</p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check me-2"></i>One way trip</li>
                                <li><i class="fas fa-check me-2"></i>Driver profesional</li>
                                <li><i class="fas fa-check me-2"></i>BBM</li>
                                <li><i class="fas fa-check me-2"></i>Air mineral</li>
                            </ul>
                            <a href="#contact" class="btn btn-primary w-100">Pesan Sekarang</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center mb-5">Hubungi Kami</h2>
            <div class="row">
                <div class="col-md-6 mb-4" data-aos="fade-right">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Kirim Pesan</h5>
                            <form action="process.php" method="POST">
                                <div class="mb-3">
                                    <input type="text" class="form-control" name="name" placeholder="Nama Lengkap" required>
                                </div>
                                <div class="mb-3">
                                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                                </div>
                                <div class="mb-3">
                                    <input type="tel" class="form-control" name="phone" placeholder="No. Telepon" required>
                                </div>
                                <div class="mb-3">
                                    <textarea class="form-control" name="message" rows="4" placeholder="Pesan" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Kirim Pesan</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4" data-aos="fade-left">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Informasi Kontak</h5>
                            <ul class="list-unstyled contact-info">
                                <li class="mb-3">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    Jl. Malioboro No. 123, Yogyakarta
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-phone me-2"></i>
                                    <a href="tel:+628123456789" class="text-decoration-none">+62 812-3456-789</a>
                                </li>
                                <li class="mb-3">
                                    <i class="fab fa-whatsapp me-2"></i>
                                    <a href="https://wa.me/628123456789" class="text-decoration-none">+62 812-3456-789</a>
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-envelope me-2"></i>
                                    <a href="mailto:info@busjogja.com" class="text-decoration-none">info@busjogja.com</a>
                                </li>
                            </ul>
                            <div class="social-media mt-4">
                                <a href="#" class="text-decoration-none me-3"><i class="fab fa-facebook fa-2x"></i></a>
                                <a href="#" class="text-decoration-none me-3"><i class="fab fa-instagram fa-2x"></i></a>
                                <a href="#" class="text-decoration-none"><i class="fab fa-youtube fa-2x"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer py-4 bg-dark text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Bus Jogja</h5>
                    <p>Solusi transportasi terpercaya untuk perjalanan Anda di Yogyakarta dan sekitarnya.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#home" class="text-white text-decoration-none">Beranda</a></li>
                        <li><a href="#about" class="text-white text-decoration-none">Tentang</a></li>
                        <li><a href="#armada" class="text-white text-decoration-none">Armada</a></li>
                        <li><a href="#packages" class="text-white text-decoration-none">Paket</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Jam Operasional</h5>
                    <ul class="list-unstyled">
                        <li>Senin - Jumat: 08:00 - 17:00</li>
                        <li>Sabtu: 09:00 - 15:00</li>
                        <li>Minggu: Tutup</li>
                    </ul>
                </div>
            </div>
            <hr class="mt-4">
            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Bus Jogja. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>