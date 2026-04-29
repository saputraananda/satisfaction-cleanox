<?php
session_start();

if (empty($_SESSION['no_nota'])) {
  header('Location: index.php');
  exit;
}

// Allow going back from NPS to edit CSAT
if (isset($_GET['back'])) {
  unset($_SESSION['csat_score'], $_SESSION['csat_label']);
}

// Only auto-advance if score is already set AND we're not in back-edit mode
if (!empty($_SESSION['csat_score']) && !isset($_GET['back'])) {
  header('Location: nps.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $score = (int) ($_POST['csat_score'] ?? 0);
  $labels = [1 => 'Sangat Tidak Puas', 2 => 'Tidak Puas', 3 => 'Biasa Saja', 4 => 'Puas', 5 => 'Sangat Puas'];
  if ($score >= 1 && $score <= 5) {
    $_SESSION['csat_score'] = $score;
    $_SESSION['csat_label'] = $labels[$score];
    header('Location: nps.php');
    exit;
  }
}

$no_nota = htmlspecialchars($_SESSION['no_nota'], ENT_QUOTES, 'UTF-8');
$preselected = (int) ($_SESSION['csat_score'] ?? 0);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Penilaian Layanan — Waschen Laundry</title>
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
            'fade-up': 'fadeUp .5s ease-out both',
            'card-in': 'cardIn .6s ease-out both',
          },
          keyframes: {
            fadeUp: { '0%': { opacity: '0', transform: 'translateY(18px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
            cardIn: { '0%': { opacity: '0', transform: 'translateY(28px) scale(.97)' }, '100%': { opacity: '1', transform: 'translateY(0) scale(1)' } },
          },
        },
      },
    }
  </script>
  <style>
    * {
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: linear-gradient(135deg, #5B005F 0%, #8A4A8D 100%);
    }

    .bubble {
      position: absolute;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.12);
      animation: floatUp linear infinite;
    }

    @keyframes floatUp {
      0% {
        transform: translateY(100vh) scale(1);
        opacity: .12;
      }

      100% {
        transform: translateY(-20vh) scale(1.2);
        opacity: 0;
      }
    }

    .emoji-btn {
      transition: transform .2s, box-shadow .2s;
      cursor: pointer;
      user-select: none;
    }

    .emoji-btn:hover {
      transform: translateY(-5px) scale(1.07);
    }

    .emoji-btn.active {
      transform: translateY(-7px) scale(1.14);
    }

    .emoji-face {
      transition: transform .2s, filter .2s;
      display: inline-block;
    }

    .emoji-btn:hover .emoji-face,
    .emoji-btn.active .emoji-face {
      transform: scale(1.15);
      filter: drop-shadow(0 4px 6px rgba(0, 0, 0, .18));
    }

    .progress-bar {
      transition: width .5s cubic-bezier(.4, 0, .2, 1);
    }

    .btn-primary {
      background: #5B005F;
      transition: background .2s, transform .15s, opacity .2s;
    }

    .btn-primary:hover:not(:disabled) {
      background: #430046;
    }

    .btn-primary:active:not(:disabled) {
      transform: scale(0.98);
    }
  </style>
</head>

