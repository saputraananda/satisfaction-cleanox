<?php
session_start();

if (empty($_SESSION['done_csat'])) {
    header('Location: index.php');
    exit;
}

$no_nota   = htmlspecialchars($_SESSION['no_nota'] ?? '',    ENT_QUOTES, 'UTF-8');
$csat      = (int)$_SESSION['done_csat'];
$csat_lbl  = htmlspecialchars($_SESSION['done_label'] ?? '', ENT_QUOTES, 'UTF-8');
$nps       = (int)$_SESSION['done_nps'];
$nps_cat   = htmlspecialchars($_SESSION['done_cat'] ?? '',   ENT_QUOTES, 'UTF-8');

$csat_emoji = ['','😭','😞','😐','😊','🤩'][$csat] ?? '😊';
$nps_emoji  = $nps <= 6 ? '😔' : ($nps <= 8 ? '😌' : '🤩');

$cat_styles = [
    'Detractor' => ['bg'=>'#FFF5F5','text'=>'#EF4444','border'=>'#FECACA','msg'=>'Terima kasih. Kami akan segera berbenah untuk Anda!'],
    'Passive'   => ['bg'=>'#FFFBEB','text'=>'#D97706','border'=>'#FDE68A','msg'=>'Terima kasih. Kami akan terus meningkatkan kualitas!'],
    'Promoter'  => ['bg'=>'#F0FDF4','text'=>'#16A34A','border'=>'#BBF7D0','msg'=>'Anda luar biasa! Terima kasih atas kepercayaan Anda!'],
];
$cat = $cat_styles[$nps_cat] ?? $cat_styles['Passive'];

