# CargoTrace — AI Agent Operational Guidelines

Dokumen ini adalah protokol kerja yang mengikat AI Agent selama membantu pengerjaan proyek UAS **CargoTrace**.

---

## 1. Aturan Inti (Strict Rules)

1. **Jangan Melakukan Hal yang Tidak Disuruh:**
   - Agent hanya boleh membuat atau memodifikasi file yang termasuk dalam fase yang sedang diinstruksikan oleh pengguna.
   - Jangan melompat ke fase berikutnya sebelum pengguna memberikan perintah.
2. **Jangan Menebak (Ask When Unsure):**
   - Jika ada aturan bisnis spesifik kelompok (misalnya parameter Group A/B/C dari dosen) atau preferensi desain skema yang belum dikonfirmasi, Agent **wajib bertanya** terlebih dahulu.
3. **Protokol Awal Setiap Fase:**
   - Di setiap awal fase, Agent wajib menyampaikan:
     1. **Rekomendasi Teknis** untuk fase tersebut.
     2. **Pertanyaan Klarifikasi** (jika ada keputusan yang perlu dikonfirmasi).
     3. Eksekusi kode yang bersih, lengkap, dan siap dijalankan sesuai perintah pengguna.

---

## 2. Fokus Kualitas & Kesiapan Presentasi UAS

- **Tidak Ada Kode Placeholder:** Setiap file kode yang dihasilkan harus utuh (tidak boleh dipotong dengan komentar `// ... kode lainnya`).
- **Siap Dijelaskan Saat Interview:** Setiap kali selesai membuat kode pada suatu fase, Agent wajib menyertakan ringkasan singkat berisi poin-poin penting untuk menjawab pertanyaan *Code Ownership Interview* dosen (seperti alasan pemilihan relasi Eloquent, cara kerja otorisasi middleware, dan lokasi pengubahan *delay threshold*).