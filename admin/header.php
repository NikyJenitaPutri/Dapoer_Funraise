<?php
// admin/header.php
require '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logo = $_POST['logo_path'] ?? 'assets/logo.png';
    $name = $_POST['business_name'] ?? 'Dapoer Funraise';
    $tag = $_POST['tagline'] ?? 'Cemilan rumahan yang bikin nagih!';

    $stmt = $pdo->prepare("
        INSERT INTO header (id, logo_path, business_name, tagline) 
        VALUES (1, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
            logo_path = VALUES(logo_path),
            business_name = VALUES(business_name),
            tagline = VALUES(tagline),
            updated_at = NOW()
    ");
    $stmt->execute([$logo, $name, $tag]);
    $success = "Header berhasil diperbarui!";
}

$stmt = $pdo->query("SELECT * FROM header WHERE id = 1");
$data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
    'logo_path' => 'assets/logo.png',
    'business_name' => 'Dapoer Funraise',
    'tagline' => 'Cemilan rumahan yang bikin nagih!'
];
?>

<h2>Header & Logo</h2>
<?php if (isset($success)): ?>
<div class="alert alert-sukses"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="POST">
    <div class="form-row">
        <label>Path Logo (relatif dari root)</label>
        <input type="text" name="logo_path" value="<?= htmlspecialchars($data['logo_path']) ?>" required>
    </div>
    <div class="form-row">
        <label>Nama Usaha</label>
        <input type="text" name="business_name" value="<?= htmlspecialchars($data['business_name']) ?>" maxlength="100" required>
    </div>
    <div class="form-row">
        <label>Tagline</label>
        <input type="text" name="tagline" value="<?= htmlspecialchars($data['tagline']) ?>" maxlength="150" required>
    </div>
    <button type="submit" class="btn btn-primary">Simpan Header</button>
</form>