<?php
require_once 'koneksi.php';

// Parameter Filter & Search
$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$jenis    = isset($_GET['jenis']) ? trim($_GET['jenis']) : '';
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$bulan    = isset($_GET['bulan']) ? trim($_GET['bulan']) : '';

// 1. Hitung Ringkasan Dashboard (Secara global / Tanpa terpengaruh filter search)
$stmt_pemasukan = $pdo->query("SELECT SUM(jumlah) AS total FROM transaksi WHERE jenis = 'Pemasukan'");
$total_pemasukan = $stmt_pemasukan->fetch()['total'] ?? 0;

$stmt_pengeluaran = $pdo->query("SELECT SUM(jumlah) AS total FROM transaksi WHERE jenis = 'Pengeluaran'");
$total_pengeluaran = $stmt_pengeluaran->fetch()['total'] ?? 0;

$total_saldo = $total_pemasukan - $total_pengeluaran;

$stmt_count = $pdo->query("SELECT COUNT(*) AS total FROM transaksi");
$total_transaksi = $stmt_count->fetch()['total'] ?? 0;

// 2. Ambil daftar kategori unik untuk dropdown filter
$stmt_kat = $pdo->query("SELECT DISTINCT kategori FROM transaksi ORDER BY kategori ASC");
$kategori_list = $stmt_kat->fetchAll(PDO::FETCH_COLUMN);

// 3. Construct Query dengan Filter & Search
$sql = "SELECT * FROM transaksi WHERE 1=1";
$params = [];

if ($search !== '') {
  $sql .= " AND (keterangan LIKE :search OR kategori LIKE :search)";
  $params[':search'] = "%$search%";
}

if ($jenis !== '' && in_array($jenis, ['Pemasukan', 'Pengeluaran'])) {
  $sql .= " AND jenis = :jenis";
  $params[':jenis'] = $jenis;
}

if ($kategori !== '') {
  $sql .= " AND kategori = :kategori";
  $params[':kategori'] = $kategori;
}

if ($bulan !== '') {
  $sql .= " AND DATE_FORMAT(tanggal, '%Y-%m') = :bulan";
  $params[':bulan'] = $bulan;
}

$sql .= " ORDER BY tanggal DESC, id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transaksi_data = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Project_VyyyFinance - Dashboard Keuangan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="style.css">
</head>

