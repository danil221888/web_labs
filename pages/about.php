<?php
$stats = [
    ['12+', 'Ігор у каталозі'],
    ['24/7', 'Підтримка'],
    ['< 1хв', 'Доставка ключів'],
    ['100%', 'Ліцензійні'],
];
?>

<div style="max-width:760px; margin:0 auto; padding:60px 32px;">
  <div style="font-family:var(--mono);font-size:10px;color:var(--accent);letter-spacing:.2em;text-transform:uppercase;margin-bottom:16px;">// about us</div>
  <h1 style="font-family:var(--cond);font-weight:900;font-size:clamp(36px,6vw,72px);text-transform:uppercase;letter-spacing:-.01em;line-height:.95;color:var(--text);margin-bottom:28px;">
    Ми — <em style="font-style:italic;color:var(--accent)">KPI Store</em>
  </h1>
  <p style="font-size:15px;color:#aaa;line-height:1.7;margin-bottom:20px;border-left:2px solid var(--border2);padding-left:16px;">
    KPI Store — ми продаємо ліцензовані ключі активації для PC, PlayStation та Xbox. Усі ключі надходять безпосередньо від видавців або авторизованих дистриб'юторів.
  </p>
  <p style="font-size:15px;color:#aaa;line-height:1.7;margin-bottom:40px;border-left:2px solid var(--border2);padding-left:16px;">
    Доставка ключа — миттєво на email після оплати. Підтримка працює 24/7.
  </p>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1px;background:var(--border);border:1px solid var(--border);border-radius:4px;overflow:hidden;">
    <?php foreach ($stats as [$num, $label]): ?>
    <div style="background:var(--card);padding:20px 18px;">
      <div style="font-family:var(--cond);font-weight:900;font-size:32px;color:var(--accent);letter-spacing:-.01em;margin-bottom:4px;"><?= htmlspecialchars($num) ?></div>
      <div style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:.12em;text-transform:uppercase;"><?= htmlspecialchars($label) ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <div style="margin-top:48px;">
    <div style="font-family:var(--mono);font-size:10px;color:var(--accent);letter-spacing:.2em;text-transform:uppercase;margin-bottom:16px;">// топ ігор (з API)</div>
    <div id="api-demo" style="font-family:var(--mono);font-size:12px;color:var(--muted);">Завантаження...</div>
  </div>
</div>
<script>
fetch('api/games.php?limit=3&sort=rating')
  .then(r => r.json())
  .then(({ data, meta }) => {
    const el = document.getElementById('api-demo');
    if (!el) return;
    el.innerHTML = data.map((g, i) =>
      `<div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);">
         <span style="color:var(--accent);min-width:20px;">${String(i+1).padStart(2,'0')}</span>
         <span style="color:var(--text);font-family:var(--cond);font-size:16px;font-weight:700;flex:1;">${g.title}</span>
         <span style="color:var(--gold);">★ ${g.rating}</span>
         <span style="color:var(--muted2);">₴${g.price.toLocaleString('uk-UA')}</span>
       </div>`
    ).join('') + `<div style="margin-top:10px;color:var(--muted);font-size:10px;">Всього в БД: ${meta.total} ігор</div>`;
  })
  .catch(e => {
    const el = document.getElementById('api-demo');
    if (el) el.textContent = 'Помилка завантаження: ' + e.message;
  });
</script>