<body class="min-h-screen flex items-start sm:items-center justify-center p-4 py-8 relative">

  <div id="bubblesContainer" class="fixed inset-0 pointer-events-none overflow-hidden"></div>

  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg px-8 py-10 animate-card-in relative z-10">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-5">
      <a href="index.php?reset=1" class="text-gray-400 hover:text-gray-600 transition-colors p-1"
        title="Kembali ke awal">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
      </a>
      <div class="flex-1">
        <p class="text-xs font-semibold uppercase tracking-widest" style="color:#5B005F;">Waschen Laundry</p>
        <p class="text-xs text-gray-400">Nota: <strong class="text-gray-600"><?= $no_nota ?></strong></p>
      </div>
      <span class="text-xs font-semibold text-gray-400 bg-gray-100 px-3 py-1 rounded-full">1 / 3</span>
    </div>

    <!-- Progress -->
    <div class="w-full bg-gray-100 rounded-full h-1.5 mb-8 overflow-hidden">
      <div class="progress-bar h-1.5 rounded-full" style="width:33%; background:#5B005F;"></div>
    </div>

    <!-- Title -->
    <div class="text-center mb-8 animate-fade-up" style="animation-delay:.08s">
      <div class="inline-flex items-center gap-2 text-xs font-semibold px-4 py-1.5 rounded-full mb-3"
        style="background:#F3E6F5;color:#5B005F;">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path
            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
        </svg>
        Kepuasan Layanan
      </div>
      <h2 class="text-xl font-bold text-gray-800 leading-tight">
        Seberapa puas Anda dengan<br>layanan kami? 🧺
      </h2>
      <p class="text-gray-400 text-sm mt-2">Pilih ekspresi yang paling menggambarkan perasaan Anda</p>
    </div>

    <form method="POST" id="csatForm">
      <input type="hidden" name="csat_score" id="csat_score_input" value="">

      <!-- Emoji rating grid -->
      <div class="grid grid-cols-5 gap-3 mb-8">
        <?php
        $options = [
          1 => ['emoji' => '😭', 'label' => 'Sangat<br>Tidak Puas', 'bg' => '#FFF5F5', 'border' => '#FECACA', 'activeBg' => '#FEE2E2', 'activeBorder' => '#F87171'],
          2 => ['emoji' => '😞', 'label' => 'Tidak<br>Puas', 'bg' => '#FFF7ED', 'border' => '#FED7AA', 'activeBg' => '#FFEDD5', 'activeBorder' => '#FB923C'],
          3 => ['emoji' => '😐', 'label' => 'Biasa<br>Saja', 'bg' => '#FEFCE8', 'border' => '#FDE68A', 'activeBg' => '#FEF9C3', 'activeBorder' => '#FACC15'],
          4 => ['emoji' => '😊', 'label' => 'Puas', 'bg' => '#F7FEE7', 'border' => '#BBF7D0', 'activeBg' => '#DCFCE7', 'activeBorder' => '#4ADE80'],
          5 => ['emoji' => '😍  ', 'label' => 'Sangat<br>Puas', 'bg' => '#F0FDF4', 'border' => '#86EFAC', 'activeBg' => '#DCFCE7', 'activeBorder' => '#22C55E'],
        ];
        foreach ($options as $score => $opt):
          $isActive = ($preselected === $score);
          $bg = $isActive ? $opt['activeBg'] : $opt['bg'];
          $border = $isActive ? $opt['activeBorder'] : $opt['border'];
          ?>
          <button type="button" onclick="selectCsat(<?= $score ?>)" id="csatBtn<?= $score ?>" data-score="<?= $score ?>"
            data-default-bg="<?= $opt['bg'] ?>" data-default-border="<?= $opt['border'] ?>"
            data-active-bg="<?= $opt['activeBg'] ?>" data-active-border="<?= $opt['activeBorder'] ?>"
            class="emoji-btn flex flex-col items-center gap-2 p-3 rounded-2xl border-2 <?= $isActive ? 'active' : '' ?>"
            style="background:<?= $bg ?>;border-color:<?= $border ?>;">
            <span class="emoji-face text-3xl leading-none"><?= $opt['emoji'] ?></span>
            <span class="text-center leading-tight"
              style="font-size:10px;font-weight:600;color:#475569;"><?= $opt['label'] ?></span>
            <span class="w-2 h-2 rounded-full <?= $isActive ? 'opacity-100' : 'opacity-0' ?> transition-opacity"
              id="dot<?= $score ?>" style="background:<?= $opt['activeBorder'] ?>;"></span>
          </button>
        <?php endforeach; ?>
      </div>

      <!-- Selected feedback strip -->
      <div id="selectedStrip"
        class="<?= $preselected ? '' : 'hidden' ?> mb-5 flex items-center justify-center gap-2 py-3 px-4 rounded-2xl text-sm font-semibold text-gray-700 animate-fade-up"
        style="background:#F3E6F5;border:1px solid #C7A1C9;">
        <span id="selectedEmoji"
          class="text-xl"><?= $preselected ? ['', '😭', '😞', '😐', '😊', '🤩'][$preselected] : '' ?></span>
        <span>Anda memilih: <strong id="selectedLabel"
            style="color:#5B005F;"><?= $preselected ? ['', 'Sangat Tidak Puas', 'Tidak Puas', 'Biasa Saja', 'Puas', 'Sangat Puas'][$preselected] : '' ?></strong></span>
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
          style="color:#10B981;">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
      </div>

      <button type="submit" id="csatSubmit" <?= $preselected ? '' : 'disabled' ?>
        class="btn-primary w-full py-3.5 rounded-2xl font-semibold text-base text-white flex items-center justify-center gap-2 shadow-md <?= $preselected ? '' : 'opacity-40 cursor-not-allowed' ?> hidden">
        Lanjutkan ke Last Question
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
        </svg>
      </button>
    </form>

    <!-- Step dots -->
    <div class="flex justify-center items-center gap-2 mt-6">
      <span class="w-6 h-2 rounded-full" style="background:#5B005F;"></span>
      <span class="w-2 h-2 rounded-full bg-gray-200"></span>
      <span class="w-2 h-2 rounded-full bg-gray-200"></span>
    </div>
  </div>

  <script>
    const labels = { 1: 'Sangat Tidak Puas', 2: 'Tidak Puas', 3: 'Biasa Saja', 4: 'Puas', 5: 'Sangat Puas' };
    const emojis = { 1: '😭', 2: '😞', 3: '😐', 4: '😊', 5: '😍' };
    let selected = <?= $preselected ?: 'null' ?>;
    let submitTimer = null;

    // Preselect if returning from NPS back
    if (selected) {
      document.getElementById('csat_score_input').value = selected;
    }

    function selectCsat(score) {
      clearTimeout(submitTimer);

      for (let i = 1; i <= 5; i++) {
        const btn = document.getElementById('csatBtn' + i);
        btn.classList.remove('active');
        btn.style.background = btn.dataset.defaultBg;
        btn.style.borderColor = btn.dataset.defaultBorder;
        document.getElementById('dot' + i).style.opacity = '0';
      }

      const active = document.getElementById('csatBtn' + score);
      active.classList.add('active');
      active.style.background = active.dataset.activeBg;
      active.style.borderColor = active.dataset.activeBorder;
      document.getElementById('dot' + score).style.opacity = '1';

      document.getElementById('csat_score_input').value = score;
      document.getElementById('selectedEmoji').textContent = emojis[score];
      document.getElementById('selectedLabel').textContent = labels[score];
      document.getElementById('selectedStrip').classList.remove('hidden');

      const btn = document.getElementById('csatSubmit');
      btn.disabled = false;
      btn.classList.remove('opacity-40', 'cursor-not-allowed');

      selected = score;

      submitTimer = setTimeout(() => {
        if (selected === score) document.getElementById('csatForm').submit();
      }, 400);
    }

    // Floating bubbles
    const container = document.getElementById('bubblesContainer');
    for (let i = 0; i < 10; i++) {
      const size = Math.random() * 55 + 18 | 0;
      const left = Math.random() * 100 | 0;
      const delay = (Math.random() * 9).toFixed(1);
      const dur = (Math.random() * 10 + 9).toFixed(1);
      const el = document.createElement('div');
      el.className = 'bubble';
      el.style.cssText = `width:${size}px;height:${size}px;left:${left}%;bottom:-${size}px;animation-duration:${dur}s;animation-delay:${delay}s;`;
      container.appendChild(el);
    }
  </script>
</body>

</html>