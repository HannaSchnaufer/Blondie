<?php
include 'koneksi.php'; // Menghubungkan file koneksi.php

// Jumlah artikel per halaman
$limit = 3;

// Mengambil nomor halaman dari URL, jika tidak ada, default ke halaman 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Mengambil kategori dari database
$kategori_stmt = $pdo->query("SELECT * FROM kategori");
$kategori = $kategori_stmt->fetchAll();

// Mengambil kategori yang dipilih dari URL
$id_kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

// Menghitung total artikel untuk pagination
if ($id_kategori) {
    $count_stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM artikel a
        JOIN artikel_kategori ak ON a.id = ak.artikel_id
        WHERE ak.kategori_id = ?
    ");
    $count_stmt->execute([$id_kategori]);
} else {
    $count_stmt = $pdo->query("SELECT COUNT(*) FROM artikel");
}

$total = $count_stmt->fetchColumn(); // Total jumlah artikel

// Mengambil artikel berdasarkan kategori dan pagination
if ($id_kategori) {
    $stmt = $pdo->prepare("
        SELECT a.* 
        FROM artikel a
        JOIN artikel_kategori ak ON a.id = ak.artikel_id
        WHERE ak.kategori_id = ?
        LIMIT :start, :limit
    ");
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute([$id_kategori]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM artikel LIMIT :start, :limit");
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
}

// Mengambil semua hasil query dan menyimpannya dalam variabel $artikel
$artikel = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Artikel</title>
    <link rel="stylesheet" href="aka.css">
    <style>
        /* Styles for article list and pagination */
        .artikel-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .artikel-item {
            border: 1px solid #ddd;
            padding: 15px;
            width: 300px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background-color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .artikel-item img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .artikel-item h2 {
            font-size: 1.5em;
            margin: 0 0 10px;
        }
        .artikel-item p {
            margin: 5px 0;
            color: #666;
        }
        .artikel-item a {
            color: #007BFF;
            text-decoration: none;
            margin-top: 10px;
            align-self: flex-start;
        }
        .artikel-item a:hover {
            text-decoration: underline;
        }
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            margin: 0 5px;
            padding: 5px 10px;
            text-decoration: none;
            background-color: #0066cc;
            color: white;
            border-radius: 4px;
        }
        .pagination a:hover {
            background-color: #004d99;
        }
        .pagination strong {
            margin: 0 5px;
            padding: 5px 10px;
            background-color: #cccccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<header class="site-header">
    <h1>Za Monolith</h1>
</header>

<nav>
    <div class="category-list">
        <ul class="category-navbar">
            <li><a href="index.php">Home</a></li>
            <?php if (!empty($kategori)): ?>
                <?php foreach ($kategori as $cat): ?>
                    <li><a href="index.php?kategori=<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nama']); ?></a></li> 
                <?php endforeach; ?>
            <?php else: ?>
                <li><em>Tidak ada kategori tersedia</em></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<div class="artikel-container">
    <!-- Daftar artikel -->
    <?php if (!empty($artikel)): ?>
        <?php foreach ($artikel as $item): ?>
            <div class="artikel-item">
                <!-- Menampilkan gambar artikel jika ada -->
                <?php if (!empty($item['gambar'])): ?>
                    <img src="<?php echo htmlspecialchars($item['gambar']); ?>" alt="Gambar Artikel">
                <?php endif; ?>

                <!-- Menampilkan judul artikel -->
                <h2><a href="detail.php?id=<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['judul']); ?></a></h2>
                
                <!-- Menampilkan deskripsi singkat -->
                <?php if (!empty($item['konten'])): ?>
                    <p><?php echo htmlspecialchars(substr(strip_tags($item['konten']), 0, 100)); ?>...</p>
                <?php endif; ?>

                <!-- Link ke detail artikel -->
                <a href="detail.php?id=<?php echo $item['id']; ?>">Read More</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p><em>Tidak ada artikel ditemukan</em></p>
    <?php endif; ?>
</div>

<!-- Pagination Links -->
<div class="pagination">
    <?php
    $totalPages = ceil($total / $limit); // Total halaman

    // Tombol "Sebelumnya"
    if ($page > 1) {
        echo '<a href="?page=' . ($page - 1) . '">Sebelumnya</a>';
    }

    // Tampilkan link halaman
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $page) {
            echo '<strong>' . $i . '</strong> ';
        } else {
            echo '<a href="?page=' . $i . '">' . $i . '</a> ';
        }
    }

    // Tombol "Selanjutnya"
    if ($page < $totalPages) {
        echo '<a href="?page=' . ($page + 1) . '">Selanjutnya</a>';
    }
    ?>
</div>

</body>
</html>
