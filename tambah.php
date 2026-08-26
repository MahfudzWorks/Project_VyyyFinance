<?php
require_once 'koneksi.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tanggal    = sanitize($_POST['tanggal'] ?? '');
  $jenis      = sanitize($_POST['jenis'] ?? '');
  $keterangan = sanitize($_POST['keterangan'] ?? '');
  $kategori   = sanitize($_POST['kategori'] ?? '');
  $jumlah     = sanitize($_POST['jumlah'] ?? '');
  $catatan    = sanitize($_POST['catatan'] ?? '');

  // Validasi
  if (empty($tanggal)) $errors[] = "Tanggal wajib diisi.";
  if (!in_array($jenis, ['Pemasukan', 'Pengeluaran'])) $errors[] = "Jenis transaksi tidak valid.";
  if (empty($keterangan)) $errors[] = "Keterangan wajib diisi.";
  if (empty($kategori)) $errors[] = "Kategori wajib diisi.";
  if (!is_numeric($jumlah) || $jumlah <= 0) $errors[] = "Jumlah harus berupa angka positif.";

  if (empty($errors)) {
    $sql = "INSERT INTO transaksi (tanggal, jenis, keterangan, kategori, jumlah, catatan) 
                VALUES (:tanggal, :jenis, :keterangan, :kategori, :jumlah, :catatan)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':tanggal'    => $tanggal,
      ':jenis'      => $jenis,
      ':keterangan' => $keterangan,
      ':kategori'   => $kategori,
      ':jumlah'     => $jumlah,
      ':catatan'    => $catatan
    ]);

    header("Location: index.php");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Transaksi - Project_VyyyFinance</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="style.css">
</head>

<body class="bg-slate-50 text-slate-800 min-h-screen py-10">

  <div class="max-w-2xl mx-auto px-4">

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8">
      <div class="mb-6 flex justify-between items-center border-b border-slate-100 pb-4">
        <h1 class="text-xl font-bold text-slate-800">Tambah Transaksi Baru</h1>
        <a href="index.php" class="text-slate-500 hover:text-slate-700 text-sm font-medium">← Kembali</a>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-sm space-y-1">
          <?php foreach ($errors as $error): ?>
            <p>• <?= $error ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="tambah.php" class="space-y-5">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal *</label>
            <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis Transaksi *</label>
            <select name="jenis" required class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
              <option value="Pemasukan">Pemasukan</option>
              <option value="Pengeluaran">Pengeluaran</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Keterangan *</label>
          <input type="text" name="keterangan" placeholder="Contoh: Gaji Bulanan, Beli Makanan" required class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori *</label>
            <input type="text" name="kategori" placeholder="Contoh: Gaji, Makanan, Transportasi" required class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Jumlah (Rp) *</label>
            <input type="number" step="0.01" name="jumlah" placeholder="0" required class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan Tambahan</label>
          <textarea name="catatan" rows="3" placeholder="Catatan Opsional..." class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
        </div>

        <div class="pt-4 flex space-x-3">
          <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-lg text-sm transition">Simpan Transaksi</button>
          <a href="index.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium py-2.5 px-5 rounded-lg text-sm transition">Batal</a>
        </div>

      </form>
    </div>

  </div>

</body>

</html>