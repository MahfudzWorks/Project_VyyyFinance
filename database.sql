CREATE DATABASE IF NOT EXISTS vyyy_finance;
USE vyyy_finance;

DROP TABLE IF EXISTS transaksi;

CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    jenis ENUM('Pemasukan', 'Pengeluaran') NOT NULL,
    keterangan VARCHAR(255) NOT NULL,
    kategori VARCHAR(100) NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    catatan TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO transaksi (tanggal, jenis, keterangan, kategori, jumlah, catatan) VALUES
('2026-08-01', 'Pemasukan', 'Gaji Bulanan Utama', 'Gaji', 7500000.00, 'Gaji bulan Agustus'),
('2026-08-02', 'Pengeluaran', 'Belanja Bulanan Supermarket', 'Belanja', 1200000.00, 'Beli kebutuhan pokok'),
('2026-08-05', 'Pemasukan', 'Proyek Web Client A', 'Freelance', 3000000.00, 'DP pengerjaan landing page'),
('2026-08-10', 'Pengeluaran', 'Bayar Tagihan Listrik & Air', 'Tagihan', 450000.00, 'Tagihan rumah'),
('2026-08-15', 'Pengeluaran', 'Makan Malam Restoran', 'Makanan', 250000.00, 'Acara keluarga'),
('2026-08-18', 'Pemasukan', 'Penjualan Barang Bekas', 'Penjualan', 350000.00, 'Jual monitor bekas'),
('2026-08-20', 'Pengeluaran', 'Bensin Motor & Servis', 'Transportasi', 150000.00, 'Rutin bulanan'),
('2026-08-22', 'Pengeluaran', 'Nonton Bioskop & Snack', 'Hiburan', 120000.00, 'Akhir pekan');