session_destroy();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Terima Kasih — Waschen Alora</title>
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
            'fade-up'    : 'fadeUp .55s ease-out both',
            'card-in'    : 'cardIn .65s ease-out both',
            'pop'        : 'pop .5s cubic-bezier(.175,.885,.32,1.275) both',
            'checkmark'  : 'checkmark .7s ease-out .3s both',
            'confetti'   : 'confettiFall linear infinite',
          },
          keyframes: {
            fadeUp      : { '0%': { opacity: '0', transform: 'translateY(20px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
            cardIn      : { '0%': { opacity: '0', transform: 'translateY(30px) scale(.96)' }, '100%': { opacity: '1', transform: 'translateY(0) scale(1)' } },
            pop         : { '0%': { transform: 'scale(0)', opacity: '0' }, '70%': { transform: 'scale(1.1)' }, '100%': { transform: 'scale(1)', opacity: '1' } },
            checkmark   : { '0%': { strokeDashoffset: '60' }, '100%': { strokeDashoffset: '0' } },
            confettiFall: { '0%': { transform: 'translateY(-10px) rotate(0deg)', opacity: '1' }, '100%': { transform: 'translateY(100vh) rotate(720deg)', opacity: '0' } },
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
      0%   { transform: translateY(100vh); opacity: .08; }
      100% { transform: translateY(-20vh); opacity: 0; }
    }
    .checkmark-path {
      stroke-dasharray: 60;
      stroke-dashoffset: 60;
      animation: checkmark .7s ease-out .4s forwards;
    }
    .confetti-piece {
      position: absolute;
      width: 8px;
      border-radius: 2px;
      animation: confettiFall linear infinite;
      pointer-events: none;
    }
    .btn-primary { background: #5B005F; transition: background .2s, transform .15s, box-shadow .2s; }
    .btn-primary:hover { background: #430046; box-shadow: 0 8px 24px rgba(91,0,95,.3); }
    .btn-primary:active { transform: scale(0.98); }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

  <div id="bubblesContainer" class="absolute inset-0 pointer-events-none overflow-hidden"></div>
  <div id="confettiContainer" class="absolute inset-0 pointer-events-none overflow-hidden"></div>

  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md px-8 py-10 animate-card-in relative z-10">

    <!-- Brand logo -->
    <div class="flex flex-col items-center mb-6">
      <img src="image/waschen.png" alt="Waschen Alora" class="h-10 w-auto mb-2 object-contain">
      <p class="text-xs font-semibold uppercase tracking-widest" style="color:#5B005F;">PT Waschen Alora Indonesia</p>
    </div>

    <!-- Success icon -->
    <div class="flex justify-center mb-6 animate-pop" style="animation-delay:.15s">
      <div class="relative w-24 h-24 flex items-center justify-center">
        <div class="absolute inset-0 rounded-full opacity-30 animate-ping" style="background:#C7A1C9;animation-duration:2s;"></div>
        <div class="w-20 h-20 rounded-full flex items-center justify-center shadow-xl" style="background:linear-gradient(135deg,#5B005F,#8A4A8D);">
          <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24">
            <path class="checkmark-path" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Heading -->
    <div class="text-center mb-6 animate-fade-up" style="animation-delay:.25s">
      <h1 class="text-3xl font-bold text-gray-800 mb-2">Terima Kasih! 🙏</h1>
      <p class="text-gray-400 text-sm leading-relaxed">
        Masukan Anda sangat berarti bagi kami.<br>
        Kami berkomitmen terus meningkatkan layanan.
      </p>
    </div>

    <!-- Nota badge -->
    <div class="flex justify-center mb-7 animate-fade-up" style="animation-delay:.3s">
      <div class="inline-flex items-center gap-2 text-xs font-semibold px-4 py-2 rounded-full" style="background:#F6F1F7;color:#5B005F;">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Nota: <strong><?= $no_nota ?></strong>
      </div>
    </div>

    <!-- Score summary -->
    <div class="grid grid-cols-2 gap-3 mb-6 animate-fade-up" style="animation-delay:.35s">

      <!-- CSAT -->
      <div class="rounded-2xl p-4 border" style="background:#F6F1F7;border-color:#E9D5EA;">
        <div class="flex items-center gap-1.5 mb-2">
          <span class="text-xs font-bold uppercase tracking-wider" style="color:#5B005F;">CSAT</span>
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20" style="color:#8A4A8D;">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
          </svg>
        </div>
        <div class="flex items-end gap-1.5">
          <span class="text-3xl leading-none"><?= $csat_emoji ?></span>
          <div>
            <p class="text-2xl font-bold text-gray-800 leading-none"><?= $csat ?><span class="text-sm font-semibold text-gray-400">/5</span></p>
            <p class="text-xs font-medium text-gray-500 mt-0.5"><?= $csat_lbl ?></p>
          </div>
        </div>
        <div class="flex gap-0.5 mt-2">
          <?php for ($s=1; $s<=5; $s++): ?>
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20" style="color:<?= $s <= $csat ? '#F59E0B' : '#E2E8F0' ?>;">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
          </svg>
          <?php endfor; ?>
        </div>
      </div>

      <!-- NPS -->
      <div class="rounded-2xl p-4 border" style="background:<?= $cat['bg'] ?>;border-color:<?= $cat['border'] ?>;">
        <div class="flex items-center gap-1.5 mb-2">
          <span class="text-xs font-bold uppercase tracking-wider" style="color:<?= $cat['text'] ?>;">NPS</span>
        </div>
        <div class="flex items-end gap-1.5">
          <span class="text-3xl leading-none"><?= $nps_emoji ?></span>
          <div>
            <p class="text-2xl font-bold text-gray-800 leading-none"><?= $nps ?><span class="text-sm font-semibold text-gray-400">/10</span></p>
            <p class="text-xs font-semibold mt-0.5" style="color:<?= $cat['text'] ?>;"><?= $nps_cat ?></p>
          </div>
        </div>
        <div class="mt-2 w-full bg-white bg-opacity-60 rounded-full h-1.5 overflow-hidden">
          <div class="h-1.5 rounded-full" style="width:<?= ($nps/10)*100 ?>%;background:<?= $cat['text'] ?>;"></div>
        </div>
      </div>
    </div>

    <!-- Category message -->
    <div class="animate-fade-up mb-7" style="animation-delay:.4s">
      <div class="rounded-2xl px-5 py-3.5 flex items-center gap-3 border" style="background:<?= $cat['bg'] ?>;border-color:<?= $cat['border'] ?>;">
        <span class="text-2xl"><?= $nps_emoji ?></span>
        <p class="text-sm font-semibold" style="color:<?= $cat['text'] ?>;"><?= $cat['msg'] ?></p>
      </div>
    </div>

    <!-- CTA -->
    <div class="animate-fade-up" style="animation-delay:.45s">
      <a href="index.php?reset=1" class="btn-primary block w-full py-3.5 rounded-2xl text-center font-semibold text-sm text-white shadow-md">
        Isi Survey Nota Lain
      </a>
    </div>

    <!-- Footer -->
    <div class="mt-7 pt-5 border-t border-gray-100 flex items-center justify-center gap-2">
      <img src="image/waschen.png" alt="" class="h-5 w-auto object-contain opacity-40">
      <p class="text-xs text-gray-400 font-medium">PT Waschen Alora Indonesia</p>
    </div>
  </div>

  <script>
    // Confetti burst
    const colors = ['#5B005F','#8A4A8D','#C7A1C9','#F3E6F5','#10B981','#F59E0B','#ffffff'];
    const confettiContainer = document.getElementById('confettiContainer');

    function makeConfetti() {
      for (let i = 0; i < 55; i++) {
        const piece = document.createElement('div');
        piece.classList.add('confetti-piece');
        piece.style.left             = Math.random() * 100 + 'vw';
        piece.style.top              = '-10px';
        piece.style.height           = (Math.random() * 8 + 6) + 'px';
        piece.style.width            = (Math.random() * 6 + 4) + 'px';
        piece.style.background       = colors[Math.floor(Math.random() * colors.length)];
        piece.style.borderRadius     = Math.random() > .5 ? '50%' : '2px';
        piece.style.animationDuration = (Math.random() * 3 + 2.5) + 's';
        piece.style.animationDelay   = (Math.random() * 1.5) + 's';
        confettiContainer.appendChild(piece);
        setTimeout(() => piece.remove(), 6000);
      }
    }

    window.addEventListener('load', () => {
      setTimeout(makeConfetti, 300);
      setTimeout(makeConfetti, 1100);
    });

    // Floating bubbles
    const container = document.getElementById('bubblesContainer');
    for (let i = 0; i < 10; i++) {
      const size  = Math.random() * 55 + 18 | 0;
      const left  = Math.random() * 100 | 0;
      const delay = (Math.random() * 9).toFixed(1);
      const dur   = (Math.random() * 10 + 9).toFixed(1);
      const el = document.createElement('div');
      el.className = 'bubble';
      el.style.cssText = `width:${size}px;height:${size}px;left:${left}%;bottom:-${size}px;animation-duration:${dur}s;animation-delay:${delay}s;`;
      container.appendChild(el);
    }
  </script>
</body>
</html>
