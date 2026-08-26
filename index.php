<?php
require_once 'koneksi.php';

// Parameter Filter & Search
$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$jenis    = isset($_GET['jenis']) ? trim($_GET['jenis']) : '';
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$bulan    = isset($_GET['bulan']) ? trim($_GET['bulan']) : '';

// 1. Ringkasan Dashboard
$stmt_pemasukan = $pdo->query("SELECT SUM(jumlah) AS total FROM transaksi WHERE jenis = 'Pemasukan'");
$total_pemasukan = $stmt_pemasukan->fetch()['total'] ?? 0;

$stmt_pengeluaran = $pdo->query("SELECT SUM(jumlah) AS total FROM transaksi WHERE jenis = 'Pengeluaran'");
$total_pengeluaran = $stmt_pengeluaran->fetch()['total'] ?? 0;

$total_saldo = $total_pemasukan - $total_pengeluaran;

$stmt_count = $pdo->query("SELECT COUNT(*) AS total FROM transaksi");
$total_transaksi = $stmt_count->fetch()['total'] ?? 0;

// 2. Daftar Kategori Unik
$stmt_kat = $pdo->query("SELECT DISTINCT kategori FROM transaksi ORDER BY kategori ASC");
$kategori_list = $stmt_kat->fetchAll(PDO::FETCH_COLUMN);

// 3. Construct Query Filter & Search
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
  <title>Project_VyyyFinance - Modern Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="style.css">
</head>

