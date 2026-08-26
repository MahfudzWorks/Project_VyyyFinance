<?php
require_once 'koneksi.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
  header("Location: index.php");
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM transaksi WHERE id = :id");
$stmt->execute([':id' => $id]);
$data = $stmt->fetch();

if (!$data) {
  header("Location: index.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Transaksi - Project_VyyyFinance</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="style.css">
</head>

<body class="bg-slate-50 text-slate-800 min-h-screen py-10">

  <div class="max-w-2xl mx-auto px-4">

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8">
      <div class="mb-6 flex justify-between items-center border-b border-slate-100 pb-4">
        <h1 class="text-xl font-bold text-slate-800">Detail Transaksi</h1>
        <a href="index.php" class="text-slate-500 hover:text-slate-700 text-sm font-medium">← Kembali</a>
      </div>

      <div class="space-y-4">

        <div class="flex justify-between items-center py-2 border-b border-slate-50">
          <span class="text-xs text-slate-400 font-semibold uppercase">Jenis Transaksi</span>
          <?php if ($data['jenis'] === 'Pemasukan'): ?>
            <span class="bg-emerald-50 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full border border-emerald-200">Pemasukan</span>
          <?php else: ?>
            <span class="bg-rose-50 text-rose-700 text-xs font-semibold px-3 py-1 rounded-full border border-rose-200">Pengeluaran</span>
          <?php endif; ?>
        </div>

        <div class="flex justify-between items-center py-2 border-b border-slate-50">
          <span class="text-xs text-slate-400 font-semibold uppercase">Jumlah</span>
          <span class="text-xl font-bold <?= $data['jenis'] === 'Pemasukan' ? 'text-emerald-600' : 'text-rose-600' ?>">
            <?= ($data['jenis'] === 'Pemasukan' ? '+ ' : '- ') . formatRupiah($data['jumlah']) ?>
          </span>
        </div>

        <div class="flex justify-between items-center py-2 border-b border-slate-50">
          <span class="text-xs text-slate-400 font-semibold uppercase">Tanggal Transaksi</span>
          <span class="text-sm font-medium text-slate-700"><?= date('d F Y', strtotime($data['tanggal'])) ?></span>
        </div>

        <div class="flex justify-between items-center py-2 border-b border-slate-50">
          <span class="text-xs text-slate-400 font-semibold uppercase">Keterangan</span>
          <span class="text-sm font-medium text-slate-800"><?= sanitize($data['keterangan']) ?></span>
        </div>

        <div class="flex justify-between items-center py-2 border-b border-slate-50">
          <span class="text-xs text-slate-400 font-semibold uppercase">Kategori</span>
          <span class="bg-slate-100 text-slate-700 text-xs font-medium px-2.5 py-1 rounded"><?= sanitize($data['kategori']) ?></span>
        </div>

        <div class="py-2 border-b border-slate-50">
          <span class="block text-xs text-slate-400 font-semibold uppercase mb-1">Catatan</span>
          <p class="text-sm text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-100">
            <?= !empty($data['catatan']) ? nl2br(sanitize($data['catatan'])) : '-' ?>
          </p>
        </div>

        <div class="grid grid-cols-2 gap-4 py-2 text-xs text-slate-400">
          <div>
            <span class="block font-medium">Dibuat Pada:</span>
            <span><?= date('d M Y H:i', strtotime($data['created_at'])) ?></span>
          </div>
          <div>
            <span class="block font-medium">Terakhir Diperbarui:</span>
            <span><?= date('d M Y H:i', strtotime($data['updated_at'])) ?></span>
          </div>
        </div>

      </div>

      <div class="mt-8 flex space-x-3 pt-4 border-t border-slate-100">
        <a href="edit.php?id=<?= $data['id'] ?>" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white text-center font-medium py-2.5 rounded-lg text-sm transition">Edit Transaksi</a>
        <a href="hapus.php?id=<?= $data['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');" class="bg-rose-600 hover:bg-rose-700 text-white font-medium py-2.5 px-5 rounded-lg text-sm transition">Hapus</a>
      </div>

    </div>

  </div>

</body>

</html>