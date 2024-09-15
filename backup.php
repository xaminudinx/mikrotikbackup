<?php
// Ambil data POST
$servermikrotik = $_POST['nama'];
$token = $_POST['token'];
$id_own = $_POST['idtele'];
$filenya = $_POST['namafile'];
$password = $_POST['passwordzip'];

// Cek apakah semua data diperlukan ada
if (!empty($servermikrotik) && !empty($token) && !empty($id_own) && !empty($filenya)) {

    // Tentukan path file yang akan dikirim
    $backupFile = $filenya . '.backup';
    $rscFile = $filenya . '.rsc';
    $zipFile = $filenya . '.zip';

    // Buat file ZIP dengan password menggunakan command line
    $command = "zip -P " . escapeshellarg($password) . " " . escapeshellarg($zipFile) . " " . escapeshellarg($backupFile) . " " . escapeshellarg($rscFile);
    exec($command, $output, $result);

    if ($result === 0) {
        // Kirim file ZIP ke Telegram
        $website = "https://api.telegram.org/bot" . $token;
        $params = [
            'chat_id' => $id_own,
            'document' => new CURLFile($zipFile),
            'caption' => 'Backup ZIP file: ' . $zipFile . "\nServer: " . $servermikrotik,
            'parse_mode' => 'html',
        ];

        $ch = curl_init($website . '/sendDocument');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        // Hapus file ZIP setelah dikirim
        unlink($zipFile);

        // Hapus semua file dengan ekstensi .backup 
        array_map('unlink', glob("*.backup"));
    } else {
        echo 'Failed to create zip file with password.';
    }

} else {
    echo "MITHA BACKUP v1.0<br><br>Upload file sukses <br><br>";
}
?>
