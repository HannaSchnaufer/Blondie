<?php
include 'koneksi.php'; // Menghubungkan file koneksi.php

// Mengambil ID artikel dari URL
$id_artikel = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Jika tidak ada ID artikel, kembali ke halaman utama
if (!$id_artikel) {
    header('Location: index.php');
    exit;
}

// Mengambil data artikel berdasarkan ID
$stmt = $pdo->prepare("SELECT artikel.*, GROUP_CONCAT(kategori.nama SEPARATOR ', ') AS kategori 
                       FROM artikel 
                       JOIN artikel_kategori ON artikel.id = artikel_kategori.artikel_id 
                       JOIN kategori ON artikel_kategori.kategori_id = kategori.id 
                       WHERE artikel.id = ? 
                       GROUP BY artikel.id");
$stmt->execute([$id_artikel]);
$artikel = $stmt->fetch();

// Jika artikel tidak ditemukan, kembali ke halaman utama
if (!$artikel) {
    header('Location: index.php');
    exit;
}

// Ambil komentar dari database
$komentar_stmt = $pdo->prepare("SELECT * FROM komentar WHERE artikel_id = ? ORDER BY tanggal DESC");
$komentar_stmt->execute([$id_artikel]);
$komentar = $komentar_stmt->fetchAll();

// Tambahkan komentar baru ke database jika formulir dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '';
    $komentar_text = isset($_POST['komentar']) ? htmlspecialchars($_POST['komentar']) : '';

    if (!empty($nama) && !empty($komentar_text)) {
        $insert_stmt = $pdo->prepare("INSERT INTO komentar (artikel_id, nama, komentar) VALUES (?, ?, ?)");
        $insert_stmt->execute([$id_artikel, $nama, $komentar_text]);
        // Redirect untuk menghindari pengiriman ulang formulir
        header("Location: detail.php?id=$id_artikel");
        exit;
    }
}

// Mengambil daftar kategori untuk navigasi
$kategoriStmt = $pdo->query("SELECT * FROM kategori");
$kategoriList = $kategoriStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Artikel - <?php echo htmlspecialchars($artikel['judul']); ?></title>
    <link rel="stylesheet" href="aka.css">
</head>
<body>
    <header class="site-header">
        <h1>Za Monolith</h1>
    </header>
    
    <!-- Navigasi Kategori -->
    <nav class="category-list">
        <ul class="category-navbar">
            <li><a href="index.php">Home</a></li>
            <?php foreach ($kategoriList as $kategori): ?>
                <li>
                    <a href="index.php?kategori=<?php echo urlencode($kategori['id']); ?>">
                        <?php echo htmlspecialchars($kategori['nama']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="artikel-detail">
        <!-- Jika ada gambar -->
        <?php if (!empty($artikel['gambar'])): ?>
            <img src="<?php echo htmlspecialchars($artikel['gambar']); ?>" alt="Gambar Artikel">
        <?php endif; ?>

        <!-- Menampilkan detail artikel -->
        <h1><?php echo htmlspecialchars($artikel['judul']); ?></h1>
        <p><strong>Kategori:</strong> <?php echo htmlspecialchars($artikel['kategori']); ?></p>
        <!-- Menampilkan nama penulis -->
        <p><strong>Ditulis oleh:</strong> <?php echo htmlspecialchars($artikel['author']); ?></p>
        
        <!-- Menampilkan konten artikel dengan format HTML -->
        <div class="konten-artikel">
            <?php echo $artikel['konten']; ?>
        </div>

        <a href="index.php">Kembali ke Daftar Artikel</a>
    </div>

    <!-- Formulir Komentar -->
    <div class="komentar-form">
        <h2>Tambahkan Komentar</h2>
        <form action="detail.php?id=<?php echo $id_artikel; ?>" method="post">
            <input type="text" name="nama" placeholder="Nama" required>
            <textarea name="komentar" placeholder="Komentar Anda..." required></textarea>
            <button type="submit">Kirim Komentar</button>
        </form>
    </div>

    <!-- Daftar Komentar -->
    <div class="komentar-list">
        <h2>Komentar</h2>
        <?php if (count($komentar) > 0): ?>
            <?php foreach ($komentar as $komen): ?>
                <div class="komentar-item">
                    <p><strong><?php echo htmlspecialchars($komen['nama']); ?></strong> pada <?php echo htmlspecialchars($komen['tanggal']); ?></p>
                    <p><?php echo nl2br(htmlspecialchars($komen['komentar'])); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Belum ada komentar. Jadilah yang pertama!</p>
        <?php endif; ?>
    </div>
</body>
</html>
