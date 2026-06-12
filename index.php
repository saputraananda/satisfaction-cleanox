<?php
session_start();

if (isset($_GET['reset'])) {
  session_destroy();
  header('Location: index.php');
  exit;
}

require_once __DIR__ . '/config/db.php';

$error          = '';   // validasi format
$duplicate      = '';   // identifier sudah ada di DB → trigger modal
$duplicate_type = '';   // 'nota' atau 'nama'
$active_tab     = $_POST['identifier_type'] ?? 'nota';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $type = $_POST['identifier_type'] ?? 'nota';

  if ($type === 'nama') {
    $nama = trim($_POST['nama'] ?? '');

    if ($nama === '') {
      $error = 'Nama tidak boleh kosong.';
    } elseif (mb_strlen($nama) < 3) {
      $error = 'Nama minimal <strong>3 karakter</strong>.';
    } else {
      $nama_safe = htmlspecialchars($nama, ENT_QUOTES, 'UTF-8');
      $conn = getConnection();
      $stmt = $conn->prepare("SELECT id FROM tr_customer_satisfaction_cleanox WHERE nama = ? LIMIT 1");
      $stmt->bind_param('s', $nama);
      $stmt->execute();
      $stmt->store_result();

      if ($stmt->num_rows > 0) {
        $duplicate      = $nama_safe;
        $duplicate_type = 'nama';
      } else {
        $_SESSION['identifier_type'] = 'nama';
        $_SESSION['no_nota']         = null;
        $_SESSION['nama']            = $nama;
        $_SESSION['step']            = 'csat';
        header('Location: csat.php');
        exit;
      }
      $stmt->close();
      $conn->close();
    }
    $active_tab = 'nama';
  } else {
    // Default: nomor nota
    $no_nota = trim($_POST['no_nota'] ?? '');

    if ($no_nota === '') {
      $error = 'Nomor nota tidak boleh kosong.';
    } elseif (!preg_match('/^\d{6}$/', $no_nota)) {
      $error = 'Nomor nota harus tepat <strong>6 digit angka</strong>.';
    } else {
      $no_nota_safe = htmlspecialchars($no_nota, ENT_QUOTES, 'UTF-8');
      $conn = getConnection();
      $stmt = $conn->prepare("SELECT id FROM tr_customer_satisfaction_cleanox WHERE no_nota = ? LIMIT 1");
      $stmt->bind_param('s', $no_nota);
      $stmt->execute();
      $stmt->store_result();

      if ($stmt->num_rows > 0) {
        $duplicate      = $no_nota_safe;
        $duplicate_type = 'nota';
      } else {
        $_SESSION['identifier_type'] = 'nota';
        $_SESSION['no_nota']         = $no_nota;
        $_SESSION['nama']            = null;
        $_SESSION['step']            = 'csat';
        header('Location: csat.php');
        exit;
      }
      $stmt->close();
      $conn->close();
    }
    $active_tab = 'nota';
  }
}