<body class="bg-slate-100 text-slate-800 min-h-screen">

  <!-- Loading Overlay -->
  <div id="loader-overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex flex-col items-center justify-center transition-all duration-300 hidden">
    <div class="spinner mb-4"></div>
    <p class="text-white text-sm font-semibold tracking-wider animate-pulse">Memuat data...</p>
  </div>

  <!-- Navbar Modern -->
  <nav class="bg-slate-900 text-white shadow-xl border-b border-slate-800 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center font-bold text-lg shadow-lg shadow-indigo-500/30">V</div>
        <div>
          <h1 class="text-lg font-bold tracking-tight">VyyyFinance</h1>
          <p class="text-xs text-slate-400">Financial Management Dashboard</p>
        </div>
      </div>
      <a href="tambah.php" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition-all shadow-lg shadow-indigo-600/30 active:scale-95 flex items-center space-x-2">
        <span>+ Transaksi Baru</span>
      </a>
    </div>
  </nav>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 animate-fade-in">

    <!-- Cards Summary Dashboard -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

      <!-- Total Saldo -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition group">
        <div class="flex justify-between items-center mb-2">
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Saldo</p>
          <span class="p-2 bg-indigo-50 rounded-lg text-indigo-600 group-hover:scale-110 transition-transform">💰</span>
        </div>
        <h3 class="text-2xl font-extrabold counter-anim <?= $total_saldo >= 0 ? 'text-slate-900' : 'text-rose-600' ?>" data-target="<?= $total_saldo ?>">
          <?= formatRupiah($total_saldo) ?>
        </h3>
      </div>

      <!-- Total Pemasukan -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition border-l-4 border-l-emerald-500 group">
        <div class="flex justify-between items-center mb-2">
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Pemasukan</p>
          <span class="p-2 bg-emerald-50 rounded-lg text-emerald-600 group-hover:scale-110 transition-transform">📈</span>
        </div>
        <h3 class="text-2xl font-extrabold text-emerald-600 counter-anim" data-target="<?= $total_pemasukan ?>">
          <?= formatRupiah($total_pemasukan) ?>
        </h3>
      </div>

      <!-- Total Pengeluaran -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition border-l-4 border-l-rose-500 group">
        <div class="flex justify-between items-center mb-2">
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Pengeluaran</p>
          <span class="p-2 bg-rose-50 rounded-lg text-rose-600 group-hover:scale-110 transition-transform">📉</span>
        </div>
        <h3 class="text-2xl font-extrabold text-rose-600 counter-anim" data-target="<?= $total_pengeluaran ?>">
          <?= formatRupiah($total_pengeluaran) ?>
        </h3>
      </div>

      <!-- Jumlah Transaksi -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition group">
        <div class="flex justify-between items-center mb-2">
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Jumlah Transaksi</p>
          <span class="p-2 bg-slate-100 rounded-lg text-slate-600 group-hover:scale-110 transition-transform">📋</span>
        </div>
        <h3 class="text-2xl font-extrabold text-slate-800">
          <?= $total_transaksi ?> <span class="text-sm font-normal text-slate-400">Records</span>
        </h3>
      </div>

    </div>

    <!-- Filter & Search Box -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
      <form method="GET" action="index.php" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Search</label>
          <input type="text" name="search" value="<?= sanitize($search) ?>" placeholder="Kata kunci..." class="w-full text-sm border border-slate-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Jenis Transaksi</label>
          <select name="jenis" class="w-full text-sm border border-slate-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
            <option value="">Semua Jenis</option>
            <option value="Pemasukan" <?= $jenis === 'Pemasukan' ? 'selected' : '' ?>>Pemasukan</option>
            <option value="Pengeluaran" <?= $jenis === 'Pengeluaran' ? 'selected' : '' ?>>Pengeluaran</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Kategori</label>
          <select name="kategori" class="w-full text-sm border border-slate-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
            <option value="">Semua Kategori</option>
            <?php foreach ($kategori_list as $kat): ?>
              <option value="<?= sanitize($kat) ?>" <?= $kategori === $kat ? 'selected' : '' ?>><?= sanitize($kat) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Pilih Bulan</label>
          <input type="month" name="bulan" value="<?= sanitize($bulan) ?>" class="w-full text-sm border border-slate-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
        </div>

        <div class="flex items-end space-x-2">
          <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold py-2.5 px-4 rounded-xl transition active:scale-95">Filter</button>
          <a href="index.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold py-2.5 px-3.5 rounded-xl transition">Reset</a>
        </div>

      </form>
    </div>

    <!-- Tabel Riwayat Transaksi -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="p-6 border-b border-slate-100 flex justify-between items-center">
        <h2 class="text-base font-bold text-slate-900">Riwayat Transaksi</h2>
        <span class="text-xs font-semibold text-slate-400 bg-slate-100 px-3 py-1 rounded-full"><?= count($transaksi_data) ?> items</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm">
          <thead>
            <tr class="bg-slate-50 text-slate-400 text-xs uppercase tracking-wider font-semibold border-b border-slate-100">
              <th class="py-4 px-6">Tanggal</th>
              <th class="py-4 px-6">Jenis</th>
              <th class="py-4 px-6">Keterangan</th>
              <th class="py-4 px-6">Kategori</th>
              <th class="py-4 px-6 text-right">Jumlah</th>
              <th class="py-4 px-6 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php if (count($transaksi_data) > 0): ?>
              <?php foreach ($transaksi_data as $row): ?>
                <tr class="hover:bg-slate-50/80 transition-colors">
                  <td class="py-4 px-6 text-slate-500 whitespace-nowrap font-medium">
                    <?= date('d M Y', strtotime($row['tanggal'])) ?>
                  </td>
                  <td class="py-4 px-6 whitespace-nowrap">
                    <?php if ($row['jenis'] === 'Pemasukan'): ?>
                      <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full border border-emerald-200">
                        Pemasukan
                      </span>
                    <?php else: ?>
                      <span class="bg-rose-50 text-rose-700 text-xs font-bold px-3 py-1 rounded-full border border-rose-200">
                        Pengeluaran
                      </span>
                    <?php endif; ?>
                  </td>
                  <td class="py-4 px-6 font-semibold text-slate-800">
                    <?= sanitize($row['keterangan']) ?>
                  </td>
                  <td class="py-4 px-6 text-slate-500">
                    <span class="bg-slate-100 text-slate-700 text-xs font-medium px-2.5 py-1 rounded-md">
                      <?= sanitize($row['kategori']) ?>
                    </span>
                  </td>
                  <td class="py-4 px-6 text-right font-extrabold whitespace-nowrap <?= $row['jenis'] === 'Pemasukan' ? 'text-emerald-600' : 'text-rose-600' ?>">
                    <?= ($row['jenis'] === 'Pemasukan' ? '+ ' : '- ') . formatRupiah($row['jumlah']) ?>
                  </td>
                  <td class="py-4 px-6 text-center whitespace-nowrap space-x-2">
                    <a href="detail.php?id=<?= $row['id'] ?>" class="text-indigo-600 hover:text-indigo-800 font-semibold text-xs bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1.5 rounded-lg transition">Detail</a>
                    <a href="edit.php?id=<?= $row['id'] ?>" class="text-amber-600 hover:text-amber-800 font-semibold text-xs bg-amber-50 hover:bg-amber-100 px-2.5 py-1.5 rounded-lg transition">Edit</a>
                    <a href="#" onclick="return confirmDelete('hapus.php?id=<?= $row['id'] ?>', '<?= sanitize($row['keterangan']) ?>');" class="text-rose-600 hover:text-rose-800 font-semibold text-xs bg-rose-50 hover:bg-rose-100 px-2.5 py-1.5 rounded-lg transition">Hapus</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="py-12 text-center text-slate-400 font-medium">
                  Tidak ada data transaksi yang ditemukan.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- Modal Konfirmasi Hapus Custom -->
  <div id="delete-modal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl animate-fade-in border border-slate-100 text-center">
      <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-4 text-xl">⚠️</div>
      <h3 class="text-lg font-bold text-slate-900 mb-1">Hapus Transaksi?</h3>
      <p class="text-xs text-slate-500 mb-6">Anda yakin ingin menghapus <span id="modal-item-name" class="font-semibold text-slate-800"></span>? Tindakan ini tidak dapat dibatalkan.</p>
      <div class="flex space-x-3">
        <button onclick="closeDeleteModal()" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2.5 rounded-xl text-xs transition">Batal</button>
        <button onclick="proceedDelete()" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white font-semibold py-2.5 rounded-xl text-xs transition shadow-lg shadow-rose-600/30">Ya, Hapus</button>
      </div>
    </div>
  </div>

  <script src="script.js"></script>
</body>

</html>