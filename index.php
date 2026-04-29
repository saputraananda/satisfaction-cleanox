<?php
session_start();

if (isset($_GET['reset'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_nota = trim(htmlspecialchars($_POST['no_nota'] ?? '', ENT_QUOTES, 'UTF-8'));

    if ($no_nota === '') {
        $error = 'Nomor nota tidak boleh kosong.';
    } elseif (strlen($no_nota) > 100) {
        $error = 'Nomor nota terlalu panjang.';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT id FROM tr_customer_satisfaction WHERE no_nota = ? LIMIT 1");
        $stmt->bind_param('s', $no_nota);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Nomor nota <strong>' . $no_nota . '</strong> sudah pernah mengisi survey. Terima kasih!';
        } else {
            $_SESSION['no_nota'] = $no_nota;
            $_SESSION['step']    = 'csat';
            header('Location: csat.php');
            exit;
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Survey Kepuasan — Waschen Alora</title>
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
          },
          keyframes: {
            fadeUp: { '0%': { opacity: '0', transform: 'translateY(20px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
            cardIn: { '0%': { opacity: '0', transform: 'translateY(28px) scale(.97)' }, '100%': { opacity: '1', transform: 'translateY(0) scale(1)' } },
          },
        },
      },
    }
  </script>
  <style>
    * { font-family: 'Poppins', sans-serif; }
    body { background: linear-gradient(135deg, #5B005F 0%, #8A4A8D 100%); }
    .bubble { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.12); animation: floatUp linear infinite; }
    @keyframes floatUp {
      0%   { transform: translateY(100vh) scale(1); opacity: .12; }
      100% { transform: translateY(-20vh) scale(1.2); opacity: 0; }
    }
    input:focus { outline: none; }
    .btn-primary { background: #5B005F; transition: background .2s, transform .15s, box-shadow .2s; }
    .btn-primary:hover { background: #430046; box-shadow: 0 8px 24px rgba(91,0,95,.35); }
    .btn-primary:active { transform: scale(0.98); }
    .input-field:focus { border-color: #5B005F; background: #fff; box-shadow: 0 0 0 3px rgba(91,0,95,.1); }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

  <div id="bubblesContainer" class="absolute inset-0 pointer-events-none overflow-hidden"></div>

  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md px-8 py-10 animate-card-in relative z-10">

    <!-- Logo & Brand -->
    <div class="flex flex-col items-center mb-8">
      <img src="image/waschen.png" alt="Waschen Alora" class="h-16 w-auto mb-3 object-contain">
      <h1 class="text-xl font-bold text-gray-900" style="color:#5B005F;">Waschen Alora</h1>
      <p class="text-xs font-medium text-gray-400 mt-0.5 tracking-wide uppercase">Survey Kepuasan Pelanggan</p>
    </div>

    <!-- Divider -->
    <div class="flex items-center gap-3 mb-7">
      <div class="flex-1 h-px bg-gray-100"></div>
      <span class="text-xs font-semibold text-gray-300 uppercase tracking-widest">Masukkan Nota</span>
      <div class="flex-1 h-px bg-gray-100"></div>
    </div>

    <!-- Intro -->
    <div class="text-center mb-6">
      <h2 class="text-lg font-semibold text-gray-800">Halo, Pelanggan Setia!</h2>
      <p class="text-gray-400 text-sm mt-1.5 leading-relaxed">
        Ceritakan pengalaman Anda. Hanya butuh <span class="font-semibold" style="color:#5B005F;">1 menit</span> &mdash; janji!
      </p>
    </div>

    <!-- Error -->
    <?php if ($error): ?>
    <div class="mb-5 flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm">
      <svg class="w-5 h-5 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
      </svg>
      <span><?= $error ?></span>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" autocomplete="off" id="notaForm">
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
          placeholder="Contoh: WA-2026-00123"
          value="<?= htmlspecialchars($_POST['no_nota'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
          maxlength="100"
          required
          class="input-field w-full pl-12 pr-4 py-3.5 rounded-2xl border-2 border-gray-200 transition-all text-gray-800 font-medium text-sm bg-gray-50"
        >
      </div>

      <button type="submit" id="submitBtn" class="btn-primary mt-5 w-full py-3.5 rounded-2xl text-white font-semibold text-base shadow-md flex items-center justify-center gap-2">
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
      kualitas layanan <span class="font-semibold" style="color:#5B005F;">Waschen Alora</span>.
    </p>
  </div>

  <script>
    // Floating bubbles via JS (avoids PHP rand() re-render on refresh)
    const container = document.getElementById('bubblesContainer');
    for (let i = 0; i < 12; i++) {
      const size  = Math.random() * 60 + 20 | 0;
      const left  = Math.random() * 100 | 0;
      const delay = (Math.random() * 8).toFixed(1);
      const dur   = (Math.random() * 10 + 9).toFixed(1);
      const el = document.createElement('div');
      el.className = 'bubble';
      el.style.cssText = `width:${size}px;height:${size}px;left:${left}%;bottom:-${size}px;animation-duration:${dur}s;animation-delay:${delay}s;`;
      container.appendChild(el);
    }

    document.getElementById('notaForm').addEventListener('submit', function() {
      document.getElementById('btnText').textContent = 'Memproses...';
      document.getElementById('btnIcon').classList.add('hidden');
      document.getElementById('btnSpinner').classList.remove('hidden');
      document.getElementById('submitBtn').disabled = true;
    });
  </script>
</body>
</html>