<body class="bg-slate-50 text-slate-800 min-h-screen">

  <!-- Navbar -->
  <nav class="bg-indigo-600 text-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
      <h1 class="text-xl font-bold tracking-wide">Project_VyyyFinance</h1>
      <a href="tambah.php" class="bg-white text-indigo-600 hover:bg-slate-100 font-semibold px-4 py-2 rounded-lg text-sm transition shadow-sm">
        + Tambah Transaksi
      </a>
    </div>
  </nav>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Cards Summary Dashboard -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

      <!-- Card Total Saldo -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Saldo</p>
        <h3 class="text-2xl font-bold <?= $total_saldo >= 0 ? 'text-slate-800' : 'text-red-600' ?>">
          <?= formatRupiah($total_saldo) ?>
        </h3>
      </div>

      <!-- Card Total Pemasukan -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 border-l-4 border-l-emerald-500 hover:shadow-md transition">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Pemasukan</p>
        <h3 class="text-2xl font-bold text-emerald-600">
          <?= formatRupiah($total_pemasukan) ?>
        </h3>
      </div>

      <!-- Card Total Pengeluaran -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 border-l-4 border-l-rose-500 hover:shadow-md transition">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Pengeluaran</p>
        <h3 class="text-2xl font-bold text-rose-600">
          <?= formatRupiah($total_pengeluaran) ?>
        </h3>
      </div>

      <!-- Card Jumlah Transaksi -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Jumlah Transaksi</p>
        <h3 class="text-2xl font-bold text-indigo-600">
          <?= $total_transaksi ?> <span class="text-sm font-normal text-slate-500">Item</span>
        </h3>
      </div>

    </div>

    <!-- Filter & Search Section -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
      <form method="GET" action="index.php" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

        <!-- Search -->
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Cari Keterangan / Kategori</label>
          <input type="text" name="search" value="<?= sanitize($search) ?>" placeholder="Kata kunci..." class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <!-- Filter Jenis -->
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Jenis Transaksi</label>
          <select name="jenis" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Jenis</option>
            <option value="Pemasukan" <?= $jenis === 'Pemasukan' ? 'selected' : '' ?>>Pemasukan</option>
            <option value="Pengeluaran" <?= $jenis === 'Pengeluaran' ? 'selected' : '' ?>>Pengeluaran</option>
          </select>
        </div>

        <!-- Filter Kategori -->
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Kategori</label>
          <select name="kategori" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Kategori</option>
            <?php foreach ($kategori_list as $kat): ?>
              <option value="<?= sanitize($kat) ?>" <?= $kategori === $kat ? 'selected' : '' ?>><?= sanitize($kat) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Filter Bulan -->
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Pilih Bulan</label>
          <input type="month" name="bulan" value="<?= sanitize($bulan) ?>" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-end space-x-2">
          <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2 px-4 rounded-lg transition">Filter</button>
          <a href="index.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium py-2 px-3 rounded-lg transition">Reset</a>
        </div>

      </form>
    </div>

    <!-- Tabel Riwayat Transaksi -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="p-6 border-b border-slate-100 flex justify-between items-center">
        <h2 class="text-lg font-bold text-slate-800">Riwayat Transaksi</h2>
        <span class="text-xs text-slate-500">Menampilkan <?= count($transaksi_data) ?> data</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm">
          <thead>
            <tr class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-100">
              <th class="py-3 px-6">Tanggal</th>
              <th class="py-3 px-6">Jenis</th>
              <th class="py-3 px-6">Keterangan</th>
              <th class="py-3 px-6">Kategori</th>
              <th class="py-3 px-6 text-right">Jumlah</th>
              <th class="py-3 px-6 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php if (count($transaksi_data) > 0): ?>
              <?php foreach ($transaksi_data as $row): ?>
                <tr class="hover:bg-slate-50 transition">
                  <td class="py-4 px-6 text-slate-600 whitespace-nowrap">
                    <?= date('d M Y', strtotime($row['tanggal'])) ?>
                  </td>
                  <td class="py-4 px-6 whitespace-nowrap">
                    <?php if ($row['jenis'] === 'Pemasukan'): ?>
                      <span class="bg-emerald-50 text-emerald-700 text-xs font-semibold px-2.5 py-1 rounded-full border border-emerald-200">
                        Pemasukan
                      </span>
                    <?php else: ?>
                      <span class="bg-rose-50 text-rose-700 text-xs font-semibold px-2.5 py-1 rounded-full border border-rose-200">
                        Pengeluaran
                      </span>
                    <?php endif; ?>
                  </td>
                  <td class="py-4 px-6 font-medium text-slate-800">
                    <?= sanitize($row['keterangan']) ?>
                  </td>
                  <td class="py-4 px-6 text-slate-500">
                    <span class="bg-slate-100 text-slate-600 text-xs font-medium px-2 py-0.5 rounded">
                      <?= sanitize($row['kategori']) ?>
                    </span>
                  </td>
                  <td class="py-4 px-6 text-right font-semibold whitespace-nowrap <?= $row['jenis'] === 'Pemasukan' ? 'text-emerald-600' : 'text-rose-600' ?>">
                    <?= ($row['jenis'] === 'Pemasukan' ? '+ ' : '- ') . formatRupiah($row['jumlah']) ?>
                  </td>
                  <td class="py-4 px-6 text-center whitespace-nowrap space-x-2">
                    <a href="detail.php?id=<?= $row['id'] ?>" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">Detail</a>
                    <a href="edit.php?id=<?= $row['id'] ?>" class="text-amber-600 hover:text-amber-900 font-medium text-xs">Edit</a>
                    <a href="hapus.php?id=<?= $row['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');" class="text-rose-600 hover:text-rose-900 font-medium text-xs">Hapus</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="py-8 text-center text-slate-400">
                  Tidak ada data transaksi yang ditemukan.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

</body>

</html>