$posted_nota = htmlspecialchars($_POST['no_nota'] ?? '', ENT_QUOTES, 'UTF-8');
$posted_nama = htmlspecialchars($_POST['nama'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Survey Kepuasan — PT Cleanox Indonesia</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Poppins', 'sans-serif'] },
          animation: {
            'fade-up': 'fadeUp .55s ease-out both',
            'card-in': 'cardIn .6s ease-out both',
            'modal-in': 'modalIn .3s cubic-bezier(.175,.885,.32,1.275) both',
          },
          keyframes: {
            fadeUp   : { '0%': { opacity: '0', transform: 'translateY(20px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
            cardIn   : { '0%': { opacity: '0', transform: 'translateY(28px) scale(.97)' }, '100%': { opacity: '1', transform: 'translateY(0) scale(1)' } },
            modalIn  : { '0%': { opacity: '0', transform: 'scale(.92) translateY(12px)' }, '100%': { opacity: '1', transform: 'scale(1) translateY(0)' } },
          },
        },
      },
    }
  </script>
  <style>
    * { font-family: 'Poppins', sans-serif; }
    body { background: linear-gradient(135deg, #0C2461 0%, #1e3799 100%); }
    .bubble { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.12); animation: floatUp linear infinite; }
    @keyframes floatUp {
      0%   { transform: translateY(100vh) scale(1); opacity: .12; }
      100% { transform: translateY(-20vh) scale(1.2); opacity: 0; }
    }
    input:focus { outline: none; }
    .btn-primary { background: #16a34a; transition: background .2s, transform .15s, box-shadow .2s; }
    .btn-primary:hover:not(:disabled) { background: #15803d; box-shadow: 0 8px 24px rgba(22,163,74,.35); }
    .btn-primary:active:not(:disabled) { transform: scale(0.98); }
    .btn-primary:disabled { opacity: .45; cursor: not-allowed; }
    .input-field { transition: border-color .2s, box-shadow .2s, background .2s; }
    .input-field:focus { border-color: #0C2461; background: #fff; box-shadow: 0 0 0 3px rgba(12,36,97,.1); }
    .input-field.error { border-color: #EF4444; box-shadow: 0 0 0 3px rgba(239,68,68,.1); }
    /* Tab active states */
    .tab-active { background: #0C2461; color: #fff; }
    .tab-inactive { background: #F1F5F9; color: #64748B; }
    .tab-inactive:hover { background: #E2E8F0; }
    /* Modal overlay */
    #modalOverlay { transition: opacity .25s; }
    #modalOverlay.hidden { display: none; }
  </style>
</head>

<body class="min-h-screen flex items-start sm:items-center justify-center p-4 py-8 relative">

  <div id="bubblesContainer" class="fixed inset-0 pointer-events-none overflow-hidden"></div>

  <!-- ===== MODAL: Survey sudah diisi ===== -->
  <?php if ($duplicate): ?>
  <div id="modalOverlay" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.5);">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm px-8 py-8 animate-modal-in text-center">
      <!-- Icon -->
      <div class="flex justify-center mb-4">
        <div class="w-16 h-16 rounded-full flex items-center justify-center" style="background:#EFF6FF;">
          <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#0C2461;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
        </div>
      </div>
      <h3 class="text-lg font-bold text-gray-800 mb-2">Survey Sudah Diisi</h3>
      <p class="text-sm text-gray-500 mb-1"><?= $duplicate_type === 'nama' ? 'Nama' : 'Nomor nota' ?></p>
      <p class="text-base font-bold mb-3" style="color:#0C2461;"><?= $duplicate ?></p>
      <p class="text-sm text-gray-400 leading-relaxed mb-6">
        <?= $duplicate_type === 'nama' ? 'Nama ini' : 'Nota ini' ?> sudah pernah mengisi survey.<br>Terima kasih atas partisipasi Anda!
      </p>
      <button onclick="closeModal()" class="btn-primary w-full py-3 rounded-2xl text-white font-semibold text-sm">
        Tutup
      </button>
    </div>
  </div>
  <?php endif; ?>

  <!-- ===== CARD ===== -->
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md px-8 py-10 animate-card-in relative z-10">

    <!-- Logo & Brand -->
    <div class="flex flex-col items-center mb-8">
      <img src="image/cleanox.png" alt="PT Cleanox Indonesia" class="h-16 w-auto mb-3 object-contain">
      <h1 class="text-xl font-bold" style="color:#0C2461;">PT Cleanox Indonesia</h1>
      <p class="text-xs font-medium text-gray-400 mt-0.5 tracking-wide uppercase">Survey Kepuasan Pelanggan</p>
    </div>

    <!-- Tab Switcher -->
    <div class="flex p-1 rounded-2xl bg-gray-100 mb-6" role="tablist">
      <button type="button" id="tabNota" onclick="switchTab('nota')" role="tab"
        class="flex-1 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-1.5 <?= $active_tab === 'nota' ? 'tab-active' : 'tab-inactive' ?>">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Nomor Nota
      </button>
      <button type="button" id="tabNama" onclick="switchTab('nama')" role="tab"
        class="flex-1 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-1.5 <?= $active_tab === 'nama' ? 'tab-active' : 'tab-inactive' ?>">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        Nama Pelanggan
      </button>
    </div>

    <!-- Inline error (validasi format) -->
    <?php if ($error): ?>
    <div class="mb-5 flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm">
      <svg class="w-5 h-5 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
      </svg>
      <span><?= $error ?></span>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" autocomplete="off" id="surveyForm">
      <input type="hidden" id="identifier_type" name="identifier_type" value="<?= htmlspecialchars($active_tab, ENT_QUOTES, 'UTF-8') ?>">

      <!-- ===== TAB: Nomor Nota ===== -->
      <div id="contentNota" style="<?= $active_tab === 'nota' ? '' : 'display:none;' ?>">
        <label class="block text-sm font-semibold text-gray-600 mb-2" for="no_nota">Nomor Nota Transaksi</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-4 flex items-center text-gray-400 pointer-events-none">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </span>
          <input
            type="text"
            id="no_nota"
            name="no_nota"
            placeholder="6 digit terakhir nomor nota"
            value="<?= $posted_nota ?>"
            maxlength="6"
            inputmode="numeric"
            pattern="[0-9]{6}"
            class="input-field <?= $error && $active_tab === 'nota' ? 'error' : '' ?> w-full pl-12 pr-16 py-3.5 rounded-2xl border-2 border-gray-200 text-gray-800 font-medium text-sm bg-gray-50"
            oninput="onNotaInput(this)"
          >
          <!-- Digit counter badge -->
          <span id="digitCounter"
            class="absolute inset-y-0 right-4 flex items-center text-xs font-semibold pointer-events-none"
            style="color:#CBD5E1;">
            <span id="digitCount">0</span>/6
          </span>
        </div>
        <p id="digitHint" class="text-xs mt-1.5 ml-1 text-gray-400">Masukkan 6 digit angka dari nomor nota Anda.</p>
      </div>

      <!-- ===== TAB: Nama Pelanggan ===== -->
      <div id="contentNama" style="<?= $active_tab === 'nama' ? '' : 'display:none;' ?>">
        <label class="block text-sm font-semibold text-gray-600 mb-2" for="nama">Nama Lengkap Anda</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-4 flex items-center text-gray-400 pointer-events-none">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </span>
          <input
            type="text"
            id="nama"
            name="nama"
            placeholder="Masukkan nama lengkap Anda"
            value="<?= $posted_nama ?>"
            maxlength="200"
            class="input-field <?= $error && $active_tab === 'nama' ? 'error' : '' ?> w-full pl-12 pr-4 py-3.5 rounded-2xl border-2 border-gray-200 text-gray-800 font-medium text-sm bg-gray-50"
            oninput="onNamaInput(this)"
          >
        </div>
        <p id="namaHint" class="text-xs mt-1.5 ml-1 text-gray-400">Minimal 3 karakter, gunakan nama asli Anda.</p>
      </div>

      <button type="submit" id="submitBtn" disabled
        class="btn-primary mt-5 w-full py-3.5 rounded-2xl text-white font-semibold text-base shadow-md flex items-center justify-center gap-2">
        <span id="btnText">Mulai Survey</span>
        <svg id="btnIcon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>
        <svg id="btnSpinner" class="w-5 h-5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
      </button>
    </form>

    <p class="text-center text-xs text-gray-400 mt-6 leading-relaxed">
      Data Anda aman dan hanya digunakan untuk meningkatkan<br>
      kualitas layanan <span class="font-semibold" style="color:#0C2461;">PT Cleanox Indonesia</span>.
    </p>
  </div>

  <script>
    let activeTab = '<?= addslashes($active_tab) ?>';

    function switchTab(tab) {
      activeTab = tab;
      document.getElementById('identifier_type').value = tab;

      // Toggle tab button styles
      const tabNota = document.getElementById('tabNota');
      const tabNama = document.getElementById('tabNama');
      tabNota.className = tabNota.className.replace(/tab-active|tab-inactive/g, '').trim() + (tab === 'nota' ? ' tab-active' : ' tab-inactive');
      tabNama.className = tabNama.className.replace(/tab-active|tab-inactive/g, '').trim() + (tab === 'nama' ? ' tab-active' : ' tab-inactive');

      // Toggle content
      document.getElementById('contentNota').style.display = tab === 'nota' ? '' : 'none';
      document.getElementById('contentNama').style.display = tab === 'nama' ? '' : 'none';

      // Re-validate active tab
      if (tab === 'nota') {
        onNotaInput(document.getElementById('no_nota'));
      } else {
        onNamaInput(document.getElementById('nama'));
      }
    }

    function onNotaInput(input) {
      input.value = input.value.replace(/\D/g, '').slice(0, 6);
      const len     = input.value.length;
      const counter = document.getElementById('digitCount');
      const hint    = document.getElementById('digitHint');
      const submit  = document.getElementById('submitBtn');
      const badge   = document.getElementById('digitCounter');

      counter.textContent = len;

      if (len === 0) {
        badge.style.color = '#CBD5E1';
        hint.textContent  = 'Masukkan 6 digit angka dari nomor nota Anda.';
        hint.style.color  = '#94a3b8';
        input.classList.remove('error');
        submit.disabled   = true;
      } else if (len < 6) {
        badge.style.color = '#F59E0B';
        hint.textContent  = `Masih kurang ${6 - len} digit lagi.`;
        hint.style.color  = '#F59E0B';
        input.classList.remove('error');
        submit.disabled   = true;
      } else {
        badge.style.color = '#10B981';
        hint.textContent  = 'Siap! Klik Mulai Survey.';
        hint.style.color  = '#10B981';
        input.classList.remove('error');
        submit.disabled   = false;
      }
    }

    function onNamaInput(input) {
      const len    = input.value.trim().length;
      const hint   = document.getElementById('namaHint');
      const submit = document.getElementById('submitBtn');

      if (len === 0) {
        hint.textContent  = 'Minimal 3 karakter, gunakan nama asli Anda.';
        hint.style.color  = '#94a3b8';
        input.classList.remove('error');
        submit.disabled   = true;
      } else if (len < 3) {
        hint.textContent  = `Masih kurang ${3 - len} karakter lagi.`;
        hint.style.color  = '#F59E0B';
        input.classList.remove('error');
        submit.disabled   = true;
      } else {
        hint.textContent  = 'Siap! Klik Mulai Survey.';
        hint.style.color  = '#10B981';
        input.classList.remove('error');
        submit.disabled   = false;
      }
    }

    // Init counters if values pre-filled (e.g. after POST error)
    const notaInput = document.getElementById('no_nota');
    const namaInput = document.getElementById('nama');
    if (notaInput.value.length > 0) onNotaInput(notaInput);
    if (namaInput.value.length > 0) onNamaInput(namaInput);

    function closeModal() {
      document.getElementById('modalOverlay').classList.add('hidden');
      if (activeTab === 'nota') {
        notaInput.value = '';
        onNotaInput(notaInput);
        notaInput.focus();
      } else {
        namaInput.value = '';
        onNamaInput(namaInput);
        namaInput.focus();
      }
    }

    document.getElementById('surveyForm').addEventListener('submit', function() {
      document.getElementById('btnText').textContent = 'Memproses...';
      document.getElementById('btnIcon').classList.add('hidden');
      document.getElementById('btnSpinner').classList.remove('hidden');
      document.getElementById('submitBtn').disabled = true;
    });

    // Floating bubbles
    const container = document.getElementById('bubblesContainer');
    for (let i = 0; i < 12; i++) {
      const size  = Math.random() * 60 + 20 | 0;
      const left  = Math.random() * 100 | 0;
      const delay = (Math.random() * 8).toFixed(1);
      const dur   = (Math.random() * 10 + 9).toFixed(1);
      const el    = document.createElement('div');
      el.className = 'bubble';
      el.style.cssText = `width:${size}px;height:${size}px;left:${left}%;bottom:-${size}px;animation-duration:${dur}s;animation-delay:${delay}s;`;
      container.appendChild(el);
    }
  </script>
</body>
</html>
