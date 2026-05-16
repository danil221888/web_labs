<?php
$items = [
    ['Як отримати ключ після покупки?', 'Ключ активації надсилається автоматично на вашу email-адресу одразу після підтвердження оплати. Перевірте папку «Спам», якщо лист не прийшов протягом 5 хвилин.'],
    ['Які платіжні методи підтримуються?', 'Ми приймаємо Visa, Mastercard, а також Apple Pay та Google Pay. Усі транзакції захищені SSL-шифруванням.'],
    ['Чи можна повернути ключ?', 'Цифрові товари не підлягають поверненню після активації. Якщо ключ не спрацював — зверніться до підтримки, ми замінимо його безкоштовно.'],
    ['Як активувати ключ Steam?', 'Відкрийте Steam → меню «Ігри» → «Активувати продукт у Steam». Введіть отриманий ключ і дотримуйтесь інструкцій.'],
    ['Для яких регіонів дійсні ключі?', 'Більшість ключів — глобальні. Регіональні обмеження вказані в описі товару окремо.'],
];
?>

<div style="max-width:760px; margin:0 auto; padding:60px 32px;">
  <div style="font-family:var(--mono);font-size:10px;color:var(--accent);letter-spacing:.2em;text-transform:uppercase;margin-bottom:16px;">// faq</div>
  <h1 style="font-family:var(--cond);font-weight:900;font-size:clamp(36px,6vw,72px);text-transform:uppercase;line-height:.95;color:var(--text);margin-bottom:36px;">
    Часті <em style="font-style:italic;color:var(--accent)">питання</em>
  </h1>
  <?php foreach ($items as $i => [$q, $a]): ?>
  <details style="border-top:1px solid var(--border);padding:18px 0;" <?= $i === 0 ? 'open' : '' ?>>
    <summary style="cursor:pointer;font-family:var(--cond);font-weight:700;font-size:18px;letter-spacing:.02em;text-transform:uppercase;color:var(--text);list-style:none;display:flex;justify-content:space-between;align-items:center;">
      <?= htmlspecialchars($q) ?>
      <span style="font-size:20px;color:var(--muted);flex-shrink:0;margin-left:12px;">+</span>
    </summary>
    <p style="margin-top:12px;font-size:14px;color:#aaa;line-height:1.65;border-left:2px solid var(--border2);padding-left:14px;"><?= htmlspecialchars($a) ?></p>
  </details>
  <?php endforeach; ?>
  <div style="border-top:1px solid var(--border);padding-top:18px;"></div>
</div>
