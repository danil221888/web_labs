<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KPI STORE — Магазин Відеоігор</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,300;0,400;0,600;0,700;0,900;1,700&family=Barlow:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

  <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/layout.css">
  <link rel="stylesheet" href="css/card.css">
  <link rel="stylesheet" href="css/overlays.css">

  <style>
    .page-nav {
      display: flex;
      gap: 0;
      border-bottom: 1px solid var(--border);
      padding: 0 32px;
      background: var(--surface);
    }
    .page-nav a {
      padding: 10px 22px;
      font-family: var(--mono);
      font-size: 11px;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: var(--muted);
      text-decoration: none;
      border-right: 1px solid var(--border);
      transition: color .15s, background .15s;
    }
    .page-nav a:first-child { border-left: 1px solid var(--border); }
    .page-nav a:hover       { color: var(--text); background: #1a1a1a; }
    .page-nav a.active      { color: var(--accent); border-bottom: 2px solid var(--accent); }

    .page-loading #content { opacity: .4; pointer-events: none; transition: opacity .2s; }
    .nav-loader {
      position: fixed; top: 0; left: 0; right: 0; height: 2px;
      background: var(--accent); transform: scaleX(0); transform-origin: left;
      transition: transform .3s ease; z-index: 9998;
    }
    .nav-loader.active { transform: scaleX(.7); }
    .nav-loader.done   { transform: scaleX(1); transition: transform .1s ease; }
  </style>
</head>
<body>

<div class="nav-loader" id="navLoader"></div>

<div id="app">

  <header>
    <div class="logo" @click="cartOpen = false">
      <span class="logo-dot"></span>
      KPI<span style="opacity:.45">STORE</span>
    </div>
    <div class="header-right">
      <input type="text" class="search-bar" placeholder="пошук ігор..." v-model="searchQuery">

      <button class="wishlist-btn" @click="wishlistOpen = true" title="Вішліст">
        ♡
        <span class="wishlist-badge" :class="{ visible: wishlist.length > 0 }">{{ wishlist.length }}</span>
      </button>

      <button class="cart-btn" @click="cartOpen = true">
        Кошик
        <span class="cart-badge" :class="{ visible: cartTotal > 0 }">{{ cartTotal }}</span>
      </button>
    </div>
  </header>

  <nav class="page-nav">
    <a href="?page=catalog" data-page="catalog"
       :class="{ active: currentPage === 'catalog' }"
       @click.prevent="navigateTo('catalog')">// Каталог</a>
    <a href="?page=about" data-page="about"
       :class="{ active: currentPage === 'about' }"
       @click.prevent="navigateTo('about')">// Про нас</a>
    <a href="?page=faq" data-page="faq"
       :class="{ active: currentPage === 'faq' }"
       @click.prevent="navigateTo('faq')">// FAQ</a>
  </nav>

  <div v-show="currentPage === 'catalog'">
    <?php $controller->render('catalog'); ?>
  </div>

  <div v-show="currentPage !== 'catalog'">
    <div v-if="staticLoading" style="text-align:center;padding:80px 32px;font-family:var(--mono);font-size:11px;color:var(--muted);letter-spacing:.1em;">// завантаження...</div>
    <div id="static-content">
      <?php if ($page !== 'catalog') $controller->render($page); ?>
    </div>
  </div>

  <div class="modal-backdrop" :class="{ open: modalOpen }" @click="modalOpen = false"></div>
  <div class="modal" :class="{ open: modalOpen }" role="dialog" aria-modal="true">
    <div class="modal-inner" v-if="selectedGame">
      <div class="modal-cover-wrap">
        <img class="modal-cover" :src="selectedGame.logo" :alt="selectedGame.title"
             @error="e => { e.target.style.display='none'; e.target.nextElementSibling.style.display='flex'; }">
        <div class="modal-cover-placeholder" style="display:none">{{ selectedGame.emoji }}</div>
        <div class="modal-cover-gradient"></div>
        <div v-if="selectedGame.discount" class="modal-cover-discount">{{ selectedGame.discount }}%</div>
        <button class="modal-close" @click="modalOpen = false">✕</button>
      </div>
      <div class="modal-body">
        <div class="modal-eyebrow">
          <span class="modal-genre">{{ genreLabel(selectedGame.genre) }}</span>
          <div class="modal-tags">
            <span v-for="tag in selectedGame.tags" :key="tag" class="modal-tag">{{ tag }}</span>
          </div>
        </div>
        <div class="modal-title">{{ selectedGame.title }}</div>
        <div class="modal-dev">{{ selectedGame.developer }} · {{ selectedGame.year }}</div>
        <div class="modal-rating-row">
          <span class="modal-stars">{{ stars(selectedGame.rating) }}</span>
          <span class="modal-score">{{ selectedGame.rating.toFixed(1) }}</span>
          <span class="modal-reviews">/ 5.0</span>
        </div>
        <p class="modal-desc">{{ selectedGame.description }}</p>
        <div class="modal-meta-grid">
          <div class="modal-meta-item"><div class="modal-meta-label">Платформа</div><div class="modal-meta-val">{{ selectedGame.platform }}</div></div>
          <div class="modal-meta-item"><div class="modal-meta-label">Гравці</div><div class="modal-meta-val">{{ selectedGame.players }}</div></div>
          <div class="modal-meta-item"><div class="modal-meta-label">Жанр</div><div class="modal-meta-val">{{ genreLabel(selectedGame.genre) }}</div></div>
          <div class="modal-meta-item"><div class="modal-meta-label">Рік виходу</div><div class="modal-meta-val">{{ selectedGame.year }}</div></div>
        </div>
        <div class="modal-footer">
          <div>
            <div v-if="selectedGame.price === 0" class="modal-price-free">Безкоштовно</div>
            <template v-else>
              <div v-if="selectedGame.oldPrice" class="modal-old-price">₴{{ fmt(selectedGame.oldPrice) }}</div>
              <div class="modal-price"><span style="font-size:20px;opacity:.6">₴</span>{{ fmt(selectedGame.price) }}</div>
            </template>
          </div>
          <button class="modal-cart-btn" :class="{ added: isInCart(selectedGame.id) }" @click="addToCart(selectedGame)">
            {{ isInCart(selectedGame.id) ? '✓ В кошику' : 'Додати до кошика' }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="cart-overlay" :class="{ open: cartOpen }" @click="cartOpen = false"></div>
  <div class="cart-sidebar" :class="{ open: cartOpen }">
    <div class="cart-header">
      <div class="cart-title">Кошик</div>
      <button class="cart-close" @click="cartOpen = false">✕</button>
    </div>
    <div class="cart-items">
      <div v-if="cart.length === 0" class="cart-empty">
        <div class="cart-empty-icon">🛒</div><p>// кошик порожній //</p>
      </div>
      <div v-for="item in cart" :key="item.id" class="cart-item">
        <div class="cart-item-img">
          <img :src="item.logo" @error="e => { e.target.parentElement.textContent = item.emoji }">
        </div>
        <div class="cart-item-info">
          <div class="cart-item-name">{{ item.title }}</div>
          <div class="cart-item-price">{{ item.price === 0 ? 'Безкоштовно' : '₴' + fmt(item.price * item.qty) }}</div>
        </div>
        <div class="cart-item-controls">
          <button class="qty-btn" @click="changeQty(item.id, -1)">−</button>
          <span class="qty-num">{{ item.qty }}</span>
          <button class="qty-btn" @click="changeQty(item.id, 1)">+</button>
          <button class="remove-btn" @click="removeFromCart(item.id)">✕</button>
        </div>
      </div>
    </div>
    <div v-if="cart.length > 0" class="cart-footer">
      <div>
        <div class="summary-row"><span>Товарів</span><span>{{ cartTotal }} шт.</span></div>
        <div v-if="cartSaved > 0" class="summary-row"><span>Економія</span><span style="color:#4ade80">−₴{{ fmt(cartSaved) }}</span></div>
        <div class="summary-row total"><span>Разом</span><span class="sum">₴{{ fmt(cartSubtotal) }}</span></div>
      </div>
      <button class="checkout-btn" @click="checkout">Оформити замовлення</button>
      <button class="clear-btn" @click="clearCart">Очистити кошик</button>
    </div>
  </div>

  <div class="cart-overlay" :class="{ open: wishlistOpen }" @click="wishlistOpen = false"></div>
  <div class="cart-sidebar" :class="{ open: wishlistOpen }">
    <div class="cart-header">
      <div class="cart-title">♡ Вішліст</div>
      <button class="cart-close" @click="wishlistOpen = false">✕</button>
    </div>
    <div class="cart-items">
      <div v-if="wishlistGames.length === 0" class="cart-empty">
        <div class="cart-empty-icon">♡</div><p>// вішліст порожній //</p>
      </div>
      <div v-for="item in wishlistGames" :key="item.id" class="cart-item">
        <div class="cart-item-img">
          <img :src="item.logo" @error="e => { e.target.parentElement.textContent = item.emoji }">
        </div>
        <div class="cart-item-info">
          <div class="cart-item-name">{{ item.title }}</div>
          <div class="cart-item-price">{{ item.price === 0 ? 'Безкоштовно' : '₴' + fmt(item.price) }}</div>
        </div>
        <div class="cart-item-controls">
          <button class="wish-add-btn" :class="{ added: isInCart(item.id) }" @click="addToCart(item)">
            {{ isInCart(item.id) ? '✓' : '+ Купити' }}
          </button>
          <button class="remove-btn" @click="toggleWishlist(item.id)">✕</button>
        </div>
      </div>
    </div>
    <div v-if="wishlistGames.length > 0" class="cart-footer">
      <button class="checkout-btn" @click="moveAllToCart">Перенести все в кошик</button>
      <button class="clear-btn" @click="clearWishlist">Очистити вішліст</button>
    </div>
  </div>

  <div class="toast" :class="{ show: toastVisible }">{{ toastMsg }}</div>

</div>

<script src="js/app.js"></script>

</body>
</html